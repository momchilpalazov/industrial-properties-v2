const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .addEntry('property-360-viewer', './assets/js/property-360-viewer.js')
    .addEntry('property-inquiry', './assets/js/property-inquiry.js')
    .addEntry('layout', './assets/js/layout.js')
    .addEntry('property-index', './assets/js/property-index.js')
    .addEntry('home', './assets/js/home.js')
    .addEntry('property-show', './assets/js/property-show.js')
    .addEntry('auction-index', './assets/js/auction-index.js')
    .addEntry('auction-show', './assets/js/auction-show.js')
    .addEntry('renting-index', './assets/js/renting/index.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.35';
    })
    .enableSassLoader()
;

module.exports = Encore.getWebpackConfig(); 