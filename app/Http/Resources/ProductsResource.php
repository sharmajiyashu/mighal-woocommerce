<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $image = [];
        if(is_array($this['images'])){
            foreach($this['images'] as $key => $val){
                $image[] = $val['src'];
            }
        }
        return [
            'product_id' => $this['id'],
            'product_name' => $this['name'],
            'type' => $this['type'],
            'description' => $this['description'],
            'short_description' => $this['short_description'],
            'sale_price' => $this['sale_price'],
            'regular_price' => $this['regular_price'],
            'price' => $this['price'],
            // 'categories' => $this['categories'],
            'images' => ImagesResource::collection($this['images']),
            'attributes' => $this['attributes'],
            'default_attributes' => $this['default_attributes'],
            'variations' => $this['variations'],
            'price_html' => $this['price_html'],
            'related_ids' => $this['related_ids'],
            'stock_status' => $this['stock_status'],
            'category_name' => isset($this['categories'][0]['name']) ? $this['categories'][0]['name'] :'',
            'category_id' => isset($this['categories'][0]['id']) ? $this['categories'][0]['id'] :'',
        ];
    }
}
