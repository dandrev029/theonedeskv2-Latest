<?php

namespace App\Http\Controllers\Api\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Admin\CondoLocation\StoreRequest;
use App\Http\Requests\Dashboard\Admin\CondoLocation\UpdateRequest;
use App\Http\Resources\CondoLocation\CondoLocationResource;
use App\Http\Resources\CondoLocation\CondoLocationSelectResource;
use App\Models\CondoLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CondoLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return CondoLocationResource::collection(CondoLocation::all());
    }

    /**
     * Display a listing of the resource for select.
     *
     * @return AnonymousResourceCollection
     */
    public function select(): AnonymousResourceCollection
    {
        return CondoLocationSelectResource::collection(CondoLocation::where('status', true)->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest  $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $request->validated();
        $condoLocation = new CondoLocation();
        $condoLocation->name = $request->get('name');
        $condoLocation->status = $request->get('status', true);
        
        if ($condoLocation->save()) {
            return response()->json(['message' => __('Data saved correctly'), 'condo_location' => new CondoLocationResource($condoLocation)]);
        }
        
        return response()->json(['message' => __('An error occurred while saving data')], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  CondoLocation  $condoLocation
     * @return CondoLocationResource
     */
    public function show(CondoLocation $condoLocation): CondoLocationResource
    {
        return new CondoLocationResource($condoLocation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  CondoLocation  $condoLocation
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, CondoLocation $condoLocation): JsonResponse
    {
        $request->validated();
        $condoLocation->name = $request->get('name');
        $condoLocation->status = $request->get('status');
        
        if ($condoLocation->save()) {
            return response()->json(['message' => __('Data updated correctly'), 'condo_location' => new CondoLocationResource($condoLocation)]);
        }
        
        return response()->json(['message' => __('An error occurred while updating data')], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CondoLocation  $condoLocation
     * @return JsonResponse
     */
    public function destroy(CondoLocation $condoLocation): JsonResponse
    {
        if ($condoLocation->users()->count() > 0) {
            return response()->json(['message' => __('Cannot delete condo location with associated users')], 422);
        }
        
        if ($condoLocation->delete()) {
            return response()->json(['message' => __('Data deleted correctly')]);
        }
        
        return response()->json(['message' => __('An error occurred while deleting data')], 500);
    }
}
