<?php
namespace Core\Page\Head\Javascript;

use Core\Cfg\Cfg;
use Core\Router\Router;
use Core\Page\PageException;

/**
 * Javascript.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Javascript
{

    /**
     *
     * @var array
     */
    private $core_js = [
        'top' => [],
        'below' => []
    ];

    /**
     *
     * @var array
     */
    private $app_js = [
        'top' => [],
        'below' => []
    ];

    /**
     *
     * @var array
     */
    private $files_used = [];

    /**
     *
     * @var int
     */
    private $filecounter = 0;

    /**
     *
     * @var string
     */
    private $mode = 'apps';

    /**
     *
     * @var string
     */
    private $js_url = '';

    /**
     *
     * @var string
     */
    private $js_dir = '';

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
     * @param Cfg $cfg
     * @param Router $router
     */
    public function __construct(Cfg $cfg, Router $router)
    {
        $this->cfg = $cfg;
        $this->router = $router;

        $this->js_url = $cfg->data['Core']['url.js'];
        $this->js_dir = $cfg->data['Core']['dir.js'];
    }

    /**
     * Init method
     */
    public function init()
    {
        $this->mode = 'core';

        // Theme name
        $theme = $this->cfg->data['Core']['style.theme.name'];

        // jQuery version
        $version = $this->cfg->data['Core']['js.jquery.version'];

        // Add local jQeury file or the one from CDN
        $file = '/' . $theme . '/js/jquery-' . $version . '.js';

        if ($this->cfg->data['Core']['js.jquery.local'] && file_exists(THEMESDIR . $file)) {
            $this->file(THEMESURL . $file);
        }
        else {
            $this->file('https://code.jquery.com/jquery-' . $version . '.min.js', false, true);
        }

        // Bootstrap Version
        $version = $this->cfg->data['Core']['style.bootstrap.version'];

        // Add Bootstrap javascript from local or cdn
        $file = '/' . $theme . '/js/bootstrap-' . $version . '.js';

        if ($this->cfg->data['Core']['style.bootstrap.local'] && file_exists(THEMESDIR . $file)) {
            $this->file(THEMESURL . $file);
        }
        else {
            $this->file('https://maxcdn.bootstrapcdn.com/bootstrap/' . $version . '/js/bootstrap.min.js', false, true);
        }

        // Add plugins file
        $this->file($this->js_url . '/plugins.js');

        // Add global fadeout time var set in config
        $this->variable('fadeout_time', $this->cfg->data['Core']['js.style.fadeout_time']);

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

            /* @var $ajax \Core\Ajax\Ajax */
            $ajax = $this->di->get('core.ajax');

            switch ($js->getType()) {
                case 'file':
                    $cmd = $ajax->createCommand('Act\LoadScript');
                    $cmd->loadScript($js->getScript());
                    $ajax->add($cmd);
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
            Throw new PageException('Wrong scriptarea.');
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
     * @throws PageException
     *
     * @return JavascriptObject
     */
    public function &file($url, $defer = false, $is_external = false)
    {
        // Do not add files already added
        if (in_array($url, $this->files_used)) {
            Throw new PageException(sprintf('Url "%s" is already set as included js file.', $url));
        }

        $dt = debug_backtrace();

        $this->files_used[$this->filecounter . '-' . $dt[1]['function']] = $url;
        $this->filecounter ++;

        $script = new JavascriptObject();

        $script->setType('file');
        $script->setScript($url);
        $script->setIsExternal($is_external);
        $script->setDefer($defer);

        $this->add($script);

        return $script;
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

        $this->add($script);

        return $script;
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

        $this->add($script);

        return $script;
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

        $this->add($script);

        return $script;
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

        $this->add($script);

        return $script;
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
            return $script_stack;
        }

        // Init js storages
        $files = $blocks = $inline = $scripts = $ready = $vars = $local_files = [];

        /* @var $script JavascriptObject */
        foreach ($script_stack as $key => $script) {

            switch ($script->getType()) {

                // File to lin
                case 'file':
                    $filename = $script->getScript();

                    if (strpos($filename, BASEURL) !== false && $script->getCombine()) {
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

        // Check cache
        if ($local_files || $inline || $vars || $scripts || $ready) {

            $key = 'combined_' . $area;
            $extension = 'js';

            $filename = $this->cfg->data['Core']['dir.cache'] . '/' . $key . '.' . $extension;

            // End of combined file TTL reached?
            if (!file_exists($filename) || filemtime($filename) + $this->cfg->data['Core']['cache.ttl_' . $extension] < time()) {

                // Strat combining all parts
                $combined = '';

                if ($local_files) {
                    foreach ($local_files as $js_file) {
                        $combined .= file_get_contents($js_file);
                    }
                }

                if ($inline) {
                    $combined .= implode('', $inline);
                }

                if ($vars) {
                    foreach ($vars as $name => $val) {
                        $combined .= 'var ' . $name . ' = ' . (is_string($val) ? '"' . $val . '"' : $val) . ';';
                    }
                }

                if ($ready) {
                    $combined .= '$(document).ready(function() {' . implode('', $ready) . '});';
                }

                if ($blocks) {
                    $combined .= implode($blocks);
                }

                $combined = \JSMin::minify($combined);

                // Make sure we write files only into to cache folder!
                if (strpos($filename, $this->cfg->data['Core']['dir.cache']) === false) {
                    Throw new PageException('Writing files outside the cachefolder from Javascript::getFiles() is not permitted.');
                }

                file_put_contents($filename, $combined);
            }

            $files[] = $this->cfg->data['Core']['url.cache'] . '/' . $key . '.' . $extension;
        }

        return $files;
    }
}
