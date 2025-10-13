<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
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
            'codes'  => $this->codes->pluck('code')->implode(', '),
            'status' => $this->status, // Ej: "Active"
            'actions' => [
                'view'   => true,
                'edit'   => true,
                'delete' => false,
            ],
        ];
    }
}
