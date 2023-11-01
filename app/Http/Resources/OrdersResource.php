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

        $data = $this['line_items'];
        $quantity = 0;
        if(is_array($data)){
            foreach($data as $key=>$val){
                $quantity += $val['quantity'];
            }
        }
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
            'date_created' => date('M d, Y',strtotime($this['date_created'])),
            'shipping' => $this['shipping'],
            'payment_method' => $this['payment_method'],
            'quantity' => $quantity,
            'line_items' => OrderItemResource::collection($this['line_items']),

        ];
    }
}
