<?php

namespace App\Http\Resources\TicketConcern;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketConcernResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'department_id' => $this->department_id,
            'assigned_user' => $this->whenLoaded('assignedUser', function () {
                return [
                    'id' => $this->assignedUser->id,
                    'name' => $this->assignedUser->name,
                    'email' => $this->assignedUser->email,
                    'avatar' => $this->assignedUser->getAvatar(),
                    'gravatar' => $this->assignedUser->getGravatar(),
                ];
            }),
            'department' => $this->whenLoaded('department', function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
