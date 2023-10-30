<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'item_id' => $this['id'],
            'product_name' => $this['name'],
            'product_id' => $this['product_id'],
            'variation_id' => $this['variation_id'],
            'quantity' => $this['quantity'],
            'subtotal' => $this['subtotal'],
            'total_tax' => $this['total_tax'],
            'total' => $this['total'],
            'price' => $this['price'],
            'image' => isset($this['image']['src']) ? $this['image']['src'] :'',
            'parent_name' => $this['parent_name']
        ];
    }
}
