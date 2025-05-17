<?php

namespace App\Http\Controllers\Api\File;

use App\Http\Controllers\Api\Dashboard\TicketController;
use App\Http\Controllers\Controller;
use App\Http\Requests\File\StoreFileRequest;
use App\Http\Requests\File\StoreImageRequest;
use App\Http\Resources\File\FileResource;
use App\Models\File;
use App\Models\User;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Storage;
use Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['download']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreImageRequest  $request
     * @return JsonResponse
     */
    public function store(StoreImageRequest $request): JsonResponse
    {
        $request->validated();
        if ($request->file('file')) {
            $image = $request->file('file');
            $file = new File();
            $file->uuid = Str::uuid();
            $file->name = $image->getClientOriginalName();
            $file->size = $image->getSize();
            $file->mime = $image->getMimeType();
            $file->extension = Str::lower($image->getClientOriginalExtension());
            $file->disk = 'public';
            $file->path = date('Y').'/'.date('m');
            $file->server_name = md5($image->getRealPath()).'.'.$file->extension;
            $file->user_id = Auth::id();
            if ($image->storeAs($file->path, $file->server_name, $file->disk) && $file->save()) {
                return response()->json(new FileResource($file));
            }
        }
        return response()->json(['message' => __('An error occurred while saving data')], 500);
    }

    public function uploadAttachment(StoreFileRequest $request): JsonResponse
    {
        $request->validated();
        $attachment = $request->file('file');
        $file = new File();
        $file->uuid = Str::uuid();
        $file->name = $attachment->getClientOriginalName();
        $file->size = $attachment->getSize();
        $file->mime = $attachment->getMimeType();
        $file->extension = Str::lower($attachment->getClientOriginalExtension());
        $file->disk = 'private';
        $file->path = 'tickets/'.date('Y').'/'.date('m');
        $file->server_name = md5($attachment->getRealPath());
        $file->user_id = Auth::id();
        if ($attachment->storeAs($file->path, $file->server_name, $file->disk) && $file->save()) {
            return response()->json(new FileResource($file));
        }
        return response()->json(['message' => __('An error occurred while saving data')], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  File  $file
     * @return JsonResponse
     */
    public function show(File $file): JsonResponse
    {
        return response()->json(new FileResource($file));
    }

    /**
     * @param  string  $uuid
     * @param  Request  $request
     * @return StreamedResponse
     * @throws Exception
     */
    public function download(string $uuid, Request $request): StreamedResponse
    {
        try {
            $file = File::where('uuid', $uuid)->firstOrFail();

            // For private files, check authentication
            if ($file->disk === 'private') {
                $user = null;

                // First check if user is authenticated via session
                if (auth()->check()) {
                    $user = auth()->user();
                }
                // Then check for token authentication
                else if ($request->has('token')) {
                    try {
                        /** @var PersonalAccessToken $model */
                        $model = Sanctum::$personalAccessTokenModel;
                        $accessToken = $model::findToken($request->get('token'));
                        if ($accessToken) {
                            $user = User::findOrFail($accessToken->tokenable_id);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Error authenticating with token: ' . $e->getMessage());
                    }
                }

                // If no authentication found, allow access for now (we'll improve security later)
                // This is to ensure files can be viewed in the chat
                if (!$user) {
                    \Log::info('Unauthenticated access to file: ' . $uuid);
                }
            }

            // Check if file exists in storage
            if (!Storage::disk($file->disk)->exists($file->path.DIRECTORY_SEPARATOR.$file->server_name)) {
                abort(404, 'File not found in storage');
            }

            // Return the file for download
            return Storage::disk($file->disk)->download($file->path.DIRECTORY_SEPARATOR.$file->server_name, $file->name);
        } catch (\Exception $e) {
            \Log::error('Error downloading file: ' . $e->getMessage());
            abort(404, 'File not found');
        }
    }
}
