<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartDetailResource;
use App\Models\CartDetail;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $query = CartDetail::with('cart');

        // 🔎 Filtro por cupón
            $coupon = strtolower($request->coupon);
            $query->whereRaw('LOWER(coupon) = ?', ['CAMILA2025']);

        $type = $request->input('type');

        // 🎟️ Filtro por tipo de entrada
        if ($type === 'ALL') {
            $query->whereIn('intBoletoId', [11, 17]);
        } elseif (!empty($type)) {
            $query->where('intBoletoId', $type);
        }

        // 📅 Filtro por rango de fechas (fecha del carrito)
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereHas('cart', function ($q) use ($request) {
                $q->whereBetween('dateCartFreg', [$request->from, $request->to]);
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
}
