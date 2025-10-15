<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InfluencerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
return [
            'id'     => $this->id,
            'name'   => $this->name,
            'social' => $this->social_handle,
            'email'  => $this->email,
            'phone'  => $this->phone_number,
            'code'   => $this->code,
            'code_description' => $this->code_description,
            'role'   => $this->role,
            'status' => $this->status,
            'actions' => [
                'view'   => true,
                'edit'   => true,
                'delete' => false,
            ],
        ];
    }
}
