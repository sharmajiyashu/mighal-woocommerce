<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrdersResource extends JsonResource
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
            'order_id' => $this['id'],
            'status' => $this['status'],
            'currency' => $this['currency'],
            'discount_total' => $this['discount_total'],
            'discount_tax' => $this['discount_tax'],
            'shipping_total' => $this['shipping_total'],
            'shipping_tax' => $this['shipping_tax'],
            'total' => $this['total'],
            'total_tax' => $this['total_tax'],
            'order_key' => $this['order_key'],
            'billing' => $this['billing'],
            'shipping' => $this['shipping'],
            'payment_method' => $this['payment_method'],
            'line_items' => OrderItemResource::collection($this['line_items']),
        ];
    }
}
