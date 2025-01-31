<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\BillDetailResource;
use App\Http\Resources\BillingRecordResource;
use App\Http\Resources\DashboardStatisticsResource;
use App\Http\Resources\OutstandingBillResource;
use App\Http\Resources\PaidBillResource;
use App\Http\Resources\RetrieveBillResource;
use App\Models\Billing;
use App\Models\Lga;
use App\Models\PropertyAssessmentValue;
use App\Models\PropertyList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BillingController extends Controller
{
    public function __construct(){

    }

    public function retrieveBills(Request $request){
        $validator = Validator::make($request->all(),[
            //"lgaId"=>"required",
            "year"=>"required",
            "billedBy"=>"required",
        ],[
            //"lgaId.required"=>"LGA value is required",
            "year.required"=>"Year field is required",
            "billedBy.required"=>"",
        ]);
        if($validator->fails() ){
            return response()->json([
                "errors"=>$validator->messages()
            ],422);
        }

        return new RetrieveBillResource($request);

    }

    public function test(){
        echo "Test value.";
    }


    public function processBill(Request $request)
    {
        $year = $request->year;
        $lgaId = $request->lgaId;

        $validator = Validator::make($request->all(), [
            "lgaId" => "required",
            "year" => "required",
            //"billedBy"=>"required",
        ], [
            "lgaId.required" => "LGA value is required",
            "year.required" => "Year field is required",
            //"billedBy.required"=>"",
        ]);
        if ($validator->fails()) {
            return ApiResponse::error($validator->messages(), 422);

        }

        $currentYear = date('Y');
        if ($year > $currentYear) {
            return ApiResponse::error("Whoops! You can't process bill ahead.", 400);
        }
        $propertyLists = [];
        if ($lgaId == 0) { //All locations/LGAs
            $propertyLists = PropertyList::orderBy('id', 'DESC')->get();
        } else {
            $propertyLists = PropertyList::where('lga_id', $lgaId)/*->take(10)*/ ->get();
        }


        if (empty($propertyLists)) {
            return ApiResponse::error("Whoops! There is nothing to process", 400);
        }
        // Check if a bill for the specified year and LGA already exists
        /*$existingBills = Billing::where('lga_id', $request->lgaId)->where('year', $request->year)->get();

        if (count($existingBills) > 0) {
            return ApiResponse::error("Whoops! Bill for the specified year and LGA has already been processed.",400);
        }*/


        foreach ($propertyLists as $list) {
            //echo "LGA ID:: ".$list->lga_id;
            $existingBill = Billing::getBillByYearLgaId($year, $list->lga_id);
            //if(empty($existingBill)){ //If there is no existing bill
            // echo "Existing Bill ID:: ".$existingBill->id;

            $pavOptional = PropertyAssessmentValue::where("pav_code", $list->pav_code)->first();

            /*
             * Tie Government = State government, religious = commercial, recreational = commercial
             * Religious =
             * Residential =
             * Commercial =
             * Tie Owner to = Owner occupied; tenant to = 3rd Party Only
             */
            $lga = Lga::find($list->lga_id);
            if (!empty($pavOptional) && !empty($lga)) {
                $uniqueNumber = uniqid();
                $billAmount = ($pavOptional->value_rate / 100) * $pavOptional->assessed_amount;
                $billing = new Billing();
                $billing->building_code = $list->building_code ?? null;
                $billing->assessment_no = $uniqueNumber;
                $billing->assessed_value = $pavOptional->assessed_amount ?? 0;
                $billing->bill_amount = $billAmount ?? 0;
                $billing->year = $year;
                $billing->entry_date = now();
                $billing->billed_by = 1;
                $billing->paid = 0;
                $billing->paid_amount = 0.00;
                $billing->objection = 0;
                $billing->lga_id = $list->lga_id; //$request->lgaId;
                $billing->property_id = $list->id;
                $billing->bill_rate = $pavOptional->value_rate ?? 0;
                $billing->pav_code = $pavOptional->pav_code;
                $billing->zone_name = $list->sub_zone ?? '';
                $billing->url = substr(sha1( (time()+rand(9,99999)) ), 29, 40);
                //
                //occupancy
                $billing->class_id = $list->class_id;
                $billing->occupancy = $list->occupant;
                $billing->save();
            }


            //}

        }
        //return response()->json(['data'=>$counter],200);
        return response()->json(['data'=>"Bill processed!"], 201);  // BillingRecordResource::collection($this->_fetchProcessedBills($request->year, $request->lgaId));

    }

    private function _fetchProcessedBills($year, $lgaId){
        return Billing::where("year", $year)->where("lga_id", $lgaId)->get();
    }

    public function showBillDataOnDashboard(Request $request){

        return response()->json($this->getMonthlyBillPaymentByYear($request->year));
    }


    public function showDashboardStatistics(Request $request){
        return new DashboardStatisticsResource($request);
    }


    public function showPropertyDistributionByZones(Request $request){

        return response()->json(
            [
                'zone'=>$this->getPropertyDistributionByZones($request->year),
                'lga'=>$this->getPropertyDistributionByLGA($request->year)
            ]
        );
    }




    public function showPropertyDistributionByLGA(){

        return response()->json($this->getPropertyDistributionByLGA());
    }



    public function showOutstandingBills(Request $request){
        $limit = $request->limit ?? 0;
        $skip = $request->skip ?? 0;
        return response()->json([
            'data'=>OutstandingBillResource::collection(Billing::getBills($limit, $skip, 0, 0, 3)),
            'total'=>Billing::getBillsByParams(0,0,3)->count(),
            'grossBills'=>Billing::getBillsByParams(0,0,3)->sum('bill_amount'),
            'grossAmountPaid'=>Billing::getBillsByParams(0,0,3)->sum('paid_amount'),
            'balanceAmount'=>(Billing::getBillsByParams(0,0,3)->sum('bill_amount') - Billing::getBillsByParams(0,0,3)->sum('paid_amount')),
        ],200);
    }

    public function showBills(Request $request){
        $limit = $request->limit ?? 0;
        $skip = $request->skip ?? 0;
        $status = $request->status ?? 0;
        return response()->json([
            'data'=>OutstandingBillResource::collection(Billing::getBillsByStatus($limit, $skip, $status, )),
            'total'=>Billing::getBillsByParamsByStatus($status)->count(),
        ],200);
    }


    public function showPaidBills(Request $request){
        $limit = $request->limit ?? 0;
        $skip = $request->skip ?? 0;
        return response()->json([
            'data'=>PaidBillResource::collection(Billing::getBills($limit, $skip, 1, 0, 3)),
            'total'=>Billing::getBillsByParams(1,0,3)->count(),
            'grossBills'=>Billing::getBillsByParams(1,0,3)->sum('bill_amount'),
            'grossAmountPaid'=>Billing::getBillsByParams(1,0,3)->sum('paid_amount'),
            'balanceAmount'=>(Billing::getBillsByParams(1,0,3)->sum('bill_amount') - Billing::getBillsByParams(1,0,3)->sum('paid_amount')),
        ]);
    }




    public function showBillDetails($url){
        $billDetail = Billing::where('url',$url)->first();

        if (!$billDetail) {
            return response()->json([
                'message' => 'Whoops! No record found.'
            ], 404);
        }

        return new BillDetailResource($billDetail);
    }







    private function getMonthlyBillPaymentByYear($year){
        $billsData = DB::table('billings')
            ->selectRaw('MONTH(created_at) AS month, SUM(paid_amount) AS totalBillAmount')
            ->whereYear('created_at', '=', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $chartData = ['labels' => [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [
                [
                    'data' => array_fill(0, 12, 0),
                ]
            ]
        ];

        foreach ($billsData as $bill) {
            $chartData['datasets'][0]['data'][$bill->month - 1] = $bill->totalBillAmount;
        }
        return $chartData;
    }

    /*
    private function getMonthlyBillPaymentByYear($year){
        $billsData = DB::table('billings')
            ->selectRaw('MONTH(created_at) AS month, SUM(bill_amount) AS totalBillAmount')
            ->whereYear('created_at', '=', $year) // Filter for the chosen year
            ->groupBy('month')
            ->orderBy('month')
            ->get();
                $chartData = [
                    'labels' => [
                        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                    ],
                    'datasets' => [
                        [
                            'label' => 'Bill payment',
                            'data' => array_fill(0, 12, 0),
                            'backgroundColor' => [
                                '#FF5733', '#33FF57', '#3357FF', '#FF33A6', '#33FFF0', '#F0FF33',
                                '#A633FF', '#FF8C33', '#33A6FF', '#8CFF33', '#FF3333', '#33FF8C'
                            ]
                        ]
                    ]
                ];

                foreach ($billsData as $bill) {
                    $chartData['datasets'][0]['data'][$bill->month - 1] = $bill->totalBillAmount;
                }
                return $chartData;
    }*/



    private function getCurrentYearBillByMonthAndLGA(){
        $billsData = DB::table('billings')
            ->selectRaw('MONTH(created_at) AS month, lga_id, SUM(bill_amount) AS totalBillAmount')
            ->whereYear('created_at', '=', date('Y')) // Filter for the current year
            ->groupBy('month', 'lga_id')
            ->orderBy('month')
            ->orderBy('lga_id')
            ->get();

        // Initialize Chart.js-compatible structure
        $chartData = [
            'labels' => [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ],
            'datasets' => []
        ];

        // Group bills by `lga_id` and prepare datasets
        $groupedBills = $billsData->groupBy('lga_id');
        foreach ($groupedBills as $lgaId => $data) {
            $dataset = [
                'label' => 'LGA ' . $lgaId,
                'data' => array_fill(0, 12, 0), // Initialize all months with 0
                'backgroundColor' => '#' . substr(md5($lgaId), 0, 6), // Generate unique color
            ];

            foreach ($data as $bill) {
                $dataset['data'][$bill->month - 1] = $bill->totalBillAmount;
            }

            $chartData['datasets'][] = $dataset;
        }

        return $chartData;
    }


    private function getBillsByZones(){
        $billsData = DB::table('billings')
            ->join('zones', 'billings.zone_id', '=', 'zones.id')
            ->selectRaw('MONTH(billings.created_at) AS month, zones.name AS zoneName, SUM(billings.bill_amount) AS totalBillAmount')
            ->whereYear('billings.created_at', '=', date('Y')) // Filter for the current year
            ->groupBy('month', 'zoneName')
            ->orderBy('month')
            ->orderBy('zoneName')
            ->get();

        // Initialize Chart.js-compatible structure
        $chartData = [
            'labels' => [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ],
            'datasets' => []
        ];

        // Group bills by `zoneName` and prepare datasets
        $groupedBills = $billsData->groupBy('zoneName');
        foreach ($groupedBills as $zoneName => $data) {
            $dataset = [
                'label' => 'Zone ' . $zoneName,
                'data' => array_fill(0, 12, 0), // Initialize all months with 0
                'backgroundColor' => '#' . substr(md5($zoneName), 0, 6), // Generate unique color
            ];

            foreach ($data as $bill) {
                $dataset['data'][$bill->month - 1] = $bill->totalBillAmount;
            }

            $chartData['datasets'][] = $dataset;
        }
    }


    private function getPropertyDistributionByZones($year){

        return DB::table('property_lists')
            ->join('zones', 'property_lists.sub_zone', '=', 'zones.sub_zone')
            ->selectRaw('zones.sub_zone AS zoneName, COUNT(property_lists.id) AS totalProperties')
            ->whereYear('property_lists.created_at', '=', $year )
            ->groupBy('zoneName')
            //->orderBy('month')
            ->orderBy('zoneName')
            ->get();

    }

    private function getPropertyDistributionByLGA($year){

        return DB::table('property_lists')
            ->join('lgas', 'property_lists.lga_id', '=', 'lgas.id')
            ->selectRaw('lgas.lga_name AS lgaName, COUNT(property_lists.id) AS totalProperties')
            ->whereYear('property_lists.created_at', '=', $year )
            ->groupBy('lgaName')
            //->orderBy('month')
            ->orderBy('lgaName')
            ->get();

    }
    /*

    private function getPropertyDistributionByZones($year){

        $propertyData = DB::table('property_lists')
            ->join('zones', 'property_lists.sub_zone', '=', 'zones.sub_zone')
            ->selectRaw('MONTH(property_lists.created_at) AS month, zones.sub_zone AS zoneName, COUNT(property_lists.id) AS totalProperties')
            ->whereYear('property_lists.created_at', '=', $year ) // Filter for the current year
            ->groupBy('month', 'zoneName')
            ->orderBy('month')
            ->orderBy('zoneName')
            ->get();

        // Initialize Chart.js-compatible structure
        $chartData = [
            'labels' => [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ],
            'datasets' => []
        ];

        // Group property listings by `zoneName` and prepare datasets
        $groupedProperties = $propertyData->groupBy('zoneName');
        foreach ($groupedProperties as $zoneName => $data) {
            $dataset = [
                'label' => $zoneName,
                'data' => array_fill(0, 12, 0), // Initialize all months with 0
                'backgroundColor' => '#' . substr(md5($zoneName), 0, 6), // Generate unique color
            ];

            foreach ($data as $property) {
                $dataset['data'][$property->month - 1] = $property->totalProperties;
            }

            $chartData['datasets'][] = $dataset;
        }
        return $chartData;

    }*/


   /* private function getPropertyDistributionByLGA(){

        $propertyData = DB::table('property_lists')
            ->join('lgas', 'property_lists.lga_id', '=', 'lgas.id')
            ->selectRaw('MONTH(property_lists.created_at) AS month, lgas.lga_name AS lgaName, COUNT(property_lists.id) AS totalProperties')
            ->whereYear('property_lists.created_at', '=', date('Y')) // Filter for the current year
            ->groupBy('month', 'lgaName')
            ->orderBy('month')
            ->orderBy('lgaName')
            ->get();

        // Initialize Chart.js-compatible structure
        $chartData = [
            'labels' => [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ],
            'datasets' => []
        ];

        // Group property listings by `zoneName` and prepare datasets
        $groupedProperties = $propertyData->groupBy('lgaName');
        foreach ($groupedProperties as $lgaName => $data) {
            $dataset = [
                'label' =>  $lgaName,
                'data' => array_fill(0, 12, 0), // Initialize all months with 0
                'backgroundColor' => '#' . substr(md5($lgaName), 0, 6), // Generate unique color
            ];

            foreach ($data as $property) {
                $dataset['data'][$property->month - 1] = $property->totalProperties;
            }

            $chartData['datasets'][] = $dataset;
        }
        return $chartData;

    }*/


    public function chartTest(Request $request){
        return response()->json([
            'billAmount'=>Billing::getCurrentYearMonthlyBillAmount($request->year),
            'amountPaid'=>Billing::getCurrentYearMonthlyAmountPaid($request->year),
            'byZones'=>Billing::getCurrentYearBillsByZone($request->year),
            'byLGA'=>Billing::getCurrentYearBillsByLGA($request->year),
            'paymentByLGA'=>Billing::getCurrentYearPaymentByLGA($request->year),
        ]);
    }

    public function actionBill(Request $request){

        $validator = Validator::make($request->all(),
            [
                "requestId"=>"required",
                "actionedBy"=>"required",
                "action"=>"required",
            ],
            [
                "requestId.required"=>"Whoops! Something is missing",
                "actionedBy.required"=>"Who action this objection?",
                "action.required"=>"Missing status update",
            ]
        );
        if($validator->fails() ){
            return response()->json([
                "errors"=>$validator->messages()
            ],422);
        }
        $record = Billing::find( $request->requestId);
        if (!$record) {
            return response()->json([
                'message' => 'Whoops! No record found.'
            ], 404);
        }
        if($request->action == 1 || $request->action == 2){
            $record->status = $request->action;
            $record->actioned_by = $request->actionedBy;
            $record->date_actioned = now();
            $record->save();
        }
        if($request->action == 3){ //authorization
            $record->status = $request->action;
            $record->authorized_by = $request->actionedBy;
            $record->date_authorized = now();
            $record->save();
        }
        if($request->action == 4){ //approved
            $record->status = $request->action;
            $record->approved_by = $request->actionedBy;
            $record->date_approved = now();
            $record->save();

        }
        return response()->json(['message' => 'Success! Action successful.'], 201);
    }


}
