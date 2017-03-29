<?php
Route::group(['namespace' => 'Auth', 'middleware' => ['web']], function () {
    // Authentication Routes...
    Route::get('login', 'AuthController@showLoginForm');
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout');
    // Registration Routes...
    //Route::get('register', 'AuthController@showRegistrationForm');
    //Route::post('register', 'AuthController@register');
    // Password Reset Routes...
    Route::get('password/reset/{token?}', 'PasswordController@showResetForm');
    Route::post('password/email', 'PasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'PasswordController@reset');
});

Route::group(['namespace' => 'Legacy', 'middleware' => ['web', 'auth'], 'as' => 'web::'], function () {
    Route::match(['get', 'post'], '/', 'IndexController@request')->name('legacy');

    Route::match(['get', 'post'], 'ajax/ajax_info.php', 'IndexController@ajax')->name('ajax');

    Route::get('svgraph/line.svg', 'IndexController@line')->name('line');

    Route::get('svgraph/pie.svg', 'IndexController@pie')->name('pie');

    Route::group(['prefix' => 'admin', 'as' => 'admin::'], function () {
        Route::match(['get', 'post'], '/', 'AdminController@request')->name('legacy');
    });

    Route::group(['prefix' => 'benutzer', 'as' => 'benutzer::'], function () {
        Route::match(['get', 'post'], '/', 'BenutzerController@request')->name('legacy');
        Route::get('index', 'BenutzerController@index')->name('index');
    });

    Route::group(['prefix' => 'bk', 'as' => 'bk::'], function () {
        Route::match(['get', 'post'], '/', 'BkController@request')->name('legacy');
    });

    Route::group(['prefix' => 'buchen', 'as' => 'buchen::'], function () {
        Route::match(['get', 'post'], '/', 'BuchenController@request')->name('legacy');
    });

    Route::group(['prefix' => 'details', 'as' => 'details::'], function () {
        Route::match(['get', 'post'], '/', 'DetailsController@request')->name('legacy');
    });

    Route::group(['prefix' => 'einheiten', 'as' => 'einheiten::'], function () {
        Route::match(['get', 'post'], '/', 'EinheitenController@request')->name('legacy');
        Route::get('index', 'EinheitenController@index')->name('index');
        Route::get('{id}', 'EinheitenController@show')->name('show');
    });

    Route::group(['prefix' => 'einheitenform', 'as' => 'einheitenform::'], function () {
        Route::match(['get', 'post'], '/', 'EinheitenFormController@request')->name('legacy');
    });

    Route::group(['prefix' => 'haeuserform', 'as' => 'haeuserform::'], function () {
        Route::match(['get', 'post'], '/', 'HaeuserFormController@request')->name('legacy');
    });

    Route::group(['prefix' => 'objekteform', 'as' => 'objekteform::'], function () {
        Route::match(['get', 'post'], '/', 'ObjekteFormController@request')->name('legacy');
    });

    Route::group(['prefix' => 'geldkonten', 'as' => 'geldkonten::'], function () {
        Route::match(['get', 'post'], '/', 'GeldkontenController@request')->name('legacy');
        Route::get('{id}/select', 'GeldkontenController@select')->name('select');
    });

    Route::group(['prefix' => 'haeuser', 'as' => 'haeuser::'], function () {
        Route::match(['get', 'post'], '/', 'HaeuserController@request')->name('legacy');
        Route::get('index', 'HaeuserController@index')->name('index');
        Route::get('{id}', 'HaeuserController@show')->name('show');
    });

    Route::group(['prefix' => 'kassen', 'as' => 'kassen::'], function () {
        Route::match(['get', 'post'], '/', 'KassenController@request')->name('legacy');
    });

    Route::group(['prefix' => 'katalog', 'as' => 'katalog::'], function () {
        Route::match(['get', 'post'], '/', 'KatalogController@request')->name('legacy');
    });

    Route::group(['prefix' => 'kautionen', 'as' => 'kautionen::'], function () {
        Route::match(['get', 'post'], '/', 'KautionenController@request')->name('legacy');
    });

    Route::group(['prefix' => 'kontenrahmen', 'as' => 'kontenrahmen::'], function () {
        Route::match(['get', 'post'], '/', 'KontenrahmenController@request')->name('legacy');
    });

    Route::group(['prefix' => 'lager', 'as' => 'lager::'], function () {
        Route::match(['get', 'post'], '/', 'LagerController@request')->name('legacy');
    });

    Route::group(['prefix' => 'leerstand', 'as' => 'leerstand::'], function () {
        Route::match(['get', 'post'], '/', 'LeerstandController@request')->name('legacy');
    });

    Route::group(['prefix' => 'listen', 'as' => 'listen::'], function () {
        Route::match(['get', 'post'], '/', 'ListenController@request')->name('legacy');
    });

    Route::group(['prefix' => 'mietanpassungen', 'as' => 'mietanpassungen::'], function () {
        Route::match(['get', 'post'], '/', 'MietanpassungenController@request')->name('legacy');
    });

    Route::group(['prefix' => 'miete_buchen', 'as' => 'miete_buchen::'], function () {
        Route::match(['get', 'post'], '/', 'MieteBuchenController@request')->name('legacy');
    });

    Route::group(['prefix' => 'miete_definieren', 'as' => 'miete_definieren::'], function () {
        Route::match(['get', 'post'], '/', 'MieteDefinierenController@request')->name('legacy');
    });

    Route::group(['prefix' => 'mietkontenblatt', 'as' => 'mietkontenblatt::'], function () {
        Route::match(['get', 'post'], '/', 'MietkontenblattController@request')->name('legacy');
    });

    Route::group(['prefix' => 'mietspiegel', 'as' => 'mietspiegel::'], function () {
        Route::match(['get', 'post'], '/', 'MietspiegelController@request')->name('legacy');
    });

    Route::group(['prefix' => 'mietvertraege', 'as' => 'mietvertraege::'], function () {
        Route::match(['get', 'post'], '/', 'MietvertraegeController@request')->name('legacy');
        Route::get('create', 'MietvertraegeController@create')->name('create');
        Route::post('store', 'MietvertraegeController@store')->name('store');
    });

    Route::group(['prefix' => 'objekte', 'as' => 'objekte::'], function () {
        Route::match(['get', 'post'], '/', 'ObjekteController@request')->name('legacy');
        Route::get('{id}/select', 'ObjekteController@select')->name('select');
        Route::get('index', 'ObjekteController@index')->name('index');
        Route::get('{id}', 'ObjekteController@show')->name('show');
    });

    Route::group(['prefix' => 'partner', 'as' => 'partner::'], function () {
        Route::match(['get', 'post'], '/', 'PartnerController@request')->name('legacy');
        Route::get('{id}/select', 'PartnerController@select')->name('select');
    });

    Route::group(['prefix' => 'personal', 'as' => 'personal::'], function () {
        Route::match(['get', 'post'], '/', 'PersonalController@request')->name('legacy');
    });

    Route::group(['prefix' => 'personen', 'as' => 'personen::'], function () {
        Route::match(['get', 'post'], '/', 'PersonenController@request')->name('legacy');
        Route::get('index', 'PersonenController@index')->name('index');
        Route::get('create', 'PersonenController@create')->name('create');
        Route::post('store', 'PersonenController@store')->name('store');
        Route::get('{id}', 'PersonenController@show')->name('show');
    });

    Route::group(['prefix' => 'rechnungen', 'as' => 'rechnungen::'], function () {
        Route::match(['get', 'post'], '/', 'RechnungenController@request')->name('legacy');
    });

    Route::group(['prefix' => 'sepa', 'as' => 'sepa::'], function () {
        Route::match(['get', 'post'], '/', 'SepaController@request')->name('legacy');
    });

    Route::group(['prefix' => 'statistik', 'as' => 'statistik::'], function () {
        Route::match(['get', 'post'], '/', 'StatistikController@request')->name('legacy');
    });

    Route::group(['prefix' => 'todo', 'as' => 'todo::'], function () {
        Route::match(['get', 'post'], '/', 'ToDoController@request')->name('legacy');
        Route::get('index', 'ToDoController@index')->name('index');
    });

    Route::group(['prefix' => 'uebersicht', 'as' => 'uebersicht::'], function () {
        Route::match(['get', 'post'], '/', 'UebersichtController@request')->name('legacy');
    });

    Route::group(['prefix' => 'urlaub', 'as' => 'urlaub::'], function () {
        Route::match(['get', 'post'], '/', 'UrlaubController@request')->name('legacy');
    });

    Route::group(['prefix' => 'weg', 'as' => 'weg::'], function () {
        Route::match(['get', 'post'], '/', 'WEGController@request')->name('legacy');
    });

    Route::group(['prefix' => 'zeiterfassung', 'as' => 'zeiterfassung::'], function () {
        Route::match(['get', 'post'], '/', 'ZeiterfassungController@request')->name('legacy');
    });

    Route::group(['prefix' => 'wartungsplaner', 'as' => 'wartungsplaner::'], function () {
        Route::match(['get', 'post'], '/', 'WartungsplanerController@index')->name('legacy');
        Route::match(['get', 'post'], '/ajax', 'WartungsplanerController@ajax')->name('ajax');
        Route::match(['get', 'post'], '/index_ajax', 'WartungsplanerController@indexAjax')->name('legacyAjax');
    });
});

Route::group(['prefix' => 'storage', 'namespace' => 'Storage', 'middleware' => ['web', 'auth']], function () {
    Route::get('{path}', 'StorageController@asset')->where('path', '.+');
});

Route::group(['prefix' => 'api/v1', 'namespace' => 'Api\v1', 'middleware' => ['web', 'auth']], function () {
    Route::get('/search', 'SearchBarController@search')->name('search');
});
