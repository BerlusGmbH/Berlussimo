var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
    mix.styles(['berlussimo.css'], 'public/css/berlussimo.css');

    mix.sass(['./node_modules/normalize.css/normalize.css',
        'materialize-css.scss',
        './node_modules/flexboxgrid/dist/flexboxgrid.css',
        './node_modules/mdi/css/materialdesignicons.css'
    ], 'public/css/vendor.css');

    mix.styles(
        [
            'legacy/wartungsplaner/index.css'
        ],
        'public/css/wartungsplaner.css', '.');

    mix.styles(
        [
            'legacy/wartungsplaner/main.css',
            'legacy/wartungsplaner/form.css'
        ],
        'public/css/wp_form.css', '.');

    mix.scripts(
        [
            'legacy/ajax/ajax.js',
            'legacy/ajax/dd_kostenkonto.js',
            'legacy/js/javascript.js',
            'legacy/js/sorttable.js',
            'legacy/js/foto_upload.js',
            'legacy/graph/js/LineGraph.js',
            'legacy/graph/js/PieGraph.js'
        ],
        'public/js/legacy.js', '.'
    );
    mix.scripts(
        [
            'materialize_chips_autocomplete.js',
            'materialize_autocomplete.js',
            'materialize_datepicker_defaults.js',
            'materialize_init.js',
            'materialize_searchbar.js'
        ],
        'public/js/berlussimo.js'
    );
    mix.scripts(
        [
            'legacy/js/wartungsplaner.js',
            'legacy/js/sorttable.js'
        ],
        'public/js/wartungsplaner.js', '.'
    );
    mix.scripts(
        [
            'node_modules/keycode-js/dist/keycode.js',
            'node_modules/jquery/dist/jquery.js',
            'node_modules/urijs/src/URI.js',
            'node_modules/urijs/src/jquery.URI.js',
            'node_modules/materialize-css/dist/js/materialize.js'
        ],
        'public/js/vendor.js', '.'
    );

    mix.copy('legacy/images/', 'public/images/');
    mix.copy('legacy/graph/css/LineGraph.css', 'public/css/LineGraph.css');
    mix.copy('legacy/graph/css/PieGraph.css', 'public/css/PieGraph.css');
    mix.copy('legacy/graph/js/LineGraph.js', 'public/js/LineGraph.js');
    mix.copy('legacy/graph/js/PieGraph.js', 'public/js/PieGraph.js');
    mix.copy('legacy/graph/img/', 'public/images/');
    mix.copy('node_modules/mdi/fonts', 'public/build/fonts');

    mix.version(
        ['public/css/berlussimo.css',
            'public/css/vendor.css',
            'public/css/wartungsplaner.css',
            'public/css/wp_form.css',
            'public/js/berlussimo.js',
            'public/js/vendor.js',
            'public/js/legacy.js',
            'public/js/wartungsplaner.js'
        ]
    );

    mix.copy('node_modules/materialize-css/dist/fonts', 'public/build/fonts');
});
