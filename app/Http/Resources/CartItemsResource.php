<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'key' => $this['key'],
            'product_id' => $this['id'],
            'quantity' => $this['quantity'],
            'quantity_limits' => $this['quantity_limits'],
            'product_name' => $this['name'],
            'images' => ImagesResource::collection($this['images']),
        ];
    }
}
