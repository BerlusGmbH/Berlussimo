const webpack = require('webpack');
const mix = require('laravel-mix');

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

mix.options({
    hmrOptions: {
        host: 'berlussimo.test',
        port: 8080
    }
});

mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.(graphql|gql)$/,
                exclude: /node_modules/,
                loader: 'graphql-tag/loader',
            },
            {
                test: /\.(graphqls|gqls)$/,
                exclude: /node_modules/,
                loader: 'raw-loader',
            }
        ]
    },
    resolve: {
        alias: {
            'jquery': path.join(__dirname, 'node_modules/jquery/dist/jquery')
        }
    },
    plugins: [
        new webpack.ContextReplacementPlugin(
            /graphql-language-service-interface[\\/]dist$/,
            new RegExp(`^\\./.*\\.js$`)
        )
    ]
});

mix.sass('resources/sass/berlussimo.scss', 'public/css');

mix.sass('resources/sass/materialize-css.scss', 'public/css');

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
        'resources/js/app.ts'
    ],
    'public/js/'
);

mix.ts(
    [
        'resources/js/app-materialize.ts'
    ],
    'public/js/'
);

mix.js(
    [
        'resources/js/materialize.js'
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

mix.babel(
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
        'keycode-js',
        'urijs',
        'lodash',
        'vue',
        'vuex',
        'vuetify'
    ],
    'public/js/vendor.js'
);


if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps();
}
