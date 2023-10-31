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

        $sale_price = isset($this['prices']['sale_price']) ? $this['prices']['sale_price'] : "0";
        $total = isset($this['totals']['line_total']) ? $this['totals']['line_total'] : "0";
        return [
            'key' => $this['key'],
            'product_id' => $this['id'],
            'quantity' => $this['quantity'],
            'quantity_limits' => $this['quantity_limits'],
            'product_name' => $this['name'],
            'images' => ImagesResource::collection($this['images']),
            'sale_price' => $sale_price / 100,
            'total' => $total / 100,
        ];
    }
}
