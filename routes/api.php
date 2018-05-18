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
    Route::get('/pbx/cid-lookup/{cid}', 'PBXController@lookup')->name('lookup');
});

Route::group(['prefix' => 'v1', 'as' => 'api.v1.', 'namespace' => 'Api\v1', 'middleware' => ['auth:api']], function () {
    Route::get('/search', 'SearchBarController@search')->name('search');

    Route::get('/pbx/call/{detail}', 'PBXController@call')->name('call');

    Route::get('/menu', 'IndexController@menu')->name('menu');
    Route::get('/menu/invoice', 'IndexController@menuInvoice')->name('menu.invoice');

    Route::group(['namespace' => 'Modules'], function () {
        Route::get('/workplace', 'WorkplaceController@show')->name('workplace.show');

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

        Route::get('/invoices/types', 'InvoiceController@types')->name('invoices.types');
        Route::get('/invoices/units', 'InvoiceController@units')->name('invoices.units');
        Route::resource('invoices', 'InvoiceController', ['only' => ['show', 'update']]);

        Route::match(['post', 'put', 'patch'], '/invoice-lines/update-batch', 'InvoiceLineController@updateBatch')->name('invoices.lines.batch');
        Route::resource('invoice-lines', 'InvoiceLineController', ['only' => ['store', 'update', 'destroy']]);

        Route::match(['post', 'put', 'patch'], '/invoice-line-assignments/update-batch', 'InvoiceLineAssignmentController@updateBatch')->name('invoices.assignments.batch');
        Route::resource('invoice-line-assignments', 'InvoiceLineAssignmentController', ['only' => ['store', 'update', 'destroy']]);

        Route::get('/rentalcontracts/details/categories', 'RentalContractController@detailsCategories')->name('rentalcontracts.details.categories');
        Route::get('/rentalcontracts/details/categories/{category}/subcategories', 'RentalContractController@detailsSubcategories')->name('rentalcontracts.details.subcategories');

        Route::get('/purchasecontracts/details/categories', 'PurchaseContractController@detailsCategories')->name('purchasecontracts.details.categories');
        Route::get('/purchasecontracts/details/categories/{category}/subcategories', 'PurchaseContractController@detailsSubcategories')->name('purchasecontracts.details.subcategories');
    });
});