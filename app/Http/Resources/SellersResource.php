<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class SellersResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'seller_id' => $this->seller_id,
             "name" =>$this->name,
             "domain" => $this->domain,
             "seller_type" => $this->seller_type,
             "is_confidential" => $this->is_confidential
        ];
    }
}
