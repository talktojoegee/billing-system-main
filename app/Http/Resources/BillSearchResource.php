<?php

namespace App\Http\Resources;

use App\Models\BillPaymentLog;
use App\Models\ChargeRate;
use App\Models\Lga;
use App\Models\Objection;
use App\Models\PropertyClassification;
use App\Models\PropertyList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $objection = Objection::where('bill_id', $this->id)->first();
        $objectionCount = !empty($objection) ? $objection->count() : 0;
        $class = PropertyClassification::find($this->class_id);
        $property = PropertyList::find($this->property_id);
        $lga = Lga::find($this->lga_id);
        $chargeRate = ChargeRate::find($this->occupancy);
        //$payments = BillPaymentLog::where('bill_master', $this->)->sum();
        return [
            //'approvedDate'=> !empty($objection) ? date('d M, Y', strtotime($objection->date_approved)) : '',
            'billId'=>$this->billId ?? '',
            'assessmentNo'=>$this->assessment_no ?? '',
            'buildingCode'=>$this->buildingCode ?? '',
            'pavCode'=>$this->pav_code ?? '',
            'year'=>$this->year ?? '',
            //'requestId'=> !empty($objection) ? $objection->request_id : '',
            'zoneName'=>$this->zone_name ?? '',
            'categoryName'=> !empty($class) ?$class->class_name : '',
            'owner'=>!empty($property) ?$property->owner_name  : '',
            'billAmount'=>$this->bill_amount ?? '',
            'balance'=>$this->bill_amount  - $this->paid_amount,
            'lgaName'=>!empty($lga) ?$lga->lga_name  : '',
            'url'=>$this->url ?? '',
            'assessValue'=>$this->assessed_value ?? '',
            'rate'=>$this->cr ?? '',
            'occupancy'=>!empty($chargeRate) ? $chargeRate->occupancy  : '',
            //'objection'=>$objectionCount ?? 0,
            'special'=>$this->special,
            'status'=>$this->status,
            'propertyUse'=>$this->property_use ?? '',
            'propertyName'=>$this->property_name ?? '',
        ];
    }
}
