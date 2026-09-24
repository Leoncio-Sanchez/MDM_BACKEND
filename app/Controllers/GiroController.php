<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Giro;
use App\Requests\Giros\StoreGiroRequest;
use App\Requests\Giros\UpdateGiroRequest;
use App\Resources\GiroResource;
use Illuminate\Http\JsonResponse;

class GiroController extends Controller
{
    public function index(): JsonResponse
    {
        $giros = Giro::query()->orderBy('nombre')->get();

        return response()->json(GiroResource::collection($giros)->resolve());
    }

    public function show(int|string $id): JsonResponse
    {
        $giro = Giro::findOrFail((int) $id);

        return response()->json((new GiroResource($giro))->resolve());
    }

    public function store(StoreGiroRequest $request): JsonResponse
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $data = $request->validated();
        $data['usuario_id'] = $user->id;
        $data['fecha_digitacion'] = now();

        $giro = Giro::create($data);

        return response()->json((new GiroResource($giro))->resolve(), 201);
    }

    public function update(UpdateGiroRequest $request, int|string $id): JsonResponse
    {
        $user = $request->user('api');
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $giro = Giro::findOrFail((int) $id);
        $data = array_filter($request->validated(), fn ($v) => $v !== null);
        $data['usuario_id'] = $user->id;

        $giro->update($data);

        return response()->json((new GiroResource($giro))->resolve());
    }

    public function destroy(int|string $id): JsonResponse
    {
        $giro = Giro::findOrFail((int) $id);
        $giro->delete();

        return response()->json(null, 204);
    }
}
