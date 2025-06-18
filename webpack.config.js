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
    .addEntry('auction-show', './assets/js/auction-show.js')    .addEntry('renting-index', './assets/js/renting/index.js')
    .addEntry('contact-index', './assets/js/contact/index.js')
    .addEntry('faq-index', './assets/js/faq-index.js')
    .addEntry('blog', './assets/js/blog.js')
    .addEntry('admin', './assets/js/admin/admin.js')
    .addEntry('admin-property-form', './assets/js/admin/property-form.js')
    .addEntry('admin-about', './assets/js/admin/about.js')
    .addEntry('admin-property-type', './assets/js/admin/property-type.js')
    .addEntry('admin_blog', './assets/js/admin/blog.js')
    .addEntry('admin_faq', './assets/js/admin/faq.js')
    .addEntry('admin_user', './assets/js/admin/user.js')
    .addEntry('admin/user-edit', [
        './assets/js/admin/user-edit.js',
        './assets/styles/admin/user-edit.scss'
    ])
    .addEntry('admin/user-new', [
        './assets/js/admin/user-new.js',
        './assets/styles/admin/user-new.scss'
    ])
    .addStyleEntry('admin_blog_styles', './assets/styles/admin/blog.scss')
    .addStyleEntry('admin_faq_styles', './assets/styles/admin/faq.scss')
    .addStyleEntry('admin_user_styles', './assets/styles/admin/user.scss')
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