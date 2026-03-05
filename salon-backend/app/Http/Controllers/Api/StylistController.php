<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStylistRequest;
use App\Http\Requests\UpdateStylistRequest;
use App\Http\Resources\StylistResource;
use App\Models\Stylist;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StylistController extends Controller
{
    public function index(): JsonResponse
    {
        $stylists = Stylist::query()->with('user')->orderBy('id')->get();

        return response()->json(StylistResource::collection($stylists));
    }

    public function store(StoreStylistRequest $request): JsonResponse
    {
        $stylist = Stylist::query()->create($request->validated());

        return response()->json(new StylistResource($stylist->load('user')), 201);
    }

    public function show(Stylist $stylist): JsonResponse
    {
        return response()->json(new StylistResource($stylist->load('user')));
    }

    public function update(UpdateStylistRequest $request, Stylist $stylist, AuditLogger $logger): JsonResponse
    {
        $before = $stylist->getAttributes();
        $stylist->update($request->validated());

        $stylist->refresh();
        $logger->log('update', $stylist, $request->user()?->id, $before, $stylist->getAttributes(), $request);

        return response()->json(new StylistResource($stylist->load('user')));
    }

    public function destroy(Request $request, Stylist $stylist, AuditLogger $logger): JsonResponse
    {
        $before = $stylist->getAttributes();
        $logger->log('delete', $stylist, $request->user()?->id, $before, null, $request);
        $stylist->delete();

        return response()->json(status: 204);
    }
}
