<?php
/**
 * Entry file for Core framework.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */
use Core\Lib\DI;

// Define that the TekFW has been loaded
define('TEKFW', 1);

// Absolute path to site
define('BASEDIR', __DIR__);

// Check for settings file
if (! file_exists(BASEDIR . '/Settings.php')) {
    die('Settings file could not be loaded.');
}

// Define path to the framweork core.
define('COREDIR', BASEDIR . '/Core');

// Define path to applications.
define('APPSDIR', BASEDIR . '/Apps');

// Define path to themes
define('THEMESDIR', BASEDIR . '/Themes');

// Define path to cache
define('CACHEDIR', BASEDIR . '/Cache');

// Define path to secured apps
define('APPSSECDIR', BASEDIR . '/Core/AppsSec');

// Load basic config from Settings.php
$cfg = include (BASEDIR . '/Settings.php');;

// Define url to Core
define('BASEURL', $cfg['url']);

// Define url of themes
define('THEMESURL', $cfg['url'] . '/Themes');

// Include error handler
require_once (COREDIR . '/Lib/Errors/Error.php');

try {

    // Register composer classloader
    require_once (BASEDIR . '/vendor/autoload.php');

    // Register core classloader
    require_once (COREDIR . '/Tools/autoload/SplClassLoader.php');

    // Register Core classloader
    $loader = new SplClassLoader('Core', BASEDIR);
    $loader->register();

    // Register app classloader
    $loader = new SplClassLoader('Apps', BASEDIR);
    $loader->register();

    // Register themes classloader
    $loader = new SplClassLoader('Themes', BASEDIR);
    $loader->register();

    // start output buffering
    ob_start();

    // Create core di container
    $di = new DI();

    // --------------------------------------
    // 1. Init basic config
    // --------------------------------------

    /* @var $config \Core\Lib\Cfg */
    $config = $di->get('core.cfg');

    // Init config with config from Settings.php
    $config->init($cfg);

    // Load additiona configs from DB
    $config->load();

    // Add dirs to config
    $dirs = [
        // Framework directory
        'fw' => '/Core',

        // Framwork subdirectories
        'css' => '/Core/Css',
        'js' => '/Core/Js',
        'lib' => '/Core/Lib',
        'html' => '/Core/Html',
        'tools' => '/Core/Tools',
        'cache' => '/Cache',

        // Public application dir
        'apps' => '/Apps',

        // Secure application dir
        'appssec' => '/Core/AppsSec'
    ];

    $config->addPaths('Core', $dirs);

    // Add urls to config
    $urls = [
        'apps' => '/Apps',
        'appssec' => '/Core/AppsSec',
        'css' => '/Core/Css',
        'js' => '/Core/Js',
        'tools' => '/Core/Tools',
        'cache' => '/Cache'
    ];

    $config->addUrls('Core', $urls);

    // --------------------------------------
    // 2. Start runtime measurement
    // --------------------------------------

    /* @var $timer \Core\Lib\Utilities\Timer */
    $timer = $di->get('core.util.timer')->start();

    // --------------------------------------
    // 3. Init session handler
    // --------------------------------------
    $di->get('core.http.session')->init();

    // --------------------------------------
    // 4. Init security system
    // --------------------------------------
    $di->get('core.sec.security')->init();

    // --------------------------------------
    // 5. Init essential Core app
    // --------------------------------------

    /* @var $creator \Core\Lib\Amvc\Creator */
    $creator = $di->get('core.amvc.creator');

    $creator->initAppConfig('Core');

    // --------------------------------------
    // 6. Start app autodiscover process
    // --------------------------------------
    $creator->autodiscover([
        $config->get('Core', 'dir_appssec'),
        $config->get('Core', 'dir_apps')
    ]);

    // --------------------------------------
    // 7. Create content
    // --------------------------------------

    /* @var $content \Core\Lib\Content\Content */
    $content = $di->get('core.content');

    $content->create();
}
catch (Exception $e) {
    echo $di->get('core.error')->handleException($e, true);
}

// That's it! Send all stuff to the browser.
ob_end_flush();
