<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'code'       => $this->coupon,
            'date_shop'        => optional($this->cart)->dateCartFreg, // fecha del carrito
            'price'       => $this->decCartdetPu,       // asumiendo que en Cart está el precio o total
            'type'       => $this->getTypeEntrie(),                 // del accessor que hicimos
        ];
    }
}
