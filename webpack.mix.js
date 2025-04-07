const path = require('path');
const mix = require('laravel-mix');
require('laravel-mix-tailwind');
require('laravel-mix-svg-vue');

// Compile dashboard assets
mix.js('resources/js/app.js', 'public/js/app.js')
    .vue({ version: 2 })
    .sourceMaps();
mix.sass('resources/sass/app.scss', 'public/css/app.css')
    .tailwind('./tailwind.config.js')
    .options({
        processCssUrls: false
    });

// Use laravel-mix-svg-vue for SVG processing
mix.svgVue({
    svgPath: 'resources/svg',
    extract: false,
    svgoSettings: [
        { removeDoctype: true },
        { removeComments: true },
        { removeViewBox: false }
    ]
});

// Additional webpack config
mix.webpackConfig({
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
            'moment': path.resolve(__dirname, 'node_modules', 'moment', 'moment.js'),
        },
    },
});

// Copy tinymce skins
mix.copy('node_modules/tinymce/skins', 'public/js/skins');

// Mix version
if (mix.inProduction()) {
    mix.version();
}

// Disable notifications
mix.disableSuccessNotifications();
