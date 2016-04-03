<?php
// Include StringTrait file
require_once (BASEDIR . '/Core/Lib/Traits/StringTrait.php');

use Core\Lib\Traits\StringTrait;

/**
 * Core.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */

// Define framwork constant
define('COREFW', 1);

// Do not show errors!
ini_set('display_errors', 0);

// Define path constants to the common framwork dirs
define('COREDIR', BASEDIR . '/Core');
define('LOGDIR', BASEDIR . '/logs');
define('APPSDIR', BASEDIR . '/Apps');
define('THEMESDIR', BASEDIR . '/Themes');
define('CACHEDIR', BASEDIR . '/Cache');
define('APPSSECDIR', COREDIR . '/AppsSec');
define('LIBDIR', COREDIR . '/Lib');

final class Core
{

    use StringTrait;

    /**
     *
     * @var array
     */
    private $settings = [];

    /**
     *
     * @var \Core\Lib\DI
     */
    private $di;

    /**
     *
     * @var \Core\Lib\Cfg\Cfg
     */
    private $cfg;

    /**
     *
     * @var \Core\Lib\Router\Router
     */
    private $router;

    /**
     *
     * @var \Core\Lib\Security\Security
     */
    private $security;

    /**
     *
     * @var \Core\Lib\Http\Http
     */
    private $http;

    /**
     *
     * @var \Core\Lib\Mailer\Mailer
     */
    private $mailer;

    /**
     *
     * @var \Core\Lib\Amvc\Creator
     */
    private $creator;

    public function run()
    {
        try {

            // Load settingsfile
            $this->loadSettings();

            // From here starts output buffering
            ob_start();

            // Registe PSR classloader
            $this->registerClassloader();

            // Init DI service
            $this->initDI();

            // Inits confix with data from db and adds basic dirs and urls to config
            $this->initConfig();

            // Init Session service
            $this->initSession();

            // Init the essential secured Core app
            $this->initCoreApp();

            // Init security system
            $this->initSecurity();

            try {

                // Autodicover all installed apps
                $this->autodiscoverApps();

                // Create references to Router and Http service
                $this->router = $this->di->get('core.router');
                $this->http = $this->di->get('core.http');

                // Get result
                $result = $this->dispatch();
            }
            catch (Throwable $t) {

                // Get result from exception handler
                $result = $this->di->get('core.error')->handleException($t, true);
            }
            finally {

                // Send mails
                $mailer = $this->di->get('core.mailer');
                $mailer->send();

                // Send cookies
                $this->http->cookies->send();

                // Handle output format
                $format = $this->router->getFormat();

                // Send headers so far
                $this->http->header->send();

                switch ($format) {

                    case 'file':
                        /* @var $download \Core\Lib\IO\Download */
                        $download = $this->di->get('core.io.download');
                        $download->sendFile($result);

                        break;

                    case 'html':

                        $this->http->header->contentType('text/html', 'utf-8');

                        /* @var $page \Core\Lib\Page\Page */
                        $page = $this->di->get('core.page');
                        $page->setContent($result);
                        $result = $page->render();

                        break;
                }

                // Send headers so far
                $this->http->header->send();

                if (! empty($result)) {
                    echo $result;
                }

                ob_end_flush();
            }
        }
        catch (Throwable $t) {

            echo '
            <h1>Error</h1>
            <p><strong>' . $t->getMessage() . '</strong></p>
            <p>in ', $t->getFile() . ' (Line: ', $t->getLine(), ')</p>';

            error_log($t->getMessage() . ' >> ' . $t->getFile() . ':' . $t->getLine());
        }
    }

    private function loadSettings()
    {
        // Check for settings file
        if (! file_exists(BASEDIR . '/Settings.php') || ! is_readable(BASEDIR . '/Settings.php')) {
            error_log('Settings file could not be loaded.');
            die('An error occured. Sorry for that! :(');
        }

        // Load basic config from Settings.php
        $this->settings = include (BASEDIR . '/Settings.php');
    }

    private function registerClassloader()
    {
        // Register core classloader
        require_once (LIBDIR . '/SplClassLoader.php');

        // Classloader to register
        $register = [
            'Core' => BASEDIR,
            'Apps' => BASEDIR,
            'Themes' => BASEDIR
        ];

        // Register classloader
        foreach ($register as $key => $path) {
            $loader = new \SplClassLoader($key, $path);
            $loader->register();
        }

        // Register composer classloader
        require_once (BASEDIR . '/vendor/autoload.php');
    }

    private function initDI()
    {

        // Create core di container
        $this->di = \Core\Lib\DI::getInstance();

        $to_map = [
            [
                'value',
                'core.di',
                $this->di
            ]
        ];

        foreach ($to_map as $map) {

            switch ($map[0]) {

                case 'value':
                    $this->di->mapValue($map[1], $map[2]);
                    break;

                case 'factory':
                    $this->di->mapFactory($map[1], $map[2], isset($map[3]) ? $map[3] : null);
                    break;

                case 'service':
                    $this->di->mapService($map[1], $map[2], isset($map[3]) ? $map[3] : null);
                    break;

                default:
                    Throw new InvalidArgumentException(sprintf('Mappingtype "%s" is not supported by DI container', $map[0]));
            }
        }

        // == DB ===========================================================

        $defaults = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'user' => 'root',
            'password' => '',
            'prefix' => '',
            'options' => [
                \PDO::ATTR_PERSISTENT => false,
                \PDO::ATTR_ERRMODE => 2,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => 1,
                \PDO::ATTR_EMULATE_PREPARES => false
            ]
        ];

        if (empty($this->settings['db'])) {
            error_log('No DB data set in Settings.php');
            Throw new Exception('Error on DB access');
        }

        if (empty($this->settings['db']['default'])) {
            error_log('No DB "default" data set in Settings.php');
            Throw new Exception('Error on DB access');
        }

        foreach ($this->settings['db'] as $name => &$settings) {

            $prefix = 'db.' . $name;

            // Check for databasename
            if (empty($settings['name'])) {
                Throw new Exception(sprintf('Name key of DB setting "%s" is missing.'), $name);
            }

            // Set map default db name
            $this->di->mapValue($prefix . '.name', $settings['name']);

            // Check for DB defaults and map values
            foreach ($defaults as $key => $default) {

                // Append default options to settings
                if ($key == 'options') {

                    if (empty($settings['options'])) {
                        $settings['options'] = [];
                    }

                    foreach ($defaults['options'] as $option => $value) {

                        if (array_key_exists($option, $settings['options'])) {
                            continue;
                        }

                        $settings[$key][$option] = $value;
                    }
                }

                if (empty($settings[$key])) {
                    $settings[$key] = $default;
                }
            }

            $this->di->mapValue($prefix . '.settings', $settings);

            // Default connection
            $this->di->mapService($prefix . '.conn', '\Core\Lib\Data\Connectors\Db\Connection', $prefix . '.settings');

            // Default db connector
            $this->di->mapFactory($prefix, '\Core\Lib\Data\Connectors\Db\Db', [
                $prefix . '.conn',
                $settings['prefix']
            ]);
        }

        // == CONFIG =======================================================
        $this->di->mapService('core.cfg', '\Core\Lib\Cfg\Cfg', 'db.default');

        // == ROUTER =======================================================
        $this->di->mapService('core.router', '\Core\Lib\Router\Router');

        // == HTTP =========================================================
        $this->di->mapService('core.http.session', '\Core\Lib\Http\Session', 'db.default');
        $this->di->mapService('core.http', '\Core\Lib\Http\Http', [
            'core.http.cookie',
            'core.http.post',
            'core.http.header'
        ]);
        $this->di->mapService('core.http.cookie', '\Core\Lib\Http\Cookie\Cookies');
        $this->di->mapService('core.http.post', '\Core\Lib\Http\Post');
        $this->di->mapService('core.http.header', '\Core\Lib\Http\Header');

        // == UTILITIES ====================================================
        $this->di->mapFactory('core.util.timer', '\Core\Lib\Utilities\Timer');
        $this->di->mapFactory('core.util.time', '\Core\Lib\Utilities\Time');
        $this->di->mapFactory('core.util.shorturl', '\Core\Lib\Utilities\ShortenURL');
        $this->di->mapFactory('core.util.date', '\Core\Lib\Utilities\Date');
        $this->di->mapFactory('core.util.debug', '\Core\Lib\Utilities\Debug');
        $this->di->mapService('core.util.fire', '\FB');

        // == SECURITY =====================================================
        $this->di->mapService('core.security', '\Core\Lib\Security\Security', [
            'core.security.user.current',
            'core.security.users',
            'core.security.group',
            'core.security.token',
            'core.security.login',
            'core.security.permission'
        ]);
        $this->di->mapFactory('core.security.users', '\Core\Lib\Security\Users', [
            'db.default',
            'core.cfg',
            'core.security.token',
            'core.log'
        ]);
        $this->di->mapFactory('core.security.user', '\Core\Lib\Security\User', [
            'db.default'
        ]);
        $this->di->mapService('core.security.user.current', '\Core\Lib\Security\User', [
            'db.default'
        ]);
        $this->di->mapService('core.security.group', '\Core\Lib\Security\Group', 'db.default');
        $this->di->mapService('core.security.token', '\Core\Lib\Security\Token', [
            'db.default',
            'core.log'
        ]);
        $this->di->mapService('core.security.login', '\Core\Lib\Security\Login', [
            'db.default',
            'core.cfg',
            'core.http.cookie',
            'core.security.token',
            'core.log'
        ]);
        $this->di->mapService('core.security.permission', '\Core\Lib\Security\Permission');

        // == AMVC =========================================================
        $this->di->mapService('core.amvc.creator', '\Core\Lib\Amvc\Creator', 'core.cfg');
        $this->di->mapFactory('core.amvc.app', '\Core\Lib\Amvc\App');

        // == IO ===========================================================
        $this->di->mapService('core.io', '\Core\Lib\IO\IO', [
            'core.io.files',
            'core.io.download'
        ]);
        $this->di->mapService('core.io.download', '\Core\Lib\IO\Download', [
            'core.http.header',
            'core.io.files'
        ]);
        $this->di->mapService('core.io.files', '\Core\Lib\IO\Files', [
            'core.log',
            'core.cfg'
        ]);

        // == LOGGING========================================================
        $this->di->mapService('core.log', '\Core\Lib\Log\Log', [
            'db.default',
            'core.cfg'
        ]);

        // == MAILER =======================================================
        $this->di->mapService('core.mailer', '\Core\Lib\Mailer\Mailer', [
            'core.cfg',
            'core.log',
            'db.default'
        ]);

        // == DATA ==========================================================
        $this->di->mapService('core.data.validator', '\Core\Lib\Data\Validator\Validator');

        // == LANGUAGE ======================================================
        $this->di->mapService('core.language', '\Core\Lib\Language\Language');

        // == CONTENT =======================================================
        $this->di->mapService('core.page', '\Core\Lib\Page\Page', [
            'core.router',
            'core.cfg',
            'core.amvc.creator',
            'core.html.factory',
            'core.page.body.nav',
            'core.page.head.css',
            'core.page.head.js',
            'core.page.body.message'
        ]);
        $this->di->mapFactory('core.page.head.css', '\Core\Lib\Page\Head\Css\Css', [
            'core.cfg'
        ]);
        $this->di->mapFactory('core.page.head.js', '\Core\Lib\Page\Head\Javascript\Javascript', [
            'core.cfg',
            'core.router'
        ]);
        $this->di->mapService('core.page.body.message', '\Core\Lib\Page\Body\Message\Message');
        $this->di->mapService('core.page.body.nav', '\Core\Lib\Page\Body\Menu\Menu');
        $this->di->mapFactory('core.page.body.menu', '\Core\Lib\Page\Body\Menu\Menu');

        // == HTML ==========================================================
        $this->di->mapService('core.html.factory', '\Core\Lib\Html\HtmlFactory');

        // == AJAX ==========================================================
        $this->di->mapService('core.ajax', '\Core\Lib\Ajax\Ajax', [
            'core.page.body.message',
            'core.io.files',
            'core.cfg'
        ]);

        // == ERROR =========================================================
        $this->di->mapService('core.error', '\Core\Lib\Errors\ExceptionHandler', [
            'core.router',
            'core.security.user.current',
            'core.ajax',
            'core.page.body.message',
            'db.default',
            'core.cfg'
        ]);
    }

    /**
     * Inits Cfg service and loads config from db
     *
     * @return void
     */
    private function initConfig()
    {
        /* @var $cfg \Core\Lib\Cfg\Cfg */
        $this->cfg = $this->di->get('core.cfg');

        // Load additional configs from DB
        $this->cfg->load();

        // Set baseurl to config
        if (empty($this->settings['protcol'])) {
            $this->settings['protocol'] = 'http';
        }

        if (empty($this->settings['baseurl'])) {
            Throw new Exception('Baseurl not set in Settings.php');
        }

        // Define some basic url constants
        define('BASEURL', $this->settings['protocol'] . '://' . $this->settings['baseurl']);
        define('THEMESURL', BASEURL . '/Themes');

        $this->cfg->data['Core']['site.protocol'] = $this->settings['protocol'];
        $this->cfg->data['Core']['site.baseurl'] = $this->settings['baseurl'];

        // Check and set basic cookiename to config
        if (empty($this->settings['cookie'])) {
            Throw new Exception('Cookiename not set in Settings.php');
        }

        $this->cfg->data['Core']['cookie.name'] = $this->settings['cookie'];

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

        $this->cfg->addPaths('Core', $dirs);

        // Add urls to config
        $urls = [
            'apps' => '/Apps',
            'appssec' => '/Core/AppsSec',
            'css' => '/Core/Css',
            'js' => '/Core/Js',
            'tools' => '/Core/Tools',
            'cache' => '/Cache'
        ];

        $this->cfg->addUrls('Core', $urls);

    }

    private function initSession()
    {
        $session = $this->di->get('core.http.session');
        $session->init();
    }

    /**
     * Inits secured app Core
     *
     * The Core app is not really an app. It's more or less the logical and visual part of the framework
     * that puts all the pieces together and offers a frontend to manage parts of the site with all
     * the other possible apps.
     */
    private function initCoreApp()
    {
        /* @var $creator \Core\Lib\Amvc\Creator */
        $this->creator = $this->di->get('core.amvc.creator');
        $this->creator->getAppInstance('Core');
    }

    private function initSecurity()
    {
        /* @var $security \Core\Lib\Security\Security */
        $this->security = $this->di->get('core.security');

        // Check ban!
        $this->security->users->checkBan();

        // Try to autologin
        $this->security->login->doAutoLogin();

        // Load User
        if ($this->security->login->loggedIn()) {
            $this->security->user->load($_SESSION['id_user']);
        }

        // Create session token
        $this->security->token->generateRandomSessionToken();
    }

    private function autodiscoverApps()
    {
        $this->creator->autodiscover([
            APPSSECDIR,
            APPSDIR
        ]);
    }

    private function dispatch()
    {

        // Add mvc name pattern to router
        $this->router->addMatchTypes([
            'mvc' => '[A-Za-z0-9_]++'
        ]);

        // Match request against stored routes
        $this->router->match();

        // Handle possible posted data
        $this->managePost();

        $app_name = $this->router->getApp();

        // Handle default settings when we have a default
        if (empty($app_name) && $this->cfg->exists('Core', 'execute.default.app')) {
            $app_name = $this->cfg->data['Core']['execute.default.app'];
        }

        /* @var $app \Core\Lib\Amvc\App */
        $app = $this->creator->getAppInstance($app_name);

        if (method_exists($app, 'Access')) {

            // Call app wide access method. This is important for using forceLogin() security method.
            $app->Access();

            $app_check = $this->router->getApp();

            // Check for redirect from Access() method!!!
            if ($app_name != $app_check) {

                /* @var $app \Core\Lib\Amvc\App */
                $app = $this->creator->getAppInstance($app_check);
            }
        }

        /**
         * Each app can have it's own start procedure.
         * This procedure is used to init apps with more than the app creator does.
         * To use this feature the app needs a run() method in it's main file.
         */
        if (method_exists($app, 'Run')) {
            $app->Run();
        }

        $controller_name = $this->router->getController();

        if (empty($controller_name) && $this->cfg->exists('Core', 'execute.default.controller')) {
            $controller_name = $this->cfg->data['Core']['execute.default.controller'];
        }

        // Load controller object
        $controller = $app->getController($controller_name);

        if ($controller == false) {
            return $this->send404();
        }

        // Which controller action has to be run?
        $action = $this->router->getAction();

        if (empty($action) && $this->cfg->exists('Core', 'execute.default.action')) {
            $action = $this->cfg->data['Core']['execute.default.action'];
        }

        if (! method_exists($controller, $action)) {
            return $this->send404();
        }

        if ($this->router->isAjax()) {

            $this->router->setFormat('json');

            // send JSON header
            $this->http->header->contentType('application/json', 'utf-8');

            // Send cache preventing headers
            $this->http->header->noCache();

            // Result will be processed as ajax command list
            $controller->ajax($action, $this->router->getParam());

            // Run ajax processor
            $result = $this->di->get('core.ajax')->process();
        }
        else {
            $result = $controller->run($action, $this->router->getParam());
        }

        return $result;
    }

    private function managePost()
    {
        // Do only react on POST requests
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST)) {
            return;
        }

        // Validate posted token with session token
        if (! $this->security->token->validatePostToken()) {
            return;
        }

        // Trim data
        array_walk_recursive($_POST, function (&$data) {
            $data = trim($data);
        });
    }

    private function send404()
    {
        $this->http->header->sendHttpError(404);

        return 'Page not found';
    }
}