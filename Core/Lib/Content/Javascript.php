<?php
namespace Core\Lib\Content;

use Core\Lib\Cfg;
use Core\Lib\Http\Router;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;
use Core\Lib\Errors\Exceptions\RuntimeException;
use Core\Lib\Cache\Cache;

/**
 * Javascript.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Javascript
{

    /**
     * Stack of core javascript objects
     *
     * @var array
     */
    private $core_js = [
        'top' => [],
        'below' => []
    ];

    /**
     * Stack of app javascript objects
     *
     * @var array
     */
    private $app_js = [
        'top' => [],
        'below' => []
    ];

    /**
     * For double file use prevention
     *
     * @var array
     */
    private $files_used = [];

    /**
     * Internal filecounter
     *
     * @var int
     */
    private $filecounter = 0;

    private $mode = 'apps';

    private $js_url = '';

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     *
     * @var Router
     */
    private $router;

    /**
     *
     * @var Cache
     */
    private $cache;

    /**
     * Constructor
     *
     * @param Cfg $cfg
     * @param Router $router
     * @param Cache $cache
     */
    public function __construct(Cfg $cfg, Router $router, Cache $cache)
    {
        $this->cfg = $cfg;
        $this->router = $router;
        $this->cache = $cache;

        $this->js_url = $cfg->get('Core', 'url_js');
    }

    /**
     * Init method
     */
    public function init()
    {
        $this->mode = 'core';

        // Add jquery cdn
        $this->file('https://code.jquery.com/jquery-' . $this->cfg->get('Core', 'jquery_version') . '.min.js', false, true);

        // Add Bootstrap javascript from cdn
        $this->file('https://maxcdn.bootstrapcdn.com/bootstrap/' . $this->cfg->get('Core', 'bootstrap_version') . '/js/bootstrap.min.js', false, true);

        // Add plugins file
        $this->file($this->js_url . '/plugins.js');

        // Add global fadeout time var set in config
        $this->variable('fadeout_time', $this->cfg->get('Core', 'js_fadeout_time'));

        // Add framework js
        $this->file($this->js_url . '/framework.js');

        $this->mode = 'apps';
    }

    /**
     * Adds an javascript objectto the content.
     *
     * @param Javascript $script
     *
     * @return JavascriptObject
     */
    public function &add(JavascriptObject $js)
    {
        if ($this->router->isAjax()) {

            /* @var $ajax \Core\Lib\Ajax\Ajax */
            $ajax = $this->di->get('core.ajax');

            switch ($js->getType()) {
                case 'file':
                    $ajax->fnLoadScript($js->getScript());
                    break;
            }

            $return = null;

            return $return;
        }

        $area = $js->getDefer() ? 'below' : 'top';

        if ($this->mode == 'core') {
            $this->core_js[$area][] = $js;
        }
        else {
            $this->app_js[$area][] = $js;
        }

        return $js;
    }

    /**
     * Returns the js object stack
     *
     * @param string $area
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public function getScriptObjects($area)
    {
        $areas = [
            'top',
            'below'
        ];

        if (! in_array($area, $areas)) {
            Throw new InvalidArgumentException('Wrong scriptarea.');
        }

        return array_merge($this->core_js[$area], $this->app_js[$area]);
    }

    /**
     * Adds a file javascript object to the output queue
     *
     * @param string $url
     * @param bool $defer
     * @param bool $is_external
     *
     * @throws RuntimeException
     *
     * @return JavascriptObject
     */
    public function &file($url, $defer = false, $is_external = false)
    {
        // Do not add files already added
        if (in_array($url, $this->files_used)) {
            Throw new RuntimeException(sprintf('Url "%s" is already set as included js file.', $url));
        }

        $dt = debug_backtrace();

        $this->files_used[$this->filecounter . '-' . $dt[1]['function']] = $url;
        $this->filecounter ++;

        $script = new JavascriptObject();

        $script->setType('file');
        $script->setScript($url);
        $script->setIsExternal($is_external);
        $script->setDefer($defer);

        return $this->add($script);
    }

    /**
     * Adds an script javascript object to the output queue
     *
     * @param string $script
     * @param bool $defer
     *
     * @return JavascriptObject
     */
    public function &script($script, $defer = false)
    {
        $script = new JavascriptObject();

        $script->setType('script');
        $script->setScript($script);
        $script->setDefer($defer);

        return $this->add($script);
    }

    /**
     * Creats a ready javascript object
     *
     * @param string $script
     * @param bool $defer
     *
     * @return Javascript
     */
    public function &ready($script, $defer = false)
    {
        $script = new JavascriptObject();

        $script->setType('ready');
        $script->setScript($script);
        $script->setDefer($defer);

        return $this->add($script);
    }

    /**
     * Blocks with complete code.
     * Use this for conditional scripts!
     *
     * @param string $script
     * @param bool $defer
     *
     * @return JavascriptObject
     */
    public function &block($script, $defer = false)
    {
        $script = new JavascriptObject();

        $script->setType('block');
        $script->setScript($script);
        $script->setDefer($defer);

        return $this->add($script);
    }

    /**
     * Creates and returns a var javascript object
     *
     * @param string $name
     * @param mixed $value
     * @param bool $is_string
     *
     * @return JavascriptObject
     */
    public function &variable($name, $value, $is_string = false)
    {
        if ($is_string == true) {
            $value = '"' . $value . '"';
        }

        $script = new JavascriptObject();

        $script->setType('var');
        $script->setScript([
            $name,
            $value
        ]);

        return $this->add($script);
    }

    /**
     * Returns an file script block for the BS js lib
     *
     * @param string $version
     * @param bool $from_cdn
     *
     * @return string
     *
     * @todo Make it an Script object?
     */
    public function &bootstrap($version, $defer = false)
    {
        $url = $this->js_url . '/bootstrap-' . $version . '.min.js';

        if (str_replace(BASEURL, BASEDIR, $url)) {
            return $this->file($url);
        }
    }

    /**
     * Returns all registered javacript objects.
     *
     * @return array
     */
    public function getStack()
    {
        return array_merge($this->core_js, $this->app_js);
    }

    /**
     * Returns filelist of js files for the requested area.
     *
     * Also combines all local files and inline scripts into one cached combined_{$area}.js file.
     *
     * @param string $area
     *
     * @return array
     */
    public function getFiles($area)
    {
        // Get scripts of this area
        $script_stack = $this->getScriptObjects($area);

        if (empty($script_stack)) {
            return false;
        }

        // Init js storages
        $files = $blocks = $inline = $scripts = $ready = $vars = [];

        // Include JSMin lib
        // if ($this->cfg->get('Core', 'js_minify')) {
        // require_once ($this->cfg->get('Core', 'dir_tools') . '/min/lib/JSMin.php');
        // }

        /* @var $script Javascript */
        foreach ($script_stack as $key => $script) {

            switch ($script->getType()) {

                // File to lin
                case 'file':
                    $filename = $script->getScript();

                    if (strpos($filename, BASEURL) !== false) {
                        $local_files[] = str_replace(BASEURL, BASEDIR, $filename);
                    }
                    else {
                        $files[] = $filename;
                    }
                    break;

                // Script to create
                case 'script':
                    $inline[] = $script->getScript();
                    break;

                // Dedicated block to embaed
                case 'block':
                    $blocks[] = $script->getScript();
                    break;

                // A variable to publish to global space
                case 'var':
                    $var = $script->getScript();
                    $vars[$var[0]] = $var[1];
                    break;

                // Script to add to $.ready()
                case 'ready':
                    $ready[] = $script->getScript();
                    break;
            }

            // Remove worked script object
            unset($script_stack[$key]);
        }

        $combined = '';

        // Check cache
        if ($local_files) {

            // Yes! Now check cache
            $cache_object = $this->cache->createCacheObject();

            $key = 'combined_' . $area;
            $extension = 'js';

            $cache_object->setKey($key);
            $cache_object->setExtension($extension);
            $cache_object->setTTL($this->cfg->get('Core', 'cache_ttl_js'));

            if ($this->cache->checkExpired($cache_object)) {

                // Create combined output
                foreach ($local_files as $filename) {
                    $combined .= file_get_contents($filename);
                }

                if ($inline) {
                    $combined .= implode('', $inline);
                }

                if ($vars || $scripts || $ready) {

                    // Create script html object
                    foreach ($vars as $name => $val) {
                        $combined .= 'var ' . $name . ' = ' . (is_string($val) ? '"' . $val . '"' : $val) . ';';
                    }

                    // Create $(document).ready()
                    if ($ready) {
                        $combined .= '$(document).ready(function() {' . implode('', $ready) . '});';
                    }

                    // Add complete blocks
                    $combined .= implode($blocks);
                }

                // Minify combined css code
                $combined = \JSMin::minify($combined);

                $cache_object->setContent($combined);

                $this->cache->put($cache_object);
            }

            $files[] = $this->cfg->get('Core', 'url_cache') . '/' . $key . '.' . $extension;
        }

        return $files;
    }
}
