<?php

namespace App\Http\Resources\TicketConcern;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketConcernSelectResource extends JsonResource
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
            'name' => $this->name
        ];
    }
}
