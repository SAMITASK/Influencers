<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartDetailResource;
use App\Models\CartDetail;
use App\Models\UserInfluencer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $query = CartDetail::with(['cart', 'influencer']);
        $user = $request->user();

        // 🔐 Validar autenticación
        if (!$user) {
            return response()->json([
                'data' => [],
                'total' => 0,
                'per_page' => 10,
                'current_page' => 1,
                'stats' => [
                    'total' => 0,
                    'totalFDT' => 0,
                    'totalLight' => 0,
                ],
            ]);
        }

        // 👑 Filtro por rol
        if ($user->isAdmin()) {
            $influencerFilter = $request->input('influencer');
            if ($influencerFilter && $influencerFilter !== 'ALL') {
                $query->whereRaw('LOWER(coupon) = ?', [strtolower($influencerFilter)]);
            }
        } elseif ($user->isInfluencer()) {
            if ($user->code) {
                $query->whereRaw('LOWER(coupon) = ?', [strtolower($user->code)]);
            } else {
                return response()->json([
                    'data' => [],
                    'total' => 0,
                    'per_page' => 10,
                    'current_page' => 1,
                    'stats' => [
                        'total' => 0,
                        'totalFDT' => 0,
                        'totalLight' => 0,
                    ],
                ]);
            }
        }

        // 🎟️ Tipo de entrada
        $type = $request->input('type');
        if ($type === 'ALL') {
            $query->whereIn('intBoletoId', [11, 17]);
        } elseif (!empty($type)) {
            $query->where('intBoletoId', $type);
        }

        // 📅 Rango de fechas
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

        // 📊 Totales globales optimizados (una sola consulta)
        $stats = (clone $query)
            ->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN intBoletoId = 11 THEN 1 ELSE 0 END) as totalFDT,
            SUM(CASE WHEN intBoletoId = 17 THEN 1 ELSE 0 END) as totalLight
        ')
            ->first();

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
            'stats' => [
                'total' => (int) $stats->total,
                'totalFDT' => (int) $stats->totalFDT,
                'totalLight' => (int) $stats->totalLight,
            ],
        ]);
    }


    public function chartData(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->emptyChartResponse();
        }

        // 👑 Si es ADMIN
        if ($user->isAdmin()) {
            $influencerFilter = $request->input('influencer');

            // Si selecciona "ALL", mostrar gráfico por influencer
            if ($influencerFilter === 'ALL') {
                return $this->chartByInfluencer($request);
            }

            // Si selecciona un influencer específico, gráfico normal
            $coupon = strtolower($influencerFilter);
        }
        // 🎤 Si es INFLUENCER
        else if ($user->isInfluencer()) {
            if (!$user->code) {
                return $this->emptyChartResponse();
            }
            $coupon = strtolower($user->code);
        }

        return $this->chartByDate($request, $coupon);
    }

    /**
     * 📊 Gráfico por fecha (normal)
     */
    private function chartByDate(Request $request, $coupon)
    {
        $dateRange = $request->input('date');
        $type = $request->input('type');

        if ($dateRange) {
            if (strpos($dateRange, ' a ') !== false) {
                [$from, $to] = explode(' a ', $dateRange);
            } else {
                $from = $to = $dateRange;
            }
        } else {
            $from = now()->startOfMonth()->format('Y-m-d');
            $to   = now()->format('Y-m-d');
        }

        $from = Carbon::parse($from)->startOfDay()->format('Y-m-d H:i:s');
        $to   = Carbon::parse($to)->endOfDay()->format('Y-m-d H:i:s');

        $query = CartDetail::select(
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
            ->orderBy(DB::raw('DATE(cart.dateCartFreg)'));

        $data = $query->get();

        $fromDate = Carbon::parse($from);
        $toDate = Carbon::parse($to);
        $daysDiff = $fromDate->diffInDays($toDate) + 1;

        if ($daysDiff > 20) {
            $dates = $data->pluck('date')->unique()->sort()->values()->toArray();
        } else {
            $dates = [];
            $current = clone $fromDate;
            while ($current <= $toDate) {
                $dates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }

        $general = [];
        $light = [];
        $categories = [];

        foreach ($dates as $date) {
            $generalSale = $data->where('date', $date)->where('intBoletoId', 11)->first();
            $lightSale = $data->where('date', $date)->where('intBoletoId', 17)->first();

            $general[] = $generalSale ? (int) $generalSale->total : 0;
            $light[] = $lightSale ? (int) $lightSale->total : 0;
            $categories[] = Carbon::parse($date)->format('d/m');
        }

        return response()->json([
            'series' => [
                ['name' => 'Entrada General', 'data' => $general],
                ['name' => 'Entrada Light', 'data' => $light],
            ],
            'categories' => $categories,
        ]);
    }

    private function emptyChartResponse()
    {
        return response()->json([
            'series' => [
                ['name' => 'Entrada General', 'data' => []],
                ['name' => 'Entrada Light', 'data' => []],
            ],
            'categories' => [],
        ]);
    }

    /**
     * 📊 Gráfico agrupado por influencer (solo para admin con "ALL")
     */
    private function chartByInfluencer(Request $request)
    {
        $dateRange = $request->input('date');
        $type = $request->input('type');
        $page = (int) $request->input('page', 1);
        $itemsPerPage = (int) $request->input('itemsPerPage', 10);

        // Fechas
        if ($dateRange) {
            if (strpos($dateRange, ' a ') !== false) {
                [$from, $to] = explode(' a ', $dateRange);
            } else {
                $from = $to = $dateRange;
            }
        } else {
            $from = now()->startOfMonth()->format('Y-m-d');
            $to = now()->format('Y-m-d');
        }

        $from = Carbon::parse($from)->startOfDay();
        $to   = Carbon::parse($to)->endOfDay();

        // Consulta
        $query = CartDetail::select(
            'cartdet.coupon',
            'cartdet.intBoletoId',
            DB::raw('COUNT(*) as total')
        )
            ->join('cart', 'cartdet.intCartId', '=', 'cart.intCartId')
            ->whereNotNull('cartdet.coupon')
            ->where('cartdet.coupon', '!=', '')
            ->when($type === 'ALL', fn($q) => $q->whereIn('cartdet.intBoletoId', [11, 17]))
            ->when(in_array($type, [11, 17]), fn($q) => $q->where('cartdet.intBoletoId', $type))
            ->whereBetween('cart.dateCartFreg', [$from, $to])
            ->groupBy('cartdet.coupon', 'cartdet.intBoletoId')
            ->get();

        $influencers = UserInfluencer::whereIn('code', $query->pluck('coupon')->unique())
            ->get()
            ->keyBy('code');

        $totals = [];
        foreach ($query->groupBy('coupon') as $coupon => $sales) {
            $general = $sales->where('intBoletoId', 11)->sum('total');
            $light = $sales->where('intBoletoId', 17)->sum('total');

            $influencer = $influencers->get($coupon);
            $totals[] = [
                'code' => $coupon,
                'name' => $influencer?->name ?? $coupon,
                'general' => (int) $general,
                'light' => (int) $light,
                'total' => (int) ($general + $light),
            ];
        }

        usort($totals, fn($a, $b) => $b['total'] - $a['total']);

        $top10 = array_slice($totals, 0, 10);
        $ranking = collect($totals)
            ->map(fn($item, $i) => array_merge($item, ['position' => $i + 1]));

        // Paginación manual
        $paginatedRanking = $ranking
            ->forPage($page, $itemsPerPage)
            ->values()
            ->toArray();

        return response()->json([
            'series' => [
                ['name' => 'Entrada General', 'data' => array_column($top10, 'general')],
                ['name' => 'Entrada Light', 'data' => array_column($top10, 'light')],
            ],
            'categories' => array_column($top10, 'name'),
            'ranking' => $ranking, // todos (para exportar o filtros)
            'paginated' => $paginatedRanking, // solo página actual
            'total' => count($totals),
        ]);
    }


    /**
     * 📋 Obtener lista de influencers (solo para admins)
     */
    public function getInfluencers(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $influencers = UserInfluencer::where('role', 'influencer')
            ->where('status', 'active')
            ->whereNotNull('code')
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return response()->json($influencers);
    }
}
