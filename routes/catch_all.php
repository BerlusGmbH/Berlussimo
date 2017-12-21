<?php

Route::get('{path}', 'Legacy\IndexController@request')->where('path', '.*');

//Unreachable but still needed to generate routes for the legacy code
Route::group(['namespace' => 'Legacy', 'as' => 'web::'], function () {
    Route::group(['prefix' => 'persons', 'as' => 'personen'], function () {
        Route::get('/', 'IndexController@request')->name('.index');
        Route::get('/{id}', 'IndexController@request')->name('.show');
    });
    Route::group(['prefix' => 'objects', 'as' => 'objekte'], function () {
        Route::get('/', 'IndexController@request')->name('.index');
        Route::get('/{id}', 'IndexController@request')->name('.show');
    });
    Route::group(['prefix' => 'houses', 'as' => 'haeuser'], function () {
        Route::get('/', 'IndexController@request')->name('.index');
        Route::get('/{id}', 'IndexController@request')->name('.show');
    });
    Route::group(['prefix' => 'units', 'as' => 'einheiten'], function () {
        Route::get('/', 'IndexController@request')->name('.index');
        Route::get('/{id}', 'IndexController@request')->name('.show');
    });
    Route::group(['prefix' => 'invoices', 'as' => 'rechnungen'], function () {
        Route::get('/', 'IndexController@request')->name('.index');
        Route::get('/{id}', 'IndexController@request')->name('.show');
    });
});