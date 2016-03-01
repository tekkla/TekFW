<?php

/**
 * Core.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Core
{

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
        define('COREFW', 1);

        $this->defineDirs();
        $this->loadSettings();
        $this->loadErrorhandler();

        ob_start();

        $this->defineUrls();
        $this->registerClassloader();
        $this->initDI();
        $this->initConfig();
        $this->initSession();
        $this->initCoreApp();
        $this->initMailer();
        $this->initSecurity();

        try {

            $this->autodiscoverApps();

            $this->router = $this->di->get('core.router');
            $this->http = $this->di->get('core.http');

            // Get result
            $result = $this->dispatch();
        }
        catch (\Exception $e) {

            // Get result from exception handler
            $result = $this->di->get('core.error')->handleException($e, true);
        }
        finally {

            // Send mails
            $this->mailer->send();

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

            echo $result;

            ob_end_flush();
        }
    }

    private function defineDirs()
    {
        // Define path to the framweork core.
        define('COREDIR', BASEDIR . '/Core');

        // Define path to the framweork core.
        define('LOGDIR', BASEDIR . '/logs');

        // Define path to applications.
        define('APPSDIR', BASEDIR . '/Apps');

        // Define path to themes
        define('THEMESDIR', BASEDIR . '/Themes');

        // Define path to cache
        define('CACHEDIR', BASEDIR . '/Cache');

        // Define path to secured apps
        define('APPSSECDIR', COREDIR . '/AppsSec');

        // Define path to the framweork core.
        define('LIBDIR', COREDIR . '/Lib');
    }

    private function loadSettings()
    {
        // Check for settings file
        if (! file_exists(BASEDIR . '/Settings.php')) {
            die('Settings file could not be loaded.');
        }

        // Load basic config from Settings.php
        $this->settings = include (BASEDIR . '/Settings.php');
    }

    private function loadErrorhandler()
    {
        // Include error handler
        require_once (LIBDIR . '/Errors/Error.php');
    }

    private function defineUrls()
    {
        // Define url to Core
        define('BASEURL', $this->settings['site.general.url']);

        // Define url of themes
        define('THEMESURL', $this->settings['site.general.url'] . '/Themes');
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
        $check = [
            'db.driver',
            'db.host',
            'db.port',
            'db.name',
            'db.user',
            'db.pass',
            'db.persistent',
            'db.errmode',
            'db.init_command',
            'db.prefix'
        ];

        if (! in_array($check, $this->settings)) {
            die('Missing db config. Check settings file.');
        }

        // == DB ===========================================================
        $this->di->mapValue('db.default.driver', $this->settings['db.driver']);
        $this->di->mapValue('db.default.host', $this->settings['db.host']);
        $this->di->mapValue('db.default.port', $this->settings['db.port']);
        $this->di->mapValue('db.default.name', $this->settings['db.name']);
        $this->di->mapValue('db.default.user', $this->settings['db.user']);
        $this->di->mapValue('db.default.pass', $this->settings['db.pass']);
        $this->di->mapValue('db.default.options', [
            \PDO::ATTR_PERSISTENT => $this->settings['db.persistent'],
            \PDO::ATTR_ERRMODE => $this->settings['db.errmode'],
            \PDO::MYSQL_ATTR_INIT_COMMAND => $this->settings['db.init_command'],
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => 1
        ]);
        $this->di->mapValue('db.default.prefix', $this->settings['db.prefix']);
        $this->di->mapService('db.default.conn', '\Core\Lib\Data\Connectors\Db\Connection', [
            'db.default.name',
            'db.default.driver',
            'db.default.host',
            'db.default.port',
            'db.default.user',
            'db.default.pass',
            'db.default.options'
        ]);
        $this->di->mapFactory('db.default', '\Core\Lib\Data\Connectors\Db\Db', [
            'db.default.conn',
            'db.default.prefix'
        ]);

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
        $this->di->mapService('core.http.post', '\Core\Lib\Http\Post', [
            'core.router',
            'core.security.token'
        ]);
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
            'core.security.login'
        ]);
        $this->di->mapFactory('core.security.users', '\Core\Lib\Security\Users', [
            'db.default',
            'core.cfg',
            'core.security.token',
            'core.log'
        ]);
        $this->di->mapFactory('core.security.user', '\Core\Lib\Security\User', [
            'db.default',
        ]);
        $this->di->mapService('core.security.user.current', '\Core\Lib\Security\User', [
            'db.default',
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

        // == AMVC =========================================================
        $this->di->mapService('core.amvc.creator', '\Core\Lib\Amvc\Creator', 'core.cfg');
        $this->di->mapFactory('core.amvc.app', '\Core\Lib\Amvc\App');

        // == CACHE ========================================================
        $this->di->mapService('core.cache', '\Core\Lib\Cache\Cache', [
            'core.cfg'
        ]);
        $this->di->mapFactory('core.cache.object', '\Core\Lib\Cache\CacheObject');

        // == IO ===========================================================
        $this->di->mapService('core.io', '\Core\Lib\IO\IO', [
            'core.io.download',
            'core.io.files'
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
            'core.log'
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
            'core.cfg',
            'core.cache'
        ]);
        $this->di->mapFactory('core.page.head.js', '\Core\Lib\Page\Head\Javascript\Javascript', [
            'core.cfg',
            'core.router',
            'core.cache'
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

    private function initConfig()
    {
        /* @var $cfg \Core\Lib\Cfg\Cfg */
        $this->cfg = $this->di->get('core.cfg');

        // Init config with config from Settings.php
        $this->cfg->init($this->settings);

        // Load additional configs from DB
        $this->cfg->load();

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

    private function initCoreApp()
    {
        /* @var $creator \Core\Lib\Amvc\Creator */
        $this->creator = $this->di->get('core.amvc.creator');
        $this->creator->getAppInstance('Core');
    }

    private function initMailer()
    {
        $this->mailer = $this->di->get('core.mailer');
        $this->mailer->init();
    }

    private function initSecurity()
    {
        /* @var $security \Core\Lib\Security\Security */
        $security = $this->di->get('core.security');

        // Check ban!
        $security->users->checkBan();

        // Try to autologin
        $security->login->doAutoLogin();

        // Load User
        if ($security->login->loggedIn()) {
            $security->user->load($_SESSION['id_user']);
        }

        // Create session token
        $security->token->generateRandomSessionToken();
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
        // Match request against stored routes
        $this->router->match();

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
        $this->controller = $app->getController($controller_name);

        $action_name = $this->router->getAction();

        if (empty($action_name) && $this->cfg->exists('Core', 'execute.default.action')) {
            $action_name = $this->cfg->data['Core']['execute.default.action'];
        }

        // Which controller action has to be run?
        $this->action = $action_name;

        if ($this->router->isAjax()) {

            $this->router->setFormat('json');

            // send JSON header
            $this->http->header->contentType('application/json', 'utf-8');

            // Send cache preventing headers
            $this->http->header->noCache();

            // Result will be processed as ajax command list
            $this->controller->ajax($this->action, $this->router->getParam());

            // Run ajax processor
            $result = $this->di->get('core.ajax')->process();
        }
        else {
            $result = $this->controller->run($this->action, $this->router->getParam());
        }

        return $result;
    }
}