<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $image = isset($this['image']['src']) ? $this['image']['src'] :'';
        return [
            'category_id' => $this['id'],
            'category_name' => $this['name'],
            'image' => $image,
            'product_data' => isset($this['product_data']) ? $this['product_data'] : [],
        ];
    }
}
