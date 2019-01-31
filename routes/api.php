<?php

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

Route::group(['prefix' => 'v1', 'as' => 'api.v1.', 'namespace' => 'Api\v1', 'middleware' => ['auth.ip']], function () {
    Route::get('/pbx/cid-lookup', 'PBXController@lookup')->name('lookup');
});

Route::group(['prefix' => 'v1', 'as' => 'api.v1.', 'namespace' => 'Api\v1', 'middleware' => ['auth:api']], function () {
    Route::get('/pbx/call/{detail}', 'PBXController@call')->name('call');
});
