var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .addEntry('app', './assets/js/main.js')
    .addStyleEntry('global', './assets/css/global.scss')
    .enableSassLoader()
    .autoProvidejQuery()
    .enableSourceMaps(!Encore.isProduction());

var config = Encore.getWebpackConfig();
module.exports = Encore.getWebpackConfig();
