<?php

namespace App\Http\Controllers\Api\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // Keep for index, or remove if not using $request directly
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\Faq\FaqResource;
use App\Http\Requests\Dashboard\Admin\Faq\StoreRequest;
use App\Http\Requests\Dashboard\Admin\Faq\UpdateRequest;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Faq::query();

        // Apply search filter
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', $search)
                  ->orWhere('answer', 'like', $search);
            });
        }

        // Apply category filter
        if ($request->has('category') && !empty($request->input('category'))) {
            $query->where('category', $request->input('category'));
        }
        
        // TODO: Consider pagination if the list can grow very large
        $faqs = $query->orderBy('created_at', 'desc')->get();

        return FaqResource::collection($faqs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $faq = Faq::create($validatedData);

        if ($faq) {
            return response()->json(['message' => __('Data saved correctly'), 'faq' => new FaqResource($faq)]);
        }

        return response()->json(['message' => __('An error occurred while saving data')], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param Faq $faq
     * @return FaqResource
     */
    public function show(Faq $faq): FaqResource
    {
        return new FaqResource($faq);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param Faq $faq
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, Faq $faq): JsonResponse
    {
        $validatedData = $request->validated();

        if ($faq->update($validatedData)) {
            return response()->json(['message' => __('Data updated correctly'), 'faq' => new FaqResource($faq)]);
        }

        return response()->json(['message' => __('An error occurred while updating data')], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Faq $faq
     * @return JsonResponse
     */
    public function destroy(Faq $faq): JsonResponse
    {
        // Optional: Add check if FAQ is associated with anything before deleting, if applicable in the future.
        // For now, direct delete.

        if ($faq->delete()) {
            return response()->json(['message' => __('Data deleted correctly')]);
        }

        return response()->json(['message' => __('An error occurred while deleting data')], 500);
    }

    /**
     * Display a listing of the resource for public consumption by category.
     *
     * @param Request $request
     * @param string $category
     * @return AnonymousResourceCollection
     */
    public function publicIndexByCategory(Request $request, string $category): AnonymousResourceCollection
    {
        if (!in_array($category, ['wifi', 'general'])) {
            return FaqResource::collection([]); // Or return 404
        }

        $query = Faq::query()->where('category', $category);

        // Optionally, add search for public pages if needed, though typically not for simple FAQ lists.
        // if ($request->has('search') && !empty($request('search'))) {
        //     $search = '%' . $request('search') . '%';
        //     $query->where(function ($q) use ($search) {
        //         $q->where('question', 'like', $search)
        //           ->orWhere('answer', 'like', $search);
        //     });
        // }
        
        $faqs = $query->orderBy('created_at', 'asc')->get(); // Or order by a specific order column if added

        return FaqResource::collection($faqs);
    }
}
