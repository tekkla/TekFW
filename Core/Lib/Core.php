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

        try {

            // start output buffering
            ob_start();

            $this->defineUrls();
            $this->registerClassloader();
            $this->initDI();
            $this->initConfig();
            $this->initSession();
            $this->initCoreApp();
            $this->initMailer();
            $this->initSecurity();
            $this->autodiscoverApps();
            $this->createPage();
        }
        catch (\Exception $e) {
            echo $this->di->get('core.error')->handleException($e, true);
        }
        finally {
            // That's it! Send all stuff to the browser.
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
        $this->di = \Core\Lib\DI::getInstance($this->settings);
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
        $security = $this->di->get('core.sec.security');
        $security->init();
    }

    private function autodiscoverApps()
    {
        $this->creator->autodiscover([
            APPSSECDIR,
            APPSDIR
        ]);
    }

    private function createPage()
    {
        $page = $this->di->get('core.page');
        $page->create();
    }
}