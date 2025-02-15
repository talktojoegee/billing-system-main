<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepreciationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          "id"=>$this->id,
          "range"=>$this->range,
          //"ageTo"=>$this->building_age_to,
          "rate"=>$this->depreciation_rate,
          "value"=>$this->value,
        ];
    }
}
