<?php

use Illuminate\Http\Request;

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

Route::group(['prefix' => 'v1', 'as' => 'api.v1.', 'namespace' => 'Api\v1', 'middleware' => ['auth:api']], function () {
    Route::get('/search', 'SearchBarController@search')->name('search');
    Route::get('/call/{detail}', 'CallController@call')->name('call');

    Route::group(['namespace' => 'Modules'], function () {
        Route::get('/persons/details/categories', 'PersonController@detailsCategories')->name('details.categories');
        Route::get('/persons/details/categories/{category}/subcategories', 'PersonController@detailsSubcategories')->name('details.subcategories');
        Route::get('/persons/{left}/merge/{right}', 'PersonController@merge')->name('merge');
        Route::get('/persons/{person}', 'PersonController@show')->name('show');
        Route::get('/persons/{person}/notifications', 'PersonController@notifications')->name('notifications');
        Route::resource('details', 'DetailController', ['only' => ['update', 'destroy', 'store']]);
        Route::resource('persons', 'PersonController', ['only' => ['update', 'destroy']]);
    });
});