<?php
namespace Core\Lib\Content;

use Core\Lib\Cfg;
use Core\Lib\Http\Router;

/**
 * Class for managing and creating of javascript objects
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
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

    private $mode = 'apps';

    private $js_url = '';

    /**
     * Constructor
     *
     * @param Cfg $cfg
     * @param Router $router
     */
    public function __construct(Cfg $cfg, Router $router)
    {
        $this->cfg = $cfg;
        $this->router = $router;

        $this->js_url = $cfg->get('Core', 'url_js');
    }

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
        } else {
            $this->app_js[$area][] = $js;
        }

        return $js;
    }

    /**
     * Returns the js object stack
     *
     * @param string $area
     *
     * @throws \InvalidArgumentException
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
            Throw new \InvalidArgumentException('Wrong scriptarea.');
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
     * @return JavascriptObject
     */
    public function &file($url, $defer = false, $is_external = false)
    {
        // Do not add files already added
        if (in_array($url, $this->files_used)) {
            Throw new \RuntimeException(sprintf('Url "%s" is already set as included js file.', $url));
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
     * @return string
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
}
