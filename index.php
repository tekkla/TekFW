<?php
/**
 * Entry file for TekFW framework.
 *
 * It defines the WEB constant for direct accesschecks,
 * defines constants to get rid of some global var use,
 * and registers an autoclassloader.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */
use Core\Lib\DI;

// Define that the TekFW has been loaded
define('TEKFW', 1);

/**
 * Absolute path to site
 */
define('BASEDIR', __DIR__);

$cfg = [];

// Check for settings file
if (! file_exists(BASEDIR . '/Settings.php')) {
    die('Settings file could not be loaded.');
}

// Load Settings.php file
require_once (BASEDIR . '/Settings.php');

// Include error handler
require_once (COREDIR . '/Lib/Errors/Error.php');

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

try {

    // start output buffering
    ob_start();

    // Create core di container
    $di = new DI();

    // --------------------------------------
    // 1. Init basic config
    // --------------------------------------

    /* @var $config \Core\Lib\Cfg */
    $config = $di->get('core.cfg');

    $config->init($cfg);
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

    // Initialize the apps
    $app_dirs = [
        $config->get('Core', 'dir_appssec'),
        $config->get('Core', 'dir_apps')
    ];

    // --------------------------------------
    // 6. Init essential content
    // --------------------------------------

    /* @var $content \Core\Lib\Content\Content */
    $content = $di->get('core.content');

    $content->setTitle($config->get('Core', 'sitename'));
    $content->meta->setCharset();
    $content->meta->setViewport();

    $content->css->init();
    $content->js->init();

    // --------------------------------------
    // 6. Start app autodiscover process
    // --------------------------------------
    $creator->autodiscover($app_dirs);

    // --------------------------------------
    // 7. Run router to get match
    // --------------------------------------

    /* @var $router \Core\Lib\Http\Router */
    $router = $di->get('core.http.router');

    // Match request against stored routes
    $router->match();

    // --------------------------------------
    // 8. Run called app
    // --------------------------------------

    // Try to use appname provided by router
    $app_name = $router->getApp();

    // No app by request? Try to get default app from config or set Core as
    // default app
    if (! $app_name) {
        $app_name = $config->exists('Core', 'default_app') ? $config->get('Core', 'default_app') : 'Core';
    }

    // Start with factoring the requested app

    /* @var $app \Core\Lib\Amvc\App */
    $app = $creator->create($app_name);

    /**
     * Each app can have it's own start procedure.
     * This procedure is used to
     * init apps with more than the app creator does. To use this feature the
     * app needs run() method in it's main file.
     */
    if (method_exists($app, 'run')) {
        $app->run();
    }

    // Get name of requested controller
    $controller_name = $router->getCtrl();

    // Set controller name to "Index" when no controller name has been returned
    // from request handler
    if (! $controller_name) {
        $controller_name = 'Index';
    }

    // Load controller object
    $controller = $app->getController($controller_name);

    // Which controller action has to be run?
    $action = $router->getAction();

    // No action => use Index as default
    if (! $action) {
        $action = 'Index';
    }

    // Are there parameters to pass to run method?
    $params = $router->getParam();

    // Run controller and process result.
    if ($router->isAjax()) {

        try {

            // Result will be processed as ajax command list
            $controller->ajax($action, $params);
        }
        catch (Exception $e) {

            $di->get('core.error')->handleException($e, false, true);
        }

        // Send cache preventing headers and set content type
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');

        // Run ajax processor
        echo $di->get('core.ajax')->process();

        // End end here
        exit();
    }
    else {

        Try {

            // Run controller and store result
            $result = $controller->run($action, $params);

            // No content created? Check app for onEmpty() event which maybe gives us content.
            if (empty($result) && method_exists($app, 'onEmpty')) {
                $result = $app->onEmpty();
            }

            // Append content provided by apps onBefore() event method
            if (method_exists($app, 'onBefore')) {
                $result = $app->onBefore() . $result;
            }

            // Prepend content provided by apps onAfter() event method
            if (method_exists($app, 'onAfter')) {
                $result .= $app->onAfter();
            }
        }
        catch (Exception $e) {

            $result = $di->get('core.error')->handleException($e);
        }

        $content->setContent($result);

        // Call content builder
        echo $content->render();

        $fb = [
            'Runtime' => $timer->getDiff() . 's',
            'Permissions' => $di->get('core.sec.permission')->getPermissions(),
            'Router match' => $router->match()
        ];

        $debug = $content->getDebug();

        if ($debug) {
            $fb['Content debug'] = $debug;
        }

        \FB::log($fb);
    }
}

// # Error handling
catch (Exception $e) {
    echo $di->get('core.error')->handleException($e, true);
}

// That's it! Send all stuff to the browser.
ob_end_flush();
