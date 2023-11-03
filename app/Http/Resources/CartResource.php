<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $totals = $this['totals'];
        if(isset($totals['total_items'])){
            $totals['total_items'] = $totals['total_items'] / 100;
        }
        if(isset($totals['total_price'])){
            $totals['total_price'] = $totals['total_price'] / 100;
        }
        return [
            'items' =>  CartItemsResource::collection($this['items']),
            'coupons' => $this['coupons'],
            'fees' => $this['fees'],
            'totals' => $totals,
            'shipping_address' => $this['shipping_address'],
            'billing_address' => $this['billing_address'],
            'needs_payment' => $this['needs_payment'],
            'needs_shipping' => $this['needs_shipping'],
            'items_count' => $this['items_count'],
            'payment_methods' => $this['payment_methods'],
        ];
    }
}
