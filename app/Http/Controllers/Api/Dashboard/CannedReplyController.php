<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\CannedReply\StoreRequest;
use App\Http\Requests\Dashboard\CannedReply\UpdateRequest;
use App\Http\Resources\CannedReply\CannedReplyResource;
use App\Models\CannedReply;
use Auth;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CannedReplyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(Request $request): JsonResponse
    {
        $sort = json_decode($request->get('sort', json_encode(['order' => 'asc', 'column' => 'created_at'], JSON_THROW_ON_ERROR)), true, 512, JSON_THROW_ON_ERROR);
        $currentUser = Auth::user();

        if (!$currentUser) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $itemsQuery = CannedReply::filter($request->all())
            ->with('user') // Eager load user for creator info
            ->where(function (Builder $query) use ($currentUser) {
                // Condition 1: Private replies of the current user
                $query->where(function (Builder $q_private) use ($currentUser) {
                    $q_private->where('shared', false)
                              ->where('user_id', $currentUser->id);
                });

                // Condition 2: Shared replies from relevant users
                $query->orWhere(function (Builder $q_shared) use ($currentUser) {
                    $q_shared->where('shared', true)
                             ->whereHas('user', function (Builder $userQuery) use ($currentUser) {
                                 $currentUserDepartmentIds = $currentUser->departments()->pluck('departments.id')->toArray();
                                 $currentUserCondoLocationId = $currentUser->condo_location_id;

                                 // If current user has no specific context (no departments AND no condo location),
                                 // they should not see any shared replies from others based on this logic.
                                 if (empty($currentUserDepartmentIds) && !$currentUserCondoLocationId) {
                                     // This will effectively prevent matching any creators for shared replies
                                     // if the current user has no departmental or location context.
                                     // They will only see their own private replies.
                                     $userQuery->whereRaw('1 = 0'); // No creator matches
                                 } else {
                                     $userQuery->where(function (Builder $creatorContextQuery) use ($currentUserDepartmentIds, $currentUserCondoLocationId) {
                                         if (!empty($currentUserDepartmentIds)) {
                                             $creatorContextQuery->whereHas('departments', function (Builder $deptQuery) use ($currentUserDepartmentIds) {
                                                 $deptQuery->whereIn('departments.id', $currentUserDepartmentIds);
                                             });
                                         }
                                         if ($currentUserCondoLocationId) {
                                             // If there was a department condition, this is an OR, otherwise it's a WHERE
                                             if (!empty($currentUserDepartmentIds)) {
                                                 $creatorContextQuery->orWhere('users.condo_location_id', $currentUserCondoLocationId);
                                             } else {
                                                 $creatorContextQuery->where('users.condo_location_id', $currentUserCondoLocationId);
                                             }
                                         }
                                     });
                                 }
                             });
                });
            });

        $items = $itemsQuery->orderBy($sort['column'], $sort['order'])
                           ->paginate((int) $request->get('perPage', 10));

        return response()->json([
            'items' => CannedReplyResource::collection($items->items()),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'perPage' => $items->perPage(),
                'total' => $items->total(),
                'totalPages' => $items->lastPage()
            ]
        ]);
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
        $cannedReply = new CannedReply();
        $cannedReply->user_id = Auth::id();
        $cannedReply->name = $request->get('name');
        $cannedReply->body = $request->get('body');
        $cannedReply->shared = $request->get('shared');
        if ($cannedReply->save()) {
            return response()->json(['message' => __('Data saved correctly'), 'cannedReply' => new CannedReplyResource($cannedReply)]);
        }
        return response()->json(['message' => __('An error occurred while saving data')], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  CannedReply  $cannedReply
     * @return JsonResponse
     */
    public function show(CannedReply $cannedReply): JsonResponse
    {
        return response()->json(new CannedReplyResource($cannedReply));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  CannedReply  $cannedReply
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, CannedReply $cannedReply): JsonResponse
    {
        $request->validated();
        if ($cannedReply->user_id !== Auth::id()) {
            return response()->json(['message' => __('This action is unauthorized')], Response::HTTP_FORBIDDEN);
        }
        $cannedReply->name = $request->get('name');
        $cannedReply->body = $request->get('body');
        $cannedReply->shared = $request->get('shared');
        if ($cannedReply->save()) {
            return response()->json(['message' => __('Data updated correctly'), 'cannedReply' => new CannedReplyResource($cannedReply)]);
        }
        return response()->json(['message' => __('An error occurred while saving data')], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CannedReply  $cannedReply
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(CannedReply $cannedReply): JsonResponse
    {
        if ($cannedReply->delete()) {
            return response()->json(['message' => 'Data deleted successfully']);
        }
        return response()->json(['message' => __('An error occurred while deleting data')], 500);
    }
}
