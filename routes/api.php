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

Route::group(['prefix' => 'v1', 'as' => 'api.v1.', 'namespace' => 'Api\v1', 'middleware' => ['auth:api']], function () {
    Route::get('/search', 'SearchBarController@search')->name('search');
    Route::get('/call/{detail}', 'CallController@call')->name('call');

    Route::group(['namespace' => 'Modules'], function () {
        Route::get('/partners/{partner}/available-job-titles', 'PartnerController@availableTitles')->name('partners.available-job-titles');

        Route::get('/notifications/{notification}/toggle', 'NotificationController@toggle')->name('notifications.toggle');

        Route::get('/persons/parameters', 'PersonController@parameters')->name('persons.parameters');
        Route::get('/persons/details/categories', 'PersonController@detailsCategories')->name('details.categories');
        Route::get('/persons/details/categories/{category}/subcategories', 'PersonController@detailsSubcategories')->name('details.subcategories');
        Route::get('/persons/{left}/merge/{right}', 'PersonController@merge')->name('merge');
        Route::get('/persons/{person}/notifications', 'PersonController@notifications')->name('persons.notifications.index');
        Route::get('/persons/{person}/notifications/mark_all_as_read', 'PersonController@notificationsMarkAllAsRead')->name('persons.notifications.mark_all_as_read');
        Route::get('/persons/{person}/roles', 'PersonController@roles')->name('persons.roles');
        Route::resource('persons', 'PersonController', ['only' => ['update', 'store', 'show', 'index']]);

        Route::resource('persons.credential', 'CredentialController', ['only' => ['store', 'index']]);

        Route::resource('jobs', 'JobController', ['only' => ['update', 'store']]);

        Route::resource('details', 'DetailController', ['only' => ['update', 'destroy', 'store']]);

        Route::resource('roles', 'RoleController', ['only' => ['index']]);

        Route::get('/assignments/parameters', 'AssignmentController@parameters')->name('assignments.parameters');
        Route::resource('assignments', 'AssignmentController', ['only' => ['store', 'update', 'index']]);

        Route::get('/units/parameters', 'UnitController@parameters')->name('units.parameters');
        Route::get('/units/possible_unit_kinds', 'UnitController@possibleUnitKinds')->name('possible_unit_kinds');
        Route::get('/units/{unit}/tenants/emails', 'UnitController@tenantsEmails')->name('units.tenants.emails');
        Route::get('/units/{unit}/owners/emails', 'UnitController@ownersEmails')->name('units.owners.emails');
        Route::get('/units/details/categories', 'UnitController@detailsCategories')->name('units.details.categories');
        Route::get('/units/details/categories/{category}/subcategories', 'UnitController@detailsSubcategories')->name('units.details.subcategories');
        Route::resource('units', 'UnitController', ['only' => ['update', 'store', 'show', 'index']]);

        Route::get('/houses/parameters', 'HouseController@parameters')->name('houses.parameters');
        Route::get('/houses/{house}/tenants/emails', 'HouseController@tenantsEmails')->name('houses.tenants.emails');
        Route::get('/houses/{house}/owners/emails', 'HouseController@ownersEmails')->name('houses.owners.emails');
        Route::get('/houses/details/categories', 'HouseController@detailsCategories')->name('houses.details.categories');
        Route::get('/houses/details/categories/{category}/subcategories', 'HouseController@detailsSubcategories')->name('houses.details.subcategories');
        Route::resource('houses', 'HouseController', ['only' => ['update', 'store', 'show', 'index']]);

        Route::get('/objects/parameters', 'ObjectController@parameters')->name('objects.parameters');
        Route::get('/objects/details/categories', 'ObjectController@detailsCategories')->name('objects.details.categories');
        Route::get('/objects/details/categories/{category}/subcategories', 'ObjectController@detailsSubcategories')->name('objects.details.subcategories');
        Route::get('/objects/{object}/copy', 'ObjectController@copy')->name('objects.copy');
        Route::get('/objects/{object}/tenants/emails', 'ObjectController@tenantsEmails')->name('objects.tenants.emails');
        Route::get('/objects/{object}/owners/emails', 'ObjectController@ownersEmails')->name('objects.owners.emails');
        Route::resource('objects', 'ObjectController', ['only' => ['update', 'store', 'show', 'index']]);
    });
});