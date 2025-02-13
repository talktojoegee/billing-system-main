<?php

use Illuminate\Support\Facades\Route;
Route::get('/migration', function(){
    \Illuminate\Support\Facades\Artisan::call('migrate');
    return dd('success');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/clear-cache', function() { \Illuminate\Support\Facades\Artisan::call('cache:clear'); });



//Areas of concern
/*
 * 1. Synchronization = attach billing code to
 *         1. Attach billing code
 *         2. Charge rate(occopiers) = synchronize to get charge rate. The occupancy on the existing table...
 *         3. Depreciation - get it from property list value
 * 2. Connect to the new database
 * 3. Rollback of bills(delete the bills) Can only been done when the bill has not been approved
 * 4. You can process bill multiple times same year for properties that were not processed before
 *
 */
