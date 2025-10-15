<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserInfluencer;

class CartDetailResource extends JsonResource
{
    public function toArray($request)
    {
        // Buscar el influencer por el código del cupón
        $influencer = null;
        if ($this->coupon) {
            $influencer = UserInfluencer::where('code', $this->coupon)->first();
        }

        return [
            'code'       => $this->coupon,
            'influencer' => $influencer ? $influencer->name : null, // 👈 Nombre del influencer
            'date_shop'  => optional($this->cart)->dateCartFreg,
            'price'      => $this->decCartdetPu,
            'type'       => $this->getTypeEntrie(),
        ];
    }
}
