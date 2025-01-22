<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyDetailResource extends JsonResource
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
            "buildingCode"=>$this->building_code,

            "pavCode"=>$this->pav_code,
            "title"=>$this->title ?? '',
            "address"=>$this->address ?? '',
            "lgaName"=>$this->getLGA->lga_name ?? '',
            "size"=>$this->size ?? '',
            "area"=>$this->area ?? '',
            "zoneName"=>$this->sub_zone ?? '',
            "occupancy"=>$this->occupant ?? '',
            "class"=>$this->getPropertyClassification->class_name ?? '',
            "kgTin"=>$this->owner_kgtin,
            "ownerEmail"=>$this->owner_email,
            "ownerGsm"=>$this->owner_gsm,
            "owner"=>$this->owner_name ?? '',

            "image"=>$this->image,
            "borehole"=>$this->borehole,
            "power"=>$this->power,
            "refuse"=>$this->refuse,
            "storey"=>$this->storey,
            "water"=>$this->water,
            "payStatus"=>$this->pay_status,
            "buildingAge"=>$this->building_age,
        ];

        //return parent::toArray($request);
    }
}
