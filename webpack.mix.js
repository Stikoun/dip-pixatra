// webpack.mix.js

let mix = require('laravel-mix');


mix.js('assets/js/app.js', 'assets/dist').setPublicPath('assets/dist');

mix.postCss('assets/css/app.css', 'assets/dist');
