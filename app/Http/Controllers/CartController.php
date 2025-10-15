<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartDetailResource;
use App\Models\CartDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $query = CartDetail::with('cart');

        // 🔎 Filtro por cupón del influencer autenticado
        $user = $request->user(); // Usuario autenticado via Sanctum
        
        if ($user && $user->code) {
            $query->whereRaw('LOWER(coupon) = ?', [strtolower($user->code)]);
        } else {
            // Si no hay usuario autenticado o no tiene código, no mostrar nada
            return response()->json([
                'data' => [],
                'total' => 0,
                'per_page' => 10,
                'current_page' => 1,
            ]);
        }

        $type = $request->input('type');

        // 🎟️ Filtro por tipo de entrada
        if ($type === 'ALL') {
            $query->whereIn('intBoletoId', [11, 17]);
        } elseif (!empty($type)) {
            $query->where('intBoletoId', $type);
        }

        // 📅 Filtro por rango de fechas (fecha del carrito)
        $dateRange = $request->input('date');

        if ($dateRange = $request->input('date')) {
            if (strpos($dateRange, ' a ') !== false) {
                [$from, $to] = explode(' a ', $dateRange);
            } else {
                $from = $to = $dateRange;
            }

            $from = Carbon::parse($from)->startOfDay();
            $to   = Carbon::parse($to)->endOfDay();

            $query->whereHas('cart', function ($q) use ($from, $to) {
                $q->whereBetween('dateCartFreg', [$from, $to]);
            });
        }

        // 📌 Ordenamiento
        $sortBy  = $request->input('sortBy', 'dateCartFreg');
        $orderBy = $request->input('orderBy', 'desc');

        $allowedSorts = ['dateCartFreg', 'intBoletoId', 'coupon'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy(
                $sortBy === 'dateCartFreg' ? CartDetail::rawColumnCartDate() : $sortBy,
                $orderBy === 'asc' ? 'asc' : 'desc'
            );
        }

        // 📄 Paginación
        $perPage = max((int) $request->input('itemsPerPage', 10), 1);
        $cartDetails = $query->paginate($perPage);

        // 📦 Respuesta uniforme
        return response()->json([
            'data' => CartDetailResource::collection($cartDetails->items()),
            'total' => $cartDetails->total(),
            'per_page' => $cartDetails->perPage(),
            'current_page' => $cartDetails->currentPage(),
        ]);
    }

    public function chartData(Request $request)
    {
        // 🔎 PASO 1: Obtener el código del influencer autenticado
        $user = $request->user();
        
        if (!$user || !$user->code) {
            return response()->json([
                'series' => [
                    ['name' => 'Entrada General', 'data' => []],
                    ['name' => 'Entrada Light', 'data' => []],
                ],
                'categories' => [],
            ]);
        }

        $coupon = strtolower($user->code);

        // 📅 PASO 2: Obtener y procesar el rango de fechas
        $dateRange = $request->input('date');
        $type = $request->input('type');

        if ($dateRange = $request->input('date')) {
            if (strpos($dateRange, ' a ') !== false) {
                // Rango de fechas
                [$from, $to] = explode(' a ', $dateRange);
            } else {
                // Solo un día
                $from = $to = $dateRange;
            }
        } else {
            // No viene fecha, usar mes actual
            $from = now()->startOfMonth()->format('Y-m-d');
            $to   = now()->format('Y-m-d');
        }

        // 🔹 Ajustar para que incluya todo el día
        $from = Carbon::parse($from)->startOfDay()->format('Y-m-d H:i:s');
        $to   = Carbon::parse($to)->endOfDay()->format('Y-m-d H:i:s');

        // 📊 PASO 3: Consulta SQL - Agrupar ventas por día y tipo
        $data = CartDetail::select(
            DB::raw('DATE(cart.dateCartFreg) as date'),
            'cartdet.intBoletoId',
            DB::raw('COUNT(*) as total')
        )
            ->join('cart', 'cartdet.intCartId', '=', 'cart.intCartId')
            ->whereRaw('LOWER(cartdet.coupon) = ?', [$coupon])
            ->when($type === 'ALL', function ($query) {
                $query->whereIn('cartdet.intBoletoId', [11, 17]);
            })
            ->when($type == 11 || $type == 17, function ($query) use ($type) {
                $query->where('cartdet.intBoletoId', $type);
            })
            ->whereBetween('cart.dateCartFreg', [$from, $to])
            ->groupBy(DB::raw('DATE(cart.dateCartFreg)'), 'cartdet.intBoletoId')
            ->orderBy(DB::raw('DATE(cart.dateCartFreg)'))
            ->get();

        // 🗓️ PASO 4: Generar TODAS las fechas del rango
        $dates = [];
        $fromDate = Carbon::parse($from);
        $toDate = Carbon::parse($to);
        $daysDiff = $fromDate->diffInDays($toDate) + 1;

        if ($daysDiff > 20) {
            // Solo fechas con ventas (sin días vacíos)
            $dates = $data->pluck('date')->unique()->sort()->values()->toArray();
        } else {
            // Generar todas las fechas del rango (comportamiento original)
            $dates = [];
            $current = clone $fromDate;

            while ($current <= $toDate) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }

        // 📦 PASO 5: Preparar arrays para ApexCharts
        $general = [];
        $light = [];
        $categories = [];

        foreach ($dates as $date) {
            // Buscar si hubo ventas de Entrada General ese día
            $generalSale = $data->where('date', $date)
                ->where('intBoletoId', 11)
                ->first();

            // Buscar si hubo ventas de Entrada Light ese día
            $lightSale = $data->where('date', $date)
                ->where('intBoletoId', 17)
                ->first();

            // Agregar el total (o 0 si no hubo ventas)
            $general[] = $generalSale ? (int) $generalSale->total : 0;
            $light[] = $lightSale ? (int) $lightSale->total : 0;

            // Formatear fecha para el eje X: "01/10"
            $categories[] = \Carbon\Carbon::parse($date)->format('d/m');
        }

        // 🎯 PASO 6: Retornar JSON en formato ApexCharts
        return response()->json([
            'series' => [
                [
                    'name' => 'Entrada General',
                    'data' => $general,
                ],
                [
                    'name' => 'Entrada Light',
                    'data' => $light,
                ],
            ],
            'categories' => $categories,
        ]);
    }
}
