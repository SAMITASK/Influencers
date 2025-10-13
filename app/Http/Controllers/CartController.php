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

        // 🔎 Filtro por cupón
        $coupon = strtolower($request->coupon);
        $query->whereRaw('LOWER(coupon) = ?', ['camila2025']);

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
        // 📅 PASO 1: Obtener y procesar el rango de fechas
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
        $from = Carbon::parse($from)->startOfDay()->format('Y-m-d H:i:s'); // 00:00:00
        $to   = Carbon::parse($to)->endOfDay()->format('Y-m-d H:i:s');     // 23:59:5

        // 🔎 PASO 2: Obtener el cupón a filtrar
        $coupon = strtolower($request->input('coupon', 'CAMILA2025'));
        // Convierte a minúsculas: "camila2025"
        // Si no viene, usa "CAMILA2025" por defecto

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
            ->groupBy(DB::raw('cart.dateCartFreg'), 'cartdet.intBoletoId')
            ->orderBy('cart.dateCartFreg')
            ->get();

        /* Resultado ejemplo de $data:
    [
        { date: "2025-10-01", intBoletoId: 11, total: 5 },
        { date: "2025-10-01", intBoletoId: 17, total: 3 },
        { date: "2025-10-03", intBoletoId: 11, total: 8 },
        // No hay datos para 2025-10-02 (día sin ventas)
    ]
    */

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

        /* Resultado de $dates:
    [
        "2025-10-01",
        "2025-10-02",
        "2025-10-03",
        ...
        "2025-10-13"
    ]
    */

        // 📦 PASO 5: Preparar arrays para ApexCharts
        $general = [];      // Ventas de Entrada General (ID 11)
        $light = [];        // Ventas de Entrada Light (ID 17)
        $categories = [];   // Etiquetas del eje X

        foreach ($dates as $date) {
            // Para CADA día del rango (incluso si no hubo ventas)

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

        /* Resultado final:
    $general = [5, 0, 8, 12, 0, ...]    // 0 en días sin ventas
    $light = [3, 0, 4, 7, 0, ...]       // 0 en días sin ventas
    $categories = ["01/10", "02/10", "03/10", ...]
    */

        // 🎯 PASO 6: Retornar JSON en formato ApexCharts
        return response()->json([
            'series' => [
                [
                    'name' => 'Entrada General',
                    'data' => $general,  // [5, 0, 8, 12, ...]
                ],
                [
                    'name' => 'Entrada Light',
                    'data' => $light,    // [3, 0, 4, 7, ...]
                ],
            ],
            'categories' => $categories,  // ["01/10", "02/10", ...]
        ]);
    }
}
