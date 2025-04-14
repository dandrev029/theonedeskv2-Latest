<?php

namespace App\Http\Resources\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this;
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'unit_number' => $user->unit_number,
            'condo_location_id' => $user->condo_location_id,
            'avatar' => $user->getAvatar(),
            'gravatar' => $user->getGravatar(),
            'role_id' => $user->role_id,
            'status' => (bool) $user->status,
            'created_at' => $user->created_at->toISOString(),
            'updated_at' => $user->updated_at->toISOString(),
            'departments' => $user->departments
        ];
    }
}
