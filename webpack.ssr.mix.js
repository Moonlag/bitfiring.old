const path = require('path')
const mix = require('laravel-mix')
const webpackNodeExternals = require('webpack-node-externals')

mix.alias({
    ziggy: path.resolve("vendor/tightenco/ziggy/dist/vue"),
});

mix
    .options({ manifest: false })
    .js('resources/vue/ssr.js', 'public/js')
    .vue({ version: 3, options: { optimizeSSR: true } })
    .alias({ '@': path.resolve('resources/vue') })
    .webpackConfig({
        target: 'node',
        externals: [webpackNodeExternals()],
    })
