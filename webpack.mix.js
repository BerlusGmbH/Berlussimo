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

mix.webpackConfig({
    resolve: {
        alias: {
            'jquery': path.join(__dirname, 'node_modules/jquery/dist/jquery')
        }
    }
});

mix.sass('resources/assets/sass/berlussimo.scss', 'public/css');

mix.sass('resources/assets/sass/materialize-css.scss', 'public/css');

mix.stylus('node_modules/vuetify/src/stylus/main.styl', 'public/css');

mix.styles(
    [
        'node_modules/normalize.css/normalize.css',
        'node_modules/flexboxgrid/dist/flexboxgrid.css',
    ],
    'public/css/vendor.css'
);

mix.styles(
    [
        'legacy/wartungsplaner/index.css'
    ],
    'public/css/wartungsplaner.css'
);

mix.styles(
    [
        'legacy/wartungsplaner/main.css',
        'legacy/wartungsplaner/form.css'
    ],
    'public/css/wp_form.css'
);

mix.ts(
    [
        'resources/assets/js/app.ts'
    ],
    'public/js/'
);

mix.ts(
    [
        'resources/assets/js/app-materialize.ts'
    ],
    'public/js/'
);

mix.js(
    [
        'resources/assets/js/materialize.js',
        'resources/assets/js/materialize_autocomplete.js',
        'resources/assets/js/materialize_chips_autocomplete.js',
        'resources/assets/js/materialize_init.js'
    ],
    'public/js/'
);

mix.scripts(
    [
        'legacy/ajax/ajax.js',
        'legacy/ajax/dd_kostenkonto.js',
        'legacy/js/javascript.js',
        'legacy/js/sorttable.js'
    ],
    'public/js/legacy.js'
);

mix.scripts(
    [
        'legacy/js/wartungsplaner.js',
        'legacy/js/sorttable.js'
    ],
    'public/js/wartungsplaner.js'
);

mix.copyDirectory('legacy/images/', 'public/images/');
mix.copy('legacy/graph/css/LineGraph.css', 'public/css/LineGraph.css');
mix.copy('legacy/graph/css/PieGraph.css', 'public/css/PieGraph.css');
mix.copy('legacy/graph/js/LineGraph.js', 'public/js/LineGraph.js');
mix.copy('legacy/graph/js/PieGraph.js', 'public/js/PieGraph.js');
mix.copyDirectory('legacy/graph/img/', 'public/images/');

mix.extract(
    [
        'axios',
        'keycode-js',
        'urijs',
        'lodash',
        'vue',
        'vuex',
        'vuetify'
    ],
    'public/js/vendor.js'
);

mix.sourceMaps();

if (mix.inProduction()) {
    mix.version();
}