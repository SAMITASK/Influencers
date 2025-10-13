<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InfluencerRequest;
use App\Http\Resources\InfluencerResource;
use App\Models\InfluencerCode;
use App\Models\UserInfluencer;
use Illuminate\Http\Request;

class Influencer extends Controller
{
    public function index(Request $request)
    {
        $query = UserInfluencer::with(['codes:id,influencer_id,code']);

        // 🔍 Búsqueda
        if ($request->filled('q')) {
            $query->where(function ($q2) use ($request) {
                $q2->where('name', 'like', "%{$request->q}%")
                    ->orWhere('phone_number', 'like', "%{$request->q}%")
                    ->orWhere('email', 'like', "%{$request->q}%")
                    ->orWhere('social_handle', 'like', "%{$request->q}%");
            });
        }

        // 📌 Orden
        $allowed = ['name', 'email', 'phone_number', 'status'];
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
    public function store(InfluencerRequest  $request)
    {
        try {
            // 1️⃣ Crear influencer
            $influencer = UserInfluencer::create($request->validated());

            // 2️⃣ Guardar códigos si existen
            if ($request->filled('codes')) {
                foreach ($request->codes as $code) {
                    $influencer->codes()->create([
                        'code' => $code,
                    ]);
                }
            }

            // 3️⃣ Retornar influencer con códigos
            return response()->json([
                'message' => 'Influencer creado correctamente.',
                'data' => $influencer->load('codes') // incluir los códigos en la respuesta
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

            // 1️⃣ Actualizar datos del influencer
            $influencer->update($request->validated());

            // 2️⃣ Sincronizar códigos si se enviaron
            if ($request->has('codes')) {
                $existingCodes = $influencer->codes->pluck('code')->toArray();
                $newCodes = $request->codes;

                // 1️⃣ Eliminar los que ya no estén
                $influencer->codes()
                    ->whereNotIn('code', $newCodes)
                    ->delete();

                // 2️⃣ Agregar los nuevos que no existan
                foreach ($newCodes as $code) {
                    if (!in_array($code, $existingCodes)) {
                        $influencer->codes()->create(['code' => $code]);
                    }
                }
            }

            // 3️⃣ Retornar influencer actualizado con códigos
            return response()->json([
                'success' => true,
                'message' => 'Influencer actualizado correctamente.',
                'data' => $influencer->load('codes')
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
