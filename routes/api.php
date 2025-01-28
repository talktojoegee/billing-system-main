<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//use Tymon\JWTAuth\Http\Middleware\Check as JWTMiddleware;


Route::options('{any}', function () {
    return response()->json(['status' => 'OK'], 200);
})->where('any', '.*');

Route::post('/register',[\App\Http\Controllers\AuthenticationController::class, 'register']);
Route::post('/authenticate',[\App\Http\Controllers\AuthenticationController::class, 'authenticate']);
Route::post('/logout', [\App\Http\Controllers\AuthenticationController::class, 'logout']);


/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/

Route::group(['middleware' => 'api'], function(){
    Route::post('/lga/new', [\App\Http\Controllers\LGAController::class, 'createLGA']);
    Route::get('/lga/all', [\App\Http\Controllers\LGAController::class, 'showAllLGAs']);

    Route::post('/zone/new', [\App\Http\Controllers\ZoneController::class, 'createZone']);
    Route::get('/zone/all', [\App\Http\Controllers\ZoneController::class, 'showAllZones']);

    #Relief settings
    Route::post('/relief/new', [\App\Http\Controllers\ReliefController::class, 'storeReliefSettings']);
    Route::get('/relief/all', [\App\Http\Controllers\ReliefController::class, 'showReliefSetup']);
    Route::get('/relief/all/{type}', [\App\Http\Controllers\ReliefController::class, 'showReliefSetupByType']);

    #Property title settings
    Route::post('/property-title/new', [\App\Http\Controllers\PropertyTitleController::class, 'storePropertyTitle']);
    Route::get('/property-title/all', [\App\Http\Controllers\PropertyTitleController::class, 'showAllPropertyTitles']);

    #area-office settings
    Route::post('/area-office/new', [\App\Http\Controllers\AreaOfficeController::class, 'storeAreaOffice']);
    Route::get('/area-office/all', [\App\Http\Controllers\AreaOfficeController::class, 'showAllAreaOffices']);

    #Property classification settings
    Route::post('/property-classification/new', [\App\Http\Controllers\PropertyClassificationController::class, 'storeClass']);
    Route::get('/property-classification/all', [\App\Http\Controllers\PropertyClassificationController::class, 'showAllPropertyClassifications']);

    #PAV
    Route::post('/property-assessment-value/new', [\App\Http\Controllers\PropertyAssessmentValueController::class, 'storePAV']);
    Route::get('/property-assessment-value/all', [\App\Http\Controllers\PropertyAssessmentValueController::class, 'showAllPAVs']);

    #Owners
    Route::post('/owners/new', [\App\Http\Controllers\OwnersController::class, 'storeOwner']);
    Route::get('/owners/all', [\App\Http\Controllers\OwnersController::class, 'showAllOwners']);



    Route::get('/sync-data/{lgaId}', [\App\Http\Controllers\RemoteController::class, 'showBuildingsByLGAId']);
    Route::get('/synchronization-report/{limit}/{skip}', [\App\Http\Controllers\RemoteController::class, 'showSyncReport']);


    Route::post('/billing/retrieve', [\App\Http\Controllers\BillingController::class, 'retrieveBills']);
    Route::post('/billing/process', [\App\Http\Controllers\BillingController::class, 'processBill']);

    Route::get('/billing/chart-summary/{year}', [\App\Http\Controllers\BillingController::class, 'showBillDataOnDashboard']);
    Route::get('/billing/property-distribution/{year}', [\App\Http\Controllers\BillingController::class, 'showPropertyDistributionByZones']);
    Route::get('/property/distribution', [\App\Http\Controllers\BillingController::class, 'showPropertyDistributionByLGA']);
    Route::get('/dashboard/statistics/{year}', [\App\Http\Controllers\BillingController::class, 'showDashboardStatistics']);
    Route::get('/billing/outstanding-bills/{limit}/{skip}', [\App\Http\Controllers\BillingController::class, 'showOutstandingBills']);
    Route::get('/billing/bills/{limit}/{skip}/{status}', [\App\Http\Controllers\BillingController::class, 'showBills']);
    //Route::get('/billing/outstanding-bills', [\App\Http\Controllers\BillingController::class, 'showOutstandingBills']);

    Route::get('/billing/paid/{limit}/{skip}', [\App\Http\Controllers\BillingController::class, 'showPaidBills']);
    Route::get('/billing/detail/{url}', [\App\Http\Controllers\BillingController::class, 'showBillDetails']);
    Route::post('billing/action-bill', [\App\Http\Controllers\BillingController::class, 'actionBill']);



    Route::get('/property-list/all/{limit}/{skip}', [\App\Http\Controllers\PropertyListController::class, 'getPropertyList']);
    Route::get('/property/{id}', [\App\Http\Controllers\PropertyListController::class, 'showPropertyDetail']);
    //Route::get('/property-list/all', [\App\Http\Controllers\PropertyListController::class, 'showPropertyLists']);



    Route::get('/chart-record/{year}', [\App\Http\Controllers\BillingController::class, 'chartTest']);

    Route::get('download/attachment/{slug}', [App\Http\Controllers\ObjectionController::class, 'downloadAttachment'] );

    Route::post('/objection/new', [\App\Http\Controllers\ObjectionController::class, 'handleNewObjection']);
    Route::get('/objection/detail/{requestId}', [\App\Http\Controllers\ObjectionController::class, 'showObjectionDetail']);
    Route::get('/objection/all/{status}/{limit}/{skip}', [\App\Http\Controllers\ObjectionController::class, 'showObjectionListByStatus']);
    Route::post('objection/action-objection', [\App\Http\Controllers\ObjectionController::class, 'actionObjection']);


});
Route::middleware([\App\Http\Middleware\JsonApiMiddleware::class])->group( function(){


});

//
