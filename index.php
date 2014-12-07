<?php
/**
 * Entry file for TekFW framework.
 *
 * It defines the WEB constant for direct accesschecks,
 * defines constants to get rid of some global var use,
 * and registers an autoclassloader.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package TekFW
 * @subpackage Global
 * @license MIT
 * @copyright 2014 by author
 */
use Core\Lib\DI;

// Define that the TekFW has been loaded
define('TEKFW', 1);

/**
 * Absolute path to site
 */
define('BASEDIR', __DIR__);

$cfg = [];

// Load Settings.php file
if (file_exists(BASEDIR . '/Settings.php')) {
    require_once (BASEDIR . '/Settings.php');
} else {
    die('Settings file could not be loaded.');
}

// Include error handler
require_once (COREDIR . '/Lib/Errors/Error.php');

// Register composer classloader
require_once (BASEDIR . '/vendor/autoload.php');

// Register core classloader
require_once (COREDIR . '/Tools/autoload/SplClassLoader.php');
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

try {

    // --------------------------------------------------------
    // Create DI service container
    // --------------------------------------------------------
    $di = new DI();

    // -------------------------------------------------------
    // Config
    // -------------------------------------------------------

    /* @var $config \Core\Lib\Cfg */
    $config = $di['core.cfg'];

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

    // Runtime measurement start
    $timer = $di['core.util.timer'];
    $timer->start();

    // # Start session by calling instance factory
    $di['core.http.session']->init();

    // Try to autologin the user
    $di['core.sec.security']->init();

    // Use app creator to init Core config

    /* @var $app_creator \Core\Lib\Amvc\Creator */
    $app_creator = $di['core.amvc.creator'];
    $app_creator->initAppConfig('Core');

    // Initialize the apps
    $app_dirs = [
        $config->get('Core', 'dir_appssec'),
        $config->get('Core', 'dir_apps')
    ];

    /* @var $content \Core\Lib\Content\Content */
    $content = $di['core.content'];

    $content->setTitle($config->get('Core', 'sitename'));
    $content->meta->setCharset();
    $content->meta->setViewport();

    // Autodiscover installed apps
    $app_creator->autodiscover($app_dirs);

    /* @var $router \Core\Lib\Http\Router */
    $router = $di['core.http.router'];

    // Match request against stored routes
    $router->match();

    $content->css->init();
    $content->js->init();

    // Try to use appname provided by router
    $app_name = $router->getApp();

    // No app by request? Try to get default app from config or set Core as
    // default app
    if (! $app_name) {
        $app_name = $config->exists('Core', 'default_app') ? $config->get('Core', 'default_app') : 'Core';
    }

    // Start with factoring the requested app

    /* @var $app \Core\Lib\Amvc\App */
    $app = $app_creator->create($app_name);

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
        } catch (Exception $e) {
            $di->get('core.error')->handleException($e, true, false);
        }

        // Run ajax processor
        header('Content-type: application/json');
        echo $di['core.ajax']->process();

        // End end here
        exit();

    } else {

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
        } catch (Exception $e) {

            $result = $di->get('core.error')->handleException($e, false);
        }

        $content->setContent($result);

        // Call content builder
        $content->render();

        echo '
		<div class="container" style="max-height: 300px; overflow-y: scroll;">
			<hr>
			<h4>Debug</h4>
			<p>Runtime : ' . $timer->stop() . 's</p>
			<p>User Permissions:</p>
			<p>';

        var_dump($di['core.sec.security']->getPermissions());

        echo '
			</p>
			<p>Permissions:</p>
			<p>';

        var_dump($di['core.sec.permission']->getPermissions());

        echo '
			</p>
			<p>Match:</p>
			<p>';

        var_dump($router->match());

        echo '
			</p>';

            $debug = $content->getDebug();

            if ($debug) {
                echo '
                <h5>Debug:</h5>
                <p>';
                echo implode('<br>', $content->getDebug());

                echo '
                </p>';
            }

        echo '
        </div>';
    }
}

// # Error handling
catch (Exception $e) {
    echo $di['core.error']->handleException($e);
}

// That's it! Send all stuff to the browser.
ob_end_flush();
