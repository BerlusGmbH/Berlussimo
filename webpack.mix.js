const {mix} = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('resources/assets/sass/berlussimo.scss', 'public/css');

mix.styles(['node_modules/normalize.css/normalize.css',
    'public/css/materialize-css.css',
    'node_modules/flexboxgrid/dist/flexboxgrid.css',
    'node_modules/mdi/css/materialdesignicons.css'
], 'public/css/vendor.css');

mix.styles(
    [
        'legacy/wartungsplaner/index.css'
    ],
    'public/css/wartungsplaner.css');

mix.styles(
    [
        'legacy/wartungsplaner/main.css',
        'legacy/wartungsplaner/form.css'
    ],
    'public/css/wp_form.css');

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
    'public/js/legacy.js'
);
mix.scripts(
    [
        'resources/assets/js/materialize_chips_autocomplete.js',
        'resources/assets/js/materialize_autocomplete.js',
        'resources/assets/js/materialize_datepicker_defaults.js',
        'resources/assets/js/materialize_init.js',
        'resources/assets/js/materialize_searchbar.js',
        'resources/assets/js/mainmenu.js'
    ],
    'public/js/berlussimo.js'
);
mix.scripts(
    [
        'legacy/js/wartungsplaner.js',
        'legacy/js/sorttable.js'
    ],
    'public/js/wartungsplaner.js'
);
mix.scripts(
    [
        'node_modules/keycode-js/dist/keycode.js',
        'node_modules/jquery/dist/jquery.js',
        'node_modules/urijs/src/URI.js',
        'node_modules/urijs/src/jquery.URI.js',
        'node_modules/materialize-css/dist/js/materialize.js'
    ],
    'public/js/vendor.js'
);

mix.copyDirectory('legacy/images/', 'public/images/');
mix.copy('legacy/graph/css/LineGraph.css', 'public/css/LineGraph.css');
mix.copy('legacy/graph/css/PieGraph.css', 'public/css/PieGraph.css');
mix.copy('legacy/graph/js/LineGraph.js', 'public/js/LineGraph.js');
mix.copy('legacy/graph/js/PieGraph.js', 'public/js/PieGraph.js');
mix.copyDirectory('legacy/graph/img/', 'public/images/');
mix.copyDirectory('node_modules/mdi/fonts', 'public/fonts');

mix.version();