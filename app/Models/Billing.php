<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Billing extends Model
{
    //
  public $currentYear;



  public function getBilledBy(){
      return $this->belongsTo(User::class, 'billed_by');
  }


  public function getReturnedBy(){
      return $this->belongsTo(User::class, 'returned_by');
  }


  public function getApprovedBy(){
      return $this->belongsTo(User::class, 'approved_by');
  }

  public function getAuthorizedBy(){
      return $this->belongsTo(User::class, 'authorized_by');
  }

  public function getVerifiedBy(){
      return $this->belongsTo(User::class, 'actioned_by');
  }

  public function getReviewedBy(){
      return $this->belongsTo(User::class, 'reviewed_by');
  }

  public function __construct(array $attributes = [])
  {
      $this->currentYear = Carbon::now()->year;
  }


    public function getPropertyClassification(){
        return $this->belongsTo(PropertyClassification::class, "class_id");
    }

    public function getChargeRate(){
        return $this->belongsTo(ChargeRate::class, "occupancy");
    }

    public function getObjection(){
        return $this->belongsTo(Objection::class, "bill_id");
    }


    public function getLGA(){
        return $this->belongsTo(Lga::class, 'lga_id');
    }


    public function getPropertyList(){
        return $this->belongsTo(PropertyList::class, 'property_id');
    }

    public static function findBillSummaryByYearAndLgaId($year, $lgaId){
        return DB::table('billings')
            ->select('lga_id AS lgaId',
                DB::raw('SUM(bill_amount) AS totalBillAmount'),
                DB::raw('COUNT(*) AS totalBills'))
            //->where('year', $year)
            //->where('lga_id', $lgaId)
            ->groupBy('lga_id')
            ->get();
    }
    public static function getBills($limit ,
                                    $skip ,
                                    $paid ,
                                    $objection ,
                                    $status ,
                                    $propertyUse,
    $special = [0]
    )
    {
        return Billing::where('paid', $paid)
            ->where('objection', $objection)
            ->where('status', $status)
            ->whereIn('special', $special)
            ->whereIn('property_use', $propertyUse)
            //->orWhere('returned', $returned)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public static function getLGAChairBills($limit ,
                                    $skip ,
                                    $paid ,
                                    $status ,
                                    $lgaId
    )
    {
        return Billing::where('paid', $paid)
            //->where('objection', $objection)
            ->where('status', $status)
            //->whereIn('special', $special)
            ->where('lga_id', $lgaId)
            //->orWhere('returned', $returned)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }


    public static function getAllPaidBills($limit ,
                                    $skip ,
                                    $paid ,
                                    $objection ,
                                    $status ,
    $propertyUse
    )
    {
        return Billing::where('paid', $paid)
            ->where('objection', $objection)
            ->where('status', $status)
            ->whereIn('property_use', $propertyUse)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public static function getAllPartlyPaidBills($limit , $skip , $objection , $propertyUse)
    {
        return Billing::where('paid_amount', '>', 0)
            ->where('objection', $objection)
            ->whereIn('property_use', $propertyUse)
            ->where('paid', 0)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }
    public static function getAllPaidSpecialInterestBills($limit ,
                                    $skip ,
                                    $paid ,
                                    $objection ,
                                    $status ,
    $propertyUse
    )
    {
        return Billing::where('paid', $paid)
            ->where('objection', $objection)
            ->where('status', $status)
            ->where('special', 1)
            ->whereIn('property_use', $propertyUse)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public static function getBillsByStatus($limit ,
                                            $skip ,
                                            $status ,
    $propertyUse
    )
    {
        return Billing::where('status', $status)
            ->whereIn('property_use', $propertyUse)
            ->where('special', 0)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public static function getObjectedBillsByStatus($limit ,
                                            $skip ,
                                            $status ,
    $propertyUse,
    $objectionIds
    )
    {
        return Billing::where('status', $status)
            ->whereIn('property_use', $propertyUse)
            ->whereIn('id', $objectionIds)
            //->where('objection', 1)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }


    public static function getAllPendingBillsByStatus($limit ,
                                            $skip ,
    $propertyUse
    )
    {
        return Billing::whereIn('status', [0,1,2,3,5])
            ->whereIn('property_use', $propertyUse)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }


    public static function getSpecialInterestBillsByStatus($limit ,
                                            $skip ,
                                            $status ,
    $propertyUse
    )
    {
        return Billing::where('status', $status)
            ->where('special', 1)
            ->whereIn('property_use', $propertyUse)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }


    public static function getAllReturnedBills($limit ,
                                            $skip ,
    $propertyUse
    )
    {
        return Billing::where('returned', 1)
            ->where('special', 0)
            ->whereIn('property_use', $propertyUse)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public static function getAllSpecialInterestReturnedBills($limit ,
                                            $skip ,
    $propertyUse
    )
    {
        return Billing::where('returned', 1)
            ->where('special', 1)
            ->whereIn('property_use', $propertyUse)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public static function getAllBillsByParams($paid , $objection , $status, $propertyUse)
    {
        return Billing::where('paid', $paid)
            ->where('objection', $objection)
            ->where('status', $status)
            ->whereIn('property_use', $propertyUse)
            ->get();

    }
    public static function getAllBillsPaymentByParams( $objection , $status, $propertyUse)
    {
        return Billing::where('objection', $objection)
            ->where('paid', 1)
            ->where('status', $status)
            ->whereIn('property_use', $propertyUse)
            ->get();

    }
    public static function getGrossAllBillsByParams( $objection, $propertyUse)
    {
        return Billing::where('objection', $objection)
            ->whereIn('property_use', $propertyUse)
            ->where('status', 4)
            ->get();

    }
    public static function getAllSpecialInterestBillsByParams($paid , $objection , $status, $propertyUse)
    {
        return Billing::where('paid', $paid)
            ->where('objection', $objection)
            ->where('special', 1)
            ->where('status', $status)
            ->whereIn('property_use', $propertyUse)
            ->get();

    }

    public static function getBillsByParams($paid , $objection , $status, $propertyUse, $special = [0])
    {
        return Billing::where('paid', $paid)
            ->where('objection', $objection)
            ->where('status', $status)
            ->whereIn('special', $special)
            ->whereIn('property_use', $propertyUse)
            //->orWhere('returned', $returned)
            ->get();

    }

    public static function getLGAChairBillsByParams($paid , $status, $lgaId)
    {
        return Billing::where('paid', $paid)
            ->where('status', $status)
            ->where('lga_id', $lgaId)
            ->get();

    }

    public static function getBillsByParamsByStatus($status , $propertyUse)
    {
        return Billing::where('status', $status)
            ->whereIn('property_use', $propertyUse)
            ->get();

    }
    public static function getObjectedBillsByParamsByStatus($status , $propertyUse)
    {
        return Billing::where('status', $status)->where('objection', 1)
            ->whereIn('property_use', $propertyUse)
            ->get();

    }

    public static function getAllPendingBillsByParamsByStatus($propertyUse)
    {
        return Billing::whereIn('status', [0,1,2,3,5])
            ->whereIn('property_use', $propertyUse)
            ->get();

    }

    public static function getSpecialInterestBillsByParamsByStatus($status, $propertyUse)
    {
        return Billing::where('status', $status)
            ->where('special', 1)
            ->where('property_use', $propertyUse)
            ->get();

    }

     public static function getAllReturnedBillsByParams($propertyUse)
        {
            return Billing::where('returned', 1)
                ->where('special', 0)
                ->whereIn('property_use', $propertyUse)
                ->get();

        }

     public static function getAllSpecialInterestReturnedBillsByParams($propertyUse)
        {
            return Billing::where('returned', 1)
                ->where('special', 1)
                ->whereIn('property_use', $propertyUse)
                ->get();

        }


    /*public static function getOutstandingBills(){
        return Billing::where('paid', 0)
            ->where('objection', 0)
            ->orderBy('id', 'DESC')
            ->get();
    }*/


    public static function getPaidBills(){
        return Billing::where('paid', 1)->where('objection', 0)->orderBy('id', 'DESC')->get();
    }

    public static function getCurrentYearMonthlyBillAmount($year){

        $monthlyBills = Billing::select(
            DB::raw('MONTH(entry_date) as month'),
            DB::raw('SUM(bill_amount) as total_bill_amount')
        )->where('status', 4)
            ->whereYear('entry_date', $year)
            ->groupBy(DB::raw('MONTH(entry_date)'))
            ->orderBy(DB::raw('MONTH(entry_date)'))
            ->get();
        $billAmountsByMonth = array_fill(0, 12, 0);
        foreach ($monthlyBills as $bill) {
            $monthIndex = $bill->month - 1;
            $billAmountsByMonth[$monthIndex] = $bill->total_bill_amount;
        }
        return  $billAmountsByMonth;

    }
    public static function getLGAChairCurrentYearMonthlyBillAmount($year, $lgaId){

        $monthlyBills = Billing::select(
            DB::raw('MONTH(entry_date) as month'),
            DB::raw('SUM(bill_amount) as total_bill_amount')
        )
            ->whereYear('entry_date', $year)
            ->where('lga_id', $lgaId)
            ->where('status', 4)//approved
            ->groupBy(DB::raw('MONTH(entry_date)'))
            ->orderBy(DB::raw('MONTH(entry_date)'))
            ->get();
        $billAmountsByMonth = array_fill(0, 12, 0);
        foreach ($monthlyBills as $bill) {
            $monthIndex = $bill->month - 1;
            $billAmountsByMonth[$monthIndex] = $bill->total_bill_amount;
        }
        return  $billAmountsByMonth;

    }

    public static function getCurrentYearMonthlyAmountPaid($year){
        $monthlyBills = Billing::select(
            DB::raw('MONTH(entry_date) as month'),
            DB::raw('SUM(paid_amount) as total_bill_amount')
        )->where('status', 4)
            ->whereYear('entry_date', $year)
            ->groupBy(DB::raw('MONTH(entry_date)'))
            ->orderBy(DB::raw('MONTH(entry_date)'))
            ->get();
        $billAmountsByMonth = array_fill(0, 12, 0);
        foreach ($monthlyBills as $bill) {
            $monthIndex = $bill->month - 1;
            $billAmountsByMonth[$monthIndex] = $bill->total_bill_amount;
        }
        return  $billAmountsByMonth;

    }
    public static function getLGAChairCurrentYearMonthlyAmountPaid($year, $lgaId){
        $monthlyBills = Billing::select(
            DB::raw('MONTH(entry_date) as month'),
            DB::raw('SUM(paid_amount) as total_bill_amount')
        )
            ->whereYear('entry_date', $year)
            ->where('lga_id', $lgaId)
            ->where('status', 4)//approved
            ->groupBy(DB::raw('MONTH(entry_date)'))
            ->orderBy(DB::raw('MONTH(entry_date)'))
            ->get();
        $billAmountsByMonth = array_fill(0, 12, 0);
        foreach ($monthlyBills as $bill) {
            $monthIndex = $bill->month - 1;
            $billAmountsByMonth[$monthIndex] = $bill->total_bill_amount;
        }
        return  $billAmountsByMonth;

    }
    public static function getCurrentYearBillsByZone($year){
        return DB::table('billings')
            ->join('zones', 'billings.zone_name', '=', 'zones.sub_zone')
            ->select('zones.sub_zone', DB::raw('SUM(billings.bill_amount) as total_bills'))
            ->whereYear('billings.entry_date', $year)
            ->where('billings.status', 4)
            ->groupBy('zones.sub_zone')
            ->orderBy('zones.sub_zone', 'ASC')
            ->get();

    }
    public static function getLGAChairCurrentYearBillsByZone($year, $lgaId){
        return DB::table('billings')
            ->join('zones', 'billings.zone_name', '=', 'zones.sub_zone')
            ->select('zones.sub_zone', DB::raw('SUM(billings.bill_amount) as total_bills'))
            ->whereYear('billings.entry_date', $year)
            ->where('billings.lga_id', $lgaId)
            ->where('billings.status', 4)//approved
            ->groupBy('zones.sub_zone')
            ->orderBy('zones.sub_zone', 'ASC')
            ->get();

    }

    public static function getCurrentYearBillsByLGA($year){

        return DB::table('billings')
            ->join('lgas', 'billings.lga_id', '=', 'lgas.id')
            ->select('lgas.lga_name', DB::raw('SUM(billings.bill_amount) as total_bills'))
            ->whereYear('billings.entry_date', $year)
            ->where('billings.status', 4)
            ->groupBy('lgas.lga_name')
            ->orderBy('lgas.lga_name', 'ASC')
            ->get();


    }

    public static function getLGAChairCurrentYearBillsByLGA($year, $lgaId){

        return DB::table('billings')
            ->join('lgas', 'billings.lga_id', '=', 'lgas.id')
            ->select('lgas.lga_name', DB::raw('SUM(billings.bill_amount) as total_bills'))
            ->whereYear('billings.entry_date', $year)
            ->where('billings.lga_id', $lgaId)
            ->where('billings.status', 4)//approved
            ->groupBy('lgas.lga_name')
            ->orderBy('lgas.lga_name', 'ASC')
            ->get();


    }
    public static function getCurrentYearPaymentByLGA($year){

        return DB::table('billings')
            ->join('lgas', 'billings.lga_id', '=', 'lgas.id')
            ->select('lgas.lga_name', DB::raw('SUM(billings.paid_amount) as amount'))
            ->whereYear('billings.entry_date', $year)
            ->where('billings.status', 4)
            ->groupBy('lgas.lga_name')
            ->orderBy('lgas.lga_name', 'ASC')
            ->get();


    }

    public static function getLGAChairCurrentYearPaymentByLGA($year, $lgaId){

        return DB::table('billings')
            ->join('lgas', 'billings.lga_id', '=', 'lgas.id')
            ->select('lgas.lga_name', DB::raw('SUM(billings.paid_amount) as amount'))
            ->whereYear('billings.entry_date', $year)
            ->where('billings.lga_id', $lgaId)
            ->where('billings.status', 4)//approved
            ->groupBy('lgas.lga_name')
            ->orderBy('lgas.lga_name', 'ASC')
            ->get();


    }

    public static function getBillByYearLgaId($year, $lgaId){
        return Billing::where('lga_id', $lgaId)->where('year', $year)->first();
    }

    public static function getBillByYearBuildingCode($year, $code){
        return Billing::where('building_code', $code)->where('year', $year)->first();
    }

    public function balanceBroughtForward($year,$buildingCode){
        return Billing::whereYear('year', '<', $year)->where('building_code', $buildingCode)
            ->where('paid', 0)
            ->sum('bill_amount') - Billing::whereYear('year', '<', $year)->where('building_code', $buildingCode)
                ->where('paid', 0)
                ->sum('paid_amount');

    }

    public static function searchBills($keyword, $propertyUse, $status, $special){
        return DB::table('billings')
            ->leftJoin('property_lists', 'billings.property_id', '=', 'property_lists.id')
            ->select(
                'billings.id as billId',
                'billings.building_code as buildingCode',
                'billings.status',
                'billings.special',
                'billings.property_use',
                'billings.assessment_no',
                'property_lists.property_name',
                'property_lists.building_code',
                'billings.assessment_no',
                'billings.pav_code',
                'billings.year',
                'billings.zone_name',
                'billings.class_id',
                'billings.property_id',
                'billings.lga_id',
                'billings.bill_amount',
                'billings.occupancy',
                'billings.paid_amount',
                'billings.assessed_value',
                'billings.special',
                'billings.status',
                'billings.property_use',
                'billings.url',
                'billings.property_id',
                'billings.cr',
            )
            ->where('billings.status', $status)
            ->where('billings.special', $special)
            ->whereIn('billings.property_use', $propertyUse)
            ->where(function ($query) use ($keyword) {
                $query->where('billings.assessment_no', 'LIKE', "%{$keyword}%")
                    ->orWhere('billings.building_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('property_lists.property_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('property_lists.building_code', 'LIKE', "%{$keyword}%");
            })
            ->distinct()
            ->get();
    }

    public static function searchReturnedBills($keyword, $propertyUse, $special){
        return DB::table('billings')
            ->leftJoin('property_lists', 'billings.property_id', '=', 'property_lists.id')
            ->select(
                'billings.id as billId',
                'billings.building_code as buildingCode',
                'billings.status',
                'billings.special',
                'billings.property_use',
                'billings.assessment_no',
                'property_lists.property_name',
                'property_lists.building_code',
                'billings.assessment_no',
                'billings.pav_code',
                'billings.year',
                'billings.zone_name',
                'billings.class_id',
                'billings.property_id',
                'billings.lga_id',
                'billings.bill_amount',
                'billings.occupancy',
                'billings.paid_amount',
                'billings.assessed_value',
                'billings.special',
                'billings.status',
                'billings.property_use',
                'billings.url',
                'billings.property_id',
                'billings.cr',
            )
            ->where('billings.returned', 1)
            ->where('billings.special', $special)
            ->whereIn('billings.property_use', $propertyUse)
            ->where(function ($query) use ($keyword) {
                $query->where('billings.assessment_no', 'LIKE', "%{$keyword}%")
                    ->orWhere('billings.building_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('property_lists.property_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('property_lists.building_code', 'LIKE', "%{$keyword}%");
            })
            ->distinct()
            ->get();
    }
    public static function searchAllPendingBills($keyword, $propertyUse, $special){
        return DB::table('billings')
            ->leftJoin('property_lists', 'billings.property_id', '=', 'property_lists.id')
            ->select(
                'billings.id as billId',
                'billings.building_code as buildingCode',
                'billings.status',
                'billings.special',
                'billings.property_use',
                'billings.assessment_no',
                'property_lists.property_name',
                'property_lists.building_code',
                'billings.assessment_no',
                'billings.pav_code',
                'billings.year',
                'billings.zone_name',
                'billings.class_id',
                'billings.property_id',
                'billings.lga_id',
                'billings.bill_amount',
                'billings.occupancy',
                'billings.paid_amount',
                'billings.assessed_value',
                'billings.special',
                'billings.status',
                'billings.property_use',
                'billings.url',
                'billings.property_id',
                'billings.cr',
            )
            ->where('billings.status','!=', 4) //approved
            ->where('billings.special', $special)
            ->whereIn('billings.property_use', $propertyUse)
            ->where(function ($query) use ($keyword) {
                $query->where('billings.assessment_no', 'LIKE', "%{$keyword}%")
                    ->orWhere('billings.building_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('property_lists.property_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('property_lists.building_code', 'LIKE', "%{$keyword}%");
            })
            ->distinct()
            ->get();
    }
    public static function searchOutstandingBills($keyword, $propertyUse, $status, $special,$objection, $paid){
        return DB::table('billings')
            ->leftJoin('property_lists', 'billings.property_id', '=', 'property_lists.id')
            ->select('billings.id as billId', 'billings.building_code as buildingCode', 'billings.*', 'property_lists.property_name', 'property_lists.building_code')
            ->where(function ($query) use ($keyword, $propertyUse, $status, $special, $objection, $paid) {
                $query->where('billings.assessment_no', 'LIKE', "%{$keyword}%")
                    ->where('billings.status', $status)
                    ->where('billings.special', $special)
                    ->where('billings.objection', $objection)
                    ->where('billings.paid', $paid)
                    ->whereIn('billings.property_use', $propertyUse)
                    ->orWhere('billings.building_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('property_lists.property_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('property_lists.building_code', 'LIKE', "%{$keyword}%");
            })
            ->distinct()
            ->get();
    }


    public static function getCustomerStatementByKgtinDate($buildingCode, $from, $to){
        return Billing::where('building_code', $buildingCode)->whereBetween('entry_date',[$from, $to])->get();
    }

  /*  public static function generateWorkflowReport($from, $to){
        return Billing::whereBetween('entry_date',[$from, $to] )
            ->orderBy('id', 'ASC')
            ->get();
    }*/

    public static function generateWorkflowReport($year)
    {
        $billings = Billing::where('year', $year)->get();
        $users = User::where('role', 5)->get();
        return $billings->groupBy('property_use')->map(function ($bills, $sector) use ($users) {
            $matchedUsers = $users->filter(function ($user) use ($sector) {
                $userSectors = array_map('trim', explode(',', $user->sector));
                return in_array($sector, $userSectors);
            })->values();

            return [
                'sector' => $sector,
                'users' => $matchedUsers,
                'review' => $bills->where('status', 0)->count(),
                'verification' => $bills->where('status', 1)->count(),
                'authorization' => $bills->where('status', 2)->count(),
                'approval' => $bills->where('status', 3)->count(),
            ];
        })->values();
    }
    public static function generatePerformanceReport($from, $to)
    {
        $billings = Billing::whereBetween('entry_date', [$from, $to])->get();
        $users = User::where('role', 5)->get();
        return $billings->groupBy('property_use')->map(function ($bills, $sector) use ($users) {
            $matchedUsers = $users->filter(function ($user) use ($sector) {
                $userSectors = array_map('trim', explode(',', $user->sector));
                return in_array($sector, $userSectors);
            })->values();

            return [
                'sector' => $sector,
                'users' => $matchedUsers,
                'review' => $bills->where('status', 0)->count(),
                'verification' => $bills->where('status', 1)->count(),
                'authorization' => $bills->where('status', 2)->count(),
                'approval' => $bills->where('status', 3)->count(),
            ];
        })->values();
    }

    public static function generatePerformanceReportByUsers($from, $to)
    {
        $billings = Billing::whereBetween('entry_date', [$from, $to])->get();
        $users = User::where('role', 5)->get();

        $report = [];

        foreach ($users as $user) {
            $report[] = [
                'user' => $user,
                'reviewed' => $billings->where('reviewed_by', $user->id)->count(),
                'verified' => $billings->where('actioned_by', $user->id)->count(),
                'authorized' => $billings->where('authorized_by', $user->id)->count(),
                'approved' => $billings->where('approved_by', $user->id)->count(),
            ];
        }

        return collect($report);
    }


    public static function getDistinctWardByLgaId($lgId){
        return DB::table('billings')
            ->where('lga_id', $lgId)
            ->distinct()
            ->pluck('ward');
    }
    public static function getDistinctZonesByWard($ward){
        return DB::table('billings')
            ->where('ward', $ward)
            ->distinct()
            ->pluck('zone_name');
    }

    public static function getFilteredBillsByStatus($limit ,
                                            $skip ,
                                            $status ,
                                            $propertyUse,
    $lga,$zone,$ward
    )
    {
        return Billing::where('status', $status)
            ->whereIn('property_use', $propertyUse)
            ->where('zone_name', $zone)
            ->where('lga_id', $lga)
            ->where('ward', $ward)
            ->where('special', 0)
            ->skip($skip)
            ->take($limit)
            ->orderBy('id', 'DESC')
            ->get();
    }
    public static function getFilteredBillsByParamsByStatus($status , $propertyUse, $lga,$zone,$ward)
    {
        return Billing::where('status', $status)
            ->whereIn('property_use', $propertyUse)
            ->where('zone_name', $zone)
            ->where('lga_id', $lga)
            ->where('ward', $ward)
            ->get();

    }


}
