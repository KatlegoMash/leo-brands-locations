<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SellersCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "contact_email" => "adops@advertisingsystem.com",
             "contact_address" => "Advertising System Inc., 101 Main Street, New York, NY 10101",
             "version" => "1.0",
             /*"identifiers" => [[
                 "name" => "TAG-ID",
                 "value" => "28cb65e5bbc0bd5f"
             ]],*/
            "sellers"=> $this->collection,
        ];
    }
}
