<?php

namespace App\Http\Controllers;

use App\Http\Resources\SyncResource;
use App\Jobs\SyncDataJob;
use App\Models\ChargeRate;
use App\Models\Depreciation;
use App\Models\Lga;
use App\Models\PropertyAssessmentValue;
use App\Models\PropertyClassification;
use App\Models\PropertyException;
use App\Models\PropertyList;
use App\Models\SynchronizationLog;
use App\Models\Zone;
use App\Traits\UtilityTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemoteController extends Controller
{
    use UtilityTrait;

    public function __construct(){

    }


    private function _fetchBuildingsByLGAName($lgaName)
    {
        //$url = env('REMOTE_LIVE_SERVER')."lga/{$lgaName}";
        $url = env('REMOTE_LOCAL_SERVER')."lga/{$lgaName}";

        $response = Http::withHeaders([
            //'Authorization' => 'Bearer your-access-token',
            'Accept' => 'application/json',
        ])->get($url);
        // Get the response body as JSON
        //$data = $response->json();

        $data = json_decode($response->getBody(), true);
        if ($response->successful()) {
            // Process the data
            return $data; //response()->json(['data' => $data], 200);
        } else {
            return response()->json(['data' => $response->body()], $response->status());
        }
    }
    private function _fetchAllBuildings()
    {
        $url = "http://127.0.0.1:8002/api/lga-list";
        //$url = "http://laravel.kofooni.ca/api/lga-list";

        $response = Http::withHeaders([
            //'Authorization' => 'Bearer your-access-token',
            'Accept' => 'application/json',
        ])->get($url);
        $data = json_decode($response->getBody(), true);
        if ($response->successful()) {
            return $data;
        } else {
            return response()->json(['data' => $response->body()], $response->status());
        }
    }


    public function showSyncReport(Request $request){
        return response()->json([
            'data'=>SyncResource::collection(SynchronizationLog::getSyncReport($request->limit, $request->skip)),
            'total'=>SynchronizationLog::count()
            ]);
    }



    private function _getZoneCharacter($char):string{
        switch (substr($char,0,1)){
            case 'A':
                return 'A';
            case 'B':
                return 'B';
            case 'C':
                return 'C';
            case 'D':
                return 'D';
            case 'E':
                return 'E';
            case 'F':
                return 'F';
            case 'G':
                return 'G';
            case 'H':
                return 'H';
            default:
                return 'Z';

        }
    }

    private function _getPavCode($classId, $zone, $syncWord){
        return PropertyAssessmentValue::where("class_id",$classId)
            ->where("zones",'LIKE', '%'.$zone.'%')
            ->where('sync_word', $syncWord)
            ->first();
    }
/*
    private function _getPavCode($classId, $occupancy, $zone){
        $pavRecord = null;
        switch (strtolower($occupancy)){
            case 'tenant':
                return PropertyAssessmentValue::where("class_id", $classId)
                    ->where("property_use", '3rd Party Only')
                    //->orWhere("occupancy", 'Commercial')
                    ->orWhere("property_use", 'Used for Business') //Religious
                    ->where("zones",'LIKE', '%'.$zone.'%')
                    ->first();
            case 'owner':
                return PropertyAssessmentValue::where("class_id", $classId)
                    ->where("property_use", 'Owner Occupied') //owner
                    ->orWhere("property_use", 'Kogi State Govt') //Government
                    ->orWhere("property_use", 'Used for Business') //Recreational
                    ->orWhere("property_use", 'Vacant/Open Empty Land') //Open Land
                    ->orWhere("property_use", 'Used for Business') //Health || Educational
                    ->where("zones",'LIKE', '%'.$zone.'%')
                    ->first();


        }
        return $pavRecord;
    }*/

    public function _getClass($className){
        $normalizedClassName = trim(strtolower($className));
        if ($normalizedClassName == 'government') {
            return PropertyClassification::where("class_name",  'Kogi State Govt.')->first();

        }elseif ($normalizedClassName == 'residential') {
            return PropertyClassification::where("class_name",  'Residential')->first();

        } elseif (in_array($normalizedClassName, ['recreational', 'religious', 'commercial'])) {
            return PropertyClassification::where("class_name",  'Commercial')->first();

        } elseif ($normalizedClassName == 'educational') {
            return PropertyClassification::where("class_name",  'Education (Private)')->first();

        } elseif ($normalizedClassName == 'health') {
            return PropertyClassification::where("class_name",  'Hospital')->first();

        } elseif ($normalizedClassName == 'open land') {
            return PropertyClassification::where("class_name",  'Vacant Properties & Open Land')->first();

        }  else {
            return null;
        }
    }

    public function _getChargeRate($occupier){
        $normalizedClassName = trim($occupier);
        if ($normalizedClassName == 'Owner_3rd_Party') {
            return ChargeRate::whereRaw("occupancy LIKE ?", ['%Residential Property (Owner and 3rd Party)%'])->first();

        }elseif ($normalizedClassName == 'Third_party') {
           return  ChargeRate::whereRaw("occupancy LIKE ?", ['%Residential Property (without Owner in residence)%'])->first();

        } elseif ($normalizedClassName == 'Owner_occupier') {
            return  ChargeRate::whereRaw("occupancy LIKE ?", ['%Owner-occupied Residential Property%'])->first();

        } elseif ($normalizedClassName == 'Not_Known') {
            return ChargeRate::whereRaw("occupancy LIKE ?", ['%Owner-occupied Residential Property%'])->first();

        }
    }




    public function showBuildingsByLGAId(Request $request){
        $lgaId = $request->lgaId;
        $userId = $request->user;
        if (!isset($lgaId) || !isset($userId)) {
            return response()->json(['error' => "Something went wrong"], 422);
        }
        SyncDataJob::dispatch($lgaId, $userId)->onQueue('data_sync_queue');
        return response()->json(["data"=>"Data synchronization is happening in the background. We'll notify you when it is done."],200);
    }




}
