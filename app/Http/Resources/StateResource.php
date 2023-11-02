<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $state = [];
        // print_r($this['countries']);die;

        if(is_array($this['countries'])){
            foreach($this['countries'] as $key=>$val){
                if(is_array($val['states'])){
                    foreach($val['states'] as $k=>$v){
                        if(!empty($v)){
                            $state[] = $v;
                        }
                    }
                }
            }
        }
        return  $state;
    }
}
