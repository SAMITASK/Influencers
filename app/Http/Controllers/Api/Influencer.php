<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfluencerRequest;
use App\Http\Resources\InfluencerResource;
use App\Models\UserInfluencer;
use Illuminate\Http\Request;

class Influencer extends Controller
{
    public function index(Request $request)
    {
        $query = UserInfluencer::query();

        // 🔍 Búsqueda
        if ($request->filled('q')) {
            $query->where(function ($q2) use ($request) {
                $q2->where('name', 'like', "%{$request->q}%")
                    ->orWhere('phone_number', 'like', "%{$request->q}%")
                    ->orWhere('email', 'like', "%{$request->q}%")
                    ->orWhere('social_handle', 'like', "%{$request->q}%")
                    ->orWhere('code', 'like', "%{$request->q}%"); // 🆕 Búsqueda por código
            });
        }

        // 📌 Orden
        $allowed = ['name', 'email', 'phone_number', 'status', 'code'];
        $sortBy = $request->input('sortBy', 'name');
        $orderBy = $request->input('orderBy', 'asc');

        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $orderBy === 'desc' ? 'desc' : 'asc');
        }

        // 📄 Paginación
        $perPage = max((int) $request->input('itemsPerPage', 10), 1);
        $influencers = $query->paginate($perPage);

        // 🎯 Devolvemos los datos formateados por el Resource
        return InfluencerResource::collection($influencers)
            ->additional([
                'totalUsers' => $influencers->total(),
                'per_page' => $influencers->perPage(),
                'current_page' => $influencers->currentPage(),
            ]);
    }

    // ➕ Crear influencer
    public function store(InfluencerRequest $request)
    {
        try {
            // 1️⃣ Crear influencer con código incluido
            $influencer = UserInfluencer::create($request->validated());

            // 2️⃣ Retornar influencer creado
            return response()->json([
                'message' => 'Influencer creado correctamente.',
                'data' => $influencer
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el influencer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✏️ Actualizar influencer
    public function update(InfluencerRequest $request, $id)
    {
        try {
            $influencer = UserInfluencer::findOrFail($id);

            // 1️⃣ Actualizar datos del influencer (incluye el código)
            $influencer->update($request->validated());

            // 2️⃣ Retornar influencer actualizado
            return response()->json([
                'success' => true,
                'message' => 'Influencer actualizado correctamente.',
                'data' => $influencer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el influencer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
