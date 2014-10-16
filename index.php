<?php
/**
 * Entry file for TekFW framework.
 *
 * It defines the WEB constant for direct accesschecks,
 * defines constants to get rid of some global var use,
 * and registers an autoclassloader.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Global
 * @license MIT
 * @copyright 2014 by author
 */
use Core\Lib\DI;

// Define that the TekFW has been loaded
define('TEKFW', 1);

$cfg = [];

// Load Settings.php file
if (file_exists(dirname(__FILE__) . '/Settings.php')) {
	require_once dirname(__FILE__) . '/Settings.php';
} else {
	die('Settings file could not be loaded.');
}

// Register core classloader
require_once (COREDIR . '/Tools/autoload/SplClassLoader.php');
$loader = new SplClassLoader('Core', BASEDIR);
$loader->register();

// Register app classloader
$loader = new SplClassLoader('Apps', BASEDIR);
$loader->register();

// start output buffering
ob_start();

try {

	// --------------------------------------------------------
	// Prepare Dependency Injection
	// --------------------------------------------------------

	$di = new DI();

	// == DB ===========================================================
	$di->mapValue('db.default.dsn', $cfg['db_dsn']);
	$di->mapValue('db.default.user', $cfg['db_user']);
	$di->mapValue('db.default.pass', $cfg['db_pass']);
	$di->mapValue('db.default.options', [
		\PDO::ATTR_PERSISTENT => true,
		\PDO::ATTR_ERRMODE => 2,
		\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
	]);
	$di->mapValue('db.default.prefix', $cfg['db_prefix']);
	$di->mapService('db.default.pdo', '\PDO', [
		'db.default.dsn',
		'db.default.user',
		'db.default.pass',
		'db.default.options'
	]);
	$di->mapFactory('db.default', '\Core\Lib\Data\Database', [
		'db.default.pdo',
		'db.default.prefix'
	]);

	// == CONFIG =======================================================
	$di->mapService('core.cfg', '\Core\Lib\Cfg', 'db.default');

	// == CORE =========================================================
	$di->mapService('core.session', '\Core\Lib\Session', 'db.default');
	$di->mapService('core.request', '\Core\Lib\Request');
	$di->mapFactory('core.cookie', '\Core\Lib\Cookie');
	$di->mapFactory('core.error', '\Core\Lib\Error\Error');

	// == UTILITIES ====================================================
	$di->mapFactory('core.util.timer', '\Core\Lib\Utilities\Timer');
	$di->mapFactory('core.util.time', '\Core\Lib\Utilities\Time');
	$di->mapFactory('core.util.shorturl', '\Core\Lib\Utilities\ShortenURL');
	$di->mapFactory('core.util.date', '\Core\Lib\Utilities\Date');
	$di->mapFactory('core.util.debug', '\Core\Lib\Utilities\Debug');
	$di->mapService('core.util.fire', '\FB');

	// == SECURITY =====================================================
	$di->mapService('core.sec.security', '\Core\Lib\Security\Security', [
		'db.default',
		'core.cfg',
		'core.session',
		'core.cookie',
		'core.sec.user.current',
		'core.sec.group',
		'core.sec.permission'
	]);
	$di->mapFactory('core.sec.user', '\Core\Lib\Security\User', [
		'db.default',
		'core.sec.permission'
	]);
	$di->mapService('core.sec.user.current', '\Core\Lib\Security\User', [
		'db.default',
		'core.sec.permission'
	]);
	$di->mapFactory('core.sec.inputfilter', '\Core\Lib\Security\Inputfilter');
	$di->mapService('core.sec.permission', '\Core\Lib\Security\Permission', 'db.default');
	$di->mapService('core.sec.group', '\Core\Lib\Security\Group', 'db.default');

	// == AMVC =========================================================
	$di->mapService('core.amvc.creator', '\Core\Lib\Amvc\Creator');
	$di->mapFactory('core.amvc.app', '\Core\Lib\Amvc\App');

	// == IO ===========================================================
	$di->mapFactory('core.io.file', '\Core\Lib\IO\File');
	$di->mapFactory('core.io.http', '\Core\Lib\IO\Http');

	// == DATA ==========================================================
	$di->mapFactory('core.data.validator', '\Core\Lib\Data\Validator');

	// == CONTENT =======================================================
	$di->mapService('core.content.page', '\Core\Lib\Content\Page', [
		'core.request',
		'core.cfg',
		'core.content.js',
		'core.content.css',
		'core.content.message',
		'core.content.nav',
		'core.amvc.creator'
	]);
	$di->mapService('core.content.lang', '\Core\Lib\Content\Language');
	$di->mapService('core.content.ajax', '\Core\Lib\Content\Ajax', 'core.request');
	$di->mapFactory('core.content.ajaxcmd', '\Core\Lib\Content\AjaxCommand');
	$di->mapFactory('core.content.css', '\Core\Lib\Content\Css', 'core.cfg');
	$di->mapFactory('core.content.js', '\Core\Lib\Content\Javascript', [
		'core.cfg',
		'core.request'
	]);
	$di->mapFactory('core.content.message', '\Core\Lib\Content\Message');
	$di->mapFactory('core.content.url', '\Core\Lib\Content\Url', 'core.request');
	$di->mapService('core.content.nav', '\Core\Lib\Content\Menu');
	$di->mapFactory('core.content.menu', '\Core\Lib\Content\Menu');
	$di->mapService('core.content.html.factory', '\Core\Lib\Content\Html\HtmlFactory');



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

	// Use app creator to init Core config

	/* @var $app_creator \Core\Lib\Amvc\Creator */
	$app_creator = $di['core.amvc.creator'];
	$app_creator->initAppConfig('Core');

	// # Start session by calling instance factory
	$di['core.session']->init();

	// Try to autologin the user
	$di['core.sec.security']->init();

	// Initialize the apps
	$app_dirs = [
		$config->get('Core', 'dir_appssec'),
		$config->get('Core', 'dir_apps')
	];

	// Autodiscover installed apps
	$app_creator->autodiscover($app_dirs);

	// # Handling on and without ajax request

	/* @var $request \Core\Lib\Request */
	$request = $di['core.request'];

	// Run request handler
	$request->processRequest();

	// # Prepare conent

	/* @var $page \Core\Lib\Content\Page */
	$page = $di['core.content.page'];
	$page->init();

	// Try to use appname provided by request handler.
	$app_name = $request->getApp();

	// No app by request? Try to get default app from config or set Core as
	// default app
	if (! $app_name) {
		$app_name = $config->exists('Core', 'default_app') ? $config->get('Core', 'default_app') : 'Core';
	}

	// Start with factoring the requested app

	/* @var $app \Core\Lib\Amvc\App */
	$app = $app_creator->create($app_name);

	/**
	 * Each app can have it's own start procedure. This procedure is used to
	 * init apps with more than the app creator does. To use this feature the
	 * app needs run() method in it's main file.
	 */
	if (method_exists($app, 'run')) {
		$app->run();
	}

	// Intit basic page css and javascript
	$di['core.content.css']->init();
	$di['core.content.js']->init();

	// Get name of requested controller
	$controller_name = $request->getCtrl();

	// Set controller name to "Index" when no controller name has been returned
	// from request handler
	if (! $controller_name) {
		$controller_name = 'Index';
	}

	// Load controller object
	$controller = $app->getController($controller_name);

	// Which controller action has to be run?
	$action = $request->getAction();

	// No action => use Index as default
	if (! $action) {
		$action = 'Index';
	}

	// Are there parameters to pass to run method?
	$param = $request->getParam();

	// Run controller and process result.
	if ($request->isAjax()) {

		// Result will be processed as ajax command list
		$controller->ajax($action, $param);

		// Run ajax processor
		$di['core.content.ajax']->process();
	} else {

		// Run controller and store result
		$content = $controller->run($action, $param);

		// No content created? Check app for onEmpty() event which maybe gives us content.
		if (empty($content) && method_exists($app, 'onEmpty')) {
			$content = $app->onEmpty();
		}

		// Append content provided by apps onBefore() event method
		if (method_exists($app, 'onBefore')) {
			$content = $app->onBefore() . $content;
		}

		// Prepend content provided by apps onAfter() event method
		if (method_exists($app, 'onAfter')) {
			$content .= $app->onAfter();
		}

		// Call content builder
		$page->build($content);

		echo '
		<div class="container">
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
		</div>';
	}
}

// # Error handling
catch (Exception $e) {

	if ($e instanceof PDOException) {
		switch ($e->getCode()) {
			case 2002:
				echo 'DB host not found.';
				break;

			default:
				echo $e->getMessage();
		}
	} else {

		echo $e->getMessage() . '> ' . $e->getFile() . ' (' . $e->getLine() . ')<br>';
	}

	echo '<pre>', $e->xdebug_message, '</pre>';

	// $error = $di['core.error'];
	// $errorsetError($e);
	// echo $errorhandle();
}

// That's it! Send all stuff to the browser.
ob_end_flush();
