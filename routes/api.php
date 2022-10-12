<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::namespace('API')->group(function() {
    Route::get('/all_autos', 'MainController@allAutos');
    Route::get('/test', 'MainController@test');
    Route::get('/free_autos', 'MainController@freeAutos');
    Route::get('/assigned_autos', 'MainController@assignedAutos');
    Route::post('/assign_user_to_auto', 'MainController@assignUserToAuto');
    Route::get('/unbind_user/{user_id}', 'MainController@unbindUser')->where(['user_id' => '[0-9]+']);
    Route::delete('/delete_auto/{auto_id}', 'MainController@deleteAuto')->where(['auto_id' => '[0-9]+']);
});
