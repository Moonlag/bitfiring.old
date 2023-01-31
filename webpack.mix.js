const mix = require('laravel-mix');
require('laravel-mix-webp')
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

mix
    .sass('resources/assets/sass/app.scss', '/assets/css/crypto.css', {
        implementation: require('node-sass'),
    })
    .options({
        processCssUrls: false,
    })
    .vue()
    .js('resources/js/app.js', 'public/js');

mix.js('resources/vue/app.js', 'public/js')
    .vue();

// mix.js('resources/js/games/game_filter.js', 'public/js')
//     .vue()
//     .postCss('resources/css/app.css', 'public/css');

// mix.js('resources/js/player_group/player_group.js', 'public/js')
//     .vue();
//
// mix.js('resources/js/groups.js', 'public/js')
//     .vue();
//
// mix.js('resources/js/payment_sorting_system.js', 'public/js')
//     .vue();
//
// mix.js('resources/js/bonuses/bonuses.js', 'public/js')
//     .vue();
//
// mix.js('resources/js/denomination/denomination.js', 'public/js')
//     .vue();
//

mix.js('resources/js/languages/main.js', 'public/js/languages.js')
    .vue();

mix
    .ImageWebp({
        from: 'resources/assets/images',
        to: 'public/assets/img',
        imageminWebpOptions: {
            quality: 90
        },
    })

mix.webpackConfig(webpack => {
    return {
        plugins: [
            new webpack.DefinePlugin({
                API_BASE_URL: JSON.stringify('https://cdn.bitfiring.com'),
                '__VUE_I18N_FULL_INSTALL__': JSON.stringify(true),
                '__VUE_I18N_LEGACY_API__': JSON.stringify(true),
                '__INTLIFY_PROD_DEVTOOLS__': JSON.stringify(true)
            }),
        ],
    }
});
