<?php
Route::group(['namespace' => 'Auth', 'middleware' => ['web']], function () {
    // Authentication Routes...
    Route::get('login', 'AuthController@showLoginForm');
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout');
    // Registration Routes...
    Route::get('register', 'AuthController@showRegistrationForm');
    Route::post('register', 'AuthController@register');
    // Password Reset Routes...
    Route::get('password/reset/{token?}', 'PasswordController@showResetForm');
    Route::post('password/email', 'PasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'PasswordController@reset');
});

Route::group(['prefix' => config('app.legacy.prefix'), 'namespace' => 'Legacy', 'middleware' => ['web', 'auth'], 'as' => 'legacy::'], function () {
    Route::match(['get', 'post'], '/', 'IndexController@request')->name('index');

    Route::match(['get', 'post'], 'ajax/ajax_info.php', 'IndexController@ajax')->name('ajax');

    Route::get('svgraph/line.svg', 'IndexController@line')->name('line');

    Route::get('svgraph/pie.svg', 'IndexController@pie')->name('pie');

    Route::group(['prefix' => 'admin', 'as' => 'admin::'], function () {
        Route::match(['get', 'post'], '/', 'AdminController@request')->name('index');
    });

    Route::group(['prefix' => 'benutzer', 'as' => 'benutzer::'], function () {
        Route::match(['get', 'post'], '/', 'BenutzerController@request')->name('index');
    });

    Route::group(['prefix' => 'bk', 'as' => 'bk::'], function () {
        Route::match(['get', 'post'], '/', 'BkController@request')->name('index');
    });

    Route::group(['prefix' => 'buchen', 'as' => 'buchen::'], function () {
        Route::match(['get', 'post'], '/', 'BuchenController@request')->name('index');
    });

    Route::group(['prefix' => 'dbbackup', 'as' => 'dbbackup::'], function () {
        Route::match(['get', 'post'], '/', 'DbBackupController@request')->name('index');
    });

    Route::group(['prefix' => 'details', 'as' => 'details::'], function () {
        Route::match(['get', 'post'], '/', 'DetailsController@request')->name('index');
    });

    Route::group(['prefix' => 'einheiten', 'as' => 'einheiten::'], function () {
        Route::match(['get', 'post'], '/', 'EinheitenController@request')->name('index');
    });

    Route::group(['prefix' => 'einheitenform', 'as' => 'einheitenform::'], function () {
        Route::match(['get', 'post'], '/', 'EinheitenFormController@request')->name('index');
    });

    Route::group(['prefix' => 'haeuserform', 'as' => 'haeuserform::'], function () {
        Route::match(['get', 'post'], '/', 'HaeuserFormController@request')->name('index');
    });

    Route::group(['prefix' => 'objekteform', 'as' => 'objekteform::'], function () {
        Route::match(['get', 'post'], '/', 'ObjekteFormController@request')->name('index');
    });

    Route::group(['prefix' => 'geldkonten', 'as' => 'geldkonten::'], function () {
        Route::match(['get', 'post'], '/', 'GeldkontenController@request')->name('index');
        Route::get('{id}/select', 'GeldkontenController@select')->name('select');
    });

    Route::group(['prefix' => 'haeuser', 'as' => 'haeuser::'], function () {
        Route::match(['get', 'post'], '/', 'HaeuserController@request')->name('index');
    });

    Route::group(['prefix' => 'kassen', 'as' => 'kassen::'], function () {
        Route::match(['get', 'post'], '/', 'KassenController@request')->name('index');
    });

    Route::group(['prefix' => 'katalog', 'as' => 'katalog::'], function () {
        Route::match(['get', 'post'], '/', 'KatalogController@request')->name('index');
    });

    Route::group(['prefix' => 'kautionen', 'as' => 'kautionen::'], function () {
        Route::match(['get', 'post'], '/', 'KautionenController@request')->name('index');
    });

    Route::group(['prefix' => 'kontenrahmen', 'as' => 'kontenrahmen::'], function () {
        Route::match(['get', 'post'], '/', 'KontenrahmenController@request')->name('index');
    });

    Route::group(['prefix' => 'kundenweb', 'as' => 'kundenweb::'], function () {
        Route::match(['get', 'post'], '/', 'KundenwebController@request')->name('index');
    });

    Route::group(['prefix' => 'lager', 'as' => 'lager::'], function () {
        Route::match(['get', 'post'], '/', 'LagerController@request')->name('index');
    });

    Route::group(['prefix' => 'leerstand', 'as' => 'leerstand::'], function () {
        Route::match(['get', 'post'], '/', 'LeerstandController@request')->name('index');
    });

    Route::group(['prefix' => 'listen', 'as' => 'listen::'], function () {
        Route::match(['get', 'post'], '/', 'ListenController@request')->name('index');
    });

    Route::group(['prefix' => 'mietanpassungen', 'as' => 'mietanpassungen::'], function () {
        Route::match(['get', 'post'], '/', 'MietanpassungenController@request')->name('index');
    });

    Route::group(['prefix' => 'miete_buchen', 'as' => 'miete_buchen::'], function () {
        Route::match(['get', 'post'], '/', 'MieteBuchenController@request')->name('index');
    });

    Route::group(['prefix' => 'miete_definieren', 'as' => 'miete_definieren::'], function () {
        Route::match(['get', 'post'], '/', 'MieteBuchenController@request')->name('index');
    });

    Route::group(['prefix' => 'mietkontenblatt', 'as' => 'mietkontenblatt::'], function () {
        Route::match(['get', 'post'], '/', 'MietkontenblattController@request')->name('index');
    });

    Route::group(['prefix' => 'mietspiegel', 'as' => 'mietspiegel::'], function () {
        Route::match(['get', 'post'], '/', 'MietspiegelController@request')->name('index');
    });

    Route::group(['prefix' => 'mietvertraege', 'as' => 'mietvertraege::'], function () {
        Route::match(['get', 'post'], '/', 'MietvertraegeController@request')->name('index');
        Route::get('create', 'MietvertraegeController@create')->name('create');
        Route::post('store', 'MietvertraegeController@store')->name('store');
    });

    Route::group(['prefix' => 'objekte', 'as' => 'objekte::'], function () {
        Route::match(['get', 'post'], '/', 'ObjekteController@request')->name('index');
        Route::get('{id}/select', 'ObjekteController@select')->name('select');
    });

    Route::group(['prefix' => 'partner', 'as' => 'partner::'], function () {
        Route::match(['get', 'post'], '/', 'PartnerController@request')->name('index');
        Route::get('{id}/select', 'PartnerController@select')->name('select');
    });

    Route::group(['prefix' => 'personal', 'as' => 'personal::'], function () {
        Route::match(['get', 'post'], '/', 'PersonalController@request')->name('index');
    });

    Route::group(['prefix' => 'personen', 'as' => 'personen::'], function () {
        Route::match(['get', 'post'], '/', 'PersonenController@request')->name('index');
    });

    Route::group(['prefix' => 'rechnungen', 'as' => 'rechnungen::'], function () {
        Route::match(['get', 'post'], '/', 'RechnungenController@request')->name('index');
    });

    Route::group(['prefix' => 'sepa', 'as' => 'sepa::'], function () {
        Route::match(['get', 'post'], '/', 'SepaController@request')->name('index');
    });

    Route::group(['prefix' => 'statistik', 'as' => 'statistik::'], function () {
        Route::match(['get', 'post'], '/', 'StatistikController@request')->name('index');
    });

    Route::group(['prefix' => 'tickets', 'as' => 'tickets::'], function () {
        Route::match(['get', 'post'], '/', 'TicketsController@request')->name('index');
    });

    Route::group(['prefix' => 'todo', 'as' => 'todo::'], function () {
        Route::match(['get', 'post'], '/', 'ToDoController@request')->name('index');
    });

    Route::group(['prefix' => 'uebersicht', 'as' => 'uebersicht::'], function () {
        Route::match(['get', 'post'], '/', 'UebersichtController@request')->name('index');
    });

    Route::group(['prefix' => 'urlaub', 'as' => 'urlaub::'], function () {
        Route::match(['get', 'post'], '/', 'UrlaubController@request')->name('index');
    });

    Route::group(['prefix' => 'weg', 'as' => 'weg::'], function () {
        Route::match(['get', 'post'], '/', 'WEGController@request')->name('index');
    });

    Route::group(['prefix' => 'zeiterfassung', 'as' => 'zeiterfassung::'], function () {
        Route::match(['get', 'post'], '/', 'ZeiterfassungController@request')->name('index');
    });

    Route::group(['prefix' => 'wartungsplaner', 'as' => 'wartungsplaner::'], function () {
        Route::match(['get', 'post'], '/', 'WartungsplanerController@index')->name('index');
        Route::match(['get', 'post'], '/ajax', 'WartungsplanerController@ajax')->name('ajax');
        Route::match(['get', 'post'], '/index_ajax', 'WartungsplanerController@indexAjax')->name('indexAjax');
    });
});

Route::group(['prefix' => 'storage', 'namespace' => 'Storage', 'middleware' => ['web', 'auth']], function () {
    Route::get('{path}', 'StorageController@asset')->where('path', '.+');
});
