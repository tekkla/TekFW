<?php
namespace Core\Lib\Content;

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
     * Javascript output queue
     * 
     * @var array
     */
    private static $js = [];

    /**
     * Types can be "file", "script", "block", "ready" or "var".
     * 
     * @var string
     */
    private $type;

    /**
     * Header (false) or scripts (true) below body? This is the target for.
     * 
     * @var bool
     */
    private $defer = false;

    /**
     * The script to add.
     * This can be an url if its an file or a script block.
     * 
     * @var string
     */
    private $script;

    /**
     * Flag for external files.
     * External files wont be minified.
     * 
     * @var bool
     */
    private $is_external = false;

    /**
     * For double file use prevention
     * 
     * @var array
     */
    private static $files_used = [];

    /**
     * Internal filecounter
     * 
     * @var int
     */
    private static $filecounter = 0;

    private $cfg;

    private $request;

    public function __construct($cfg, $request)
    {
        $this->cfg = $cfg;
        $this->request = $request;
    }

    public function init()
    {
        // # Create the js scripts
        
        // Add jquery cdn
        $this->file('//code.jquery.com/jquery-' . $this->cfg->get('Core', 'jquery_version') . '.min.js');
        
        // Add Bootstrap javascript from cdn
        $this->file('//maxcdn.bootstrapcdn.com/bootstrap/' . $this->cfg->get('Core', 'bootstrap_version') . '/js/bootstrap.min.js');
        
        // Add plugins file
        $this->file($this->cfg->get('Core', 'url_js') . '/plugins.js');
        
        // Add global fadeout time var set in config
        $this->variable('fadeout_time', $this->cfg->get('Core', 'js_fadeout_time'));
        
        // Add framework js
        $this->file($this->cfg->get('Core', 'url_js') . '/framework.js');
    }

    /**
     * Adds an javascript objectto the content
     *
     * @param Javascript $script
     */
    public function &add()
    {
        self::$js[] = $this;
        
        return $this;
    }

    /**
     * Compiles the javascript objects and adds them to the $context javascripts
     */
    public function compile($defer = false)
    {
        // No need to run when nothing is to do
        if (empty(self::$js))
            return;
            
            // Init output
        $output = '';
        
        // Init js storages
        $files = $blocks = $inline = $scripts = $ready = $vars = [];
        
        // Include JSMin lib
        if ($this->cfg->get('Core', 'js_minify'))
            require_once ($this->cfg->get('Core', 'dir_tools') . '/min/lib/JSMin.php');
            
            /* @var $script Javascript */
        foreach (self::$js as $script) {
            if ($script->getDefer() != $defer)
                continue;
            
            switch ($script->getType()) {
                case 'file':
                    $files[] = ($script->getScript());
                    break;
                
                case 'script':
                    $inline[] = ($this->cfg->get('Core', 'js_minify') ? \JSMin::minify($script->getScript()) : $script->getScript());
                    break;
                
                case 'block':
                    $blocks[] = PHP_EOL . $script->getScript();
                    break;
                
                case 'var':
                    $var = $script->getScript();
                    $vars[$var[0]] = $var[1];
                    break;
                
                case 'ready':
                    $ready[] = $this->cfg->get('Core', 'js_minify') ? \JSMin::minify($script->getScript()) : $script->getScript();
                    break;
            }
        }
        
        // Are there files to minify?
        if ($this->cfg->get('Core', 'js_minify')) {
            
            if ($files)
                $to_minfiy = [];
            
            foreach ($files as $file) {
                if (strpos($file['filename'], BASEURL) !== false) {
                    $board_parts = parse_url(BASEURL);
                    $url_parts = parse_url($file['filename']);
                    
                    if ($board_parts['host'] != $url_parts['host'])
                        continue;
                        
                        // Store filename in minify list
                    if (! in_array('/' . $url_parts['path'], $files))
                        $to_minfiy[] = '/' . $url_parts['path'];
                }
            }
            
            // Are there files to combine?
            if ($to_minfiy) {
                // Insert filelink above or below?
                $side = $defer ? 'below' : 'above';
                
                // Store files to minify in session
                $_SESSION['js_to_min'] = $to_minfiy;
                
                // Add link to combined js file
                $files = array(
                    $this->cfg->get('Core', 'url_tools') . '/min/g=js-' . $side
                );
            }
        }
        
        // Create compiled output
        ob_start();
        
        if ($vars || $scripts || $ready) {
            echo PHP_EOL . PHP_TAB . '<!-- ' . ($defer ? 'LOWER' : 'UPPER') . ' JAVASCRIPTS -->';
            
            // Create script commands
            echo PHP_EOL . PHP_TAB . '<script>';
            
            foreach ($vars as $name => $val)
                echo PHP_EOL . PHP_TAB . 'var ' . $name . ' = ' . (is_string($val) ? '"' . $val . '"' : $val) . ';';
                
                // Create $(document).ready()
            if ($ready) {
                $script = '$(document).ready(function() {' . PHP_EOL;
                $script .= implode(PHP_EOL, $ready) . PHP_EOL;
                $script .= '});';
                
                echo ($this->cfg->get('Core', 'js_minify') ? \JSMin::minify($script) : $script);
            }
            
            echo '
	</script>';
        }
        
        // Add complete blocks
        echo implode(PHP_EOL, $blocks);
        
        // Create files
        foreach ($files as $file)
            echo PHP_EOL . "\t" . '<script src="' . $file . '"></script>';
        
        return ob_get_clean();
    }

    /**
     * Sets the objects type.
     * Select from "file", "script", "ready", "block" or "var".
     * 
     * @param string $type
     * @throws Error
     * @return \Core\Lib\Javascript
     */
    public function setType($type)
    {
        $types = array(
            'file',
            'script',
            'ready',
            'block',
            'var'
        );
        
        if (! in_array($type, $types))
            Throw new \InvalidArgumentException('Javascript targets have to be "file", "script", "block", "var" or "ready"');
        
        $this->type = $type;
        return $this;
    }

    /**
     * Sets the objects external flag.
     * 
     * @param bool $bool
     * @return \Core\Lib\Javascript
     */
    public function setIsExternal($bool)
    {
        $this->is_external = is_bool($bool) ? $bool : false;
        return $this;
    }

    /**
     * Sets the objects script content.
     * 
     * @param string $script
     * @return \Core\Lib\Javascript
     */
    public function setScript($script)
    {
        $this->script = $script;
        return $this;
    }

    /**
     * Returns the objects type.
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /*
     * + Returns the objects external flag state.
     */
    public function getIsExternal()
    {
        return $this->is_external;
    }

    /**
     * Returns the objects script content.
     * 
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Sets the objects defer state.
     * 
     * @param bool $defer
     * @return \Core\Lib\Javascript
     */
    public function setDefer($defer = false)
    {
        $this->defer = is_bool($defer) ? $defer : false;
        return $this;
    }

    /**
     * Returns the objects defer state
     * 
     * @return boolean
     */
    public function getDefer()
    {
        return $this->defer;
    }

    /**
     * Adds a file javascript object to the output queue
     * 
     * @param string $url
     * @param bool $defer
     * @param bool $is_external
     */
    public function &file($url, $defer = false, $is_external = false)
    {
        static $files_used = array();
        static $file_counter = 0;
        
        if ($this->request->isAjax()) {
            $this->ajaxadd(array(
                'type' => 'act',
                'fn' => 'load_script',
                'args' => $url
            ));
        } else {
            // Do not add files already added
            if (in_array($url, $files_used))
                Throw new \RuntimeException(sprintf('Url "%s" is already set as included js file.', $url));
            
            $dt = debug_backtrace();
            $files_used[$file_counter . '-' . $dt[1]['function']] = $url;
            $file_counter ++;
            
            $script = $this->di['core.content.js'];
            $script->setType('script');
            $script->setScript($url);
            $script->setIsExternal($is_external);
            $script->setDefer($defer);
            
            return $script->add();
        }
    }

    /**
     * Adds an script javascript object to the output queue
     * 
     * @param string $script
     * @param bool $defer
     */
    public function &script($script, $defer = false)
    {
        $script = $this->di['core.content.js'];
        $script->setType('script');
        $script->setScript($script);
        $script->setDefer($defer);
        
        return $script->add();
    }

    /**
     * Creats a ready javascript object
     * 
     * @param string $script
     * @param bool $defer
     * @return Javascript
     */
    public function &ready($script, $defer = false)
    {
        $script = $this->di['core.content.js'];
        $script->setType('ready');
        $script->setScript($script);
        $script->setDefer($defer);
        
        return $script->add();
    }

    /**
     * Blocks with complete code.
     * Use this for conditional scripts!
     * 
     * @param unknown $content
     * @param string $target
     */
    public static function &block($script, $defer = false)
    {
        $script = $this->di['core.content.js'];
        $script->setType('block');
        $script->setScript($script);
        $script->setDefer($defer);
        
        return $script->add();
    }

    /**
     * Creates and returns a var javascript object
     * 
     * @param string $name
     * @param mixed $value
     * @param bool $is_string
     * @return Javascript
     */
    public function &variable($name, $value, $is_string = false)
    {
        if ($is_string == true)
            $value = '"' . $value . '"';
        
        $script = $this->di['core.content.js'];
        $script->setType('var');
        $script->setScript([
            $name,
            $value
        ]);
        
        return $script->add($script);
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
        $url = $this->cfg->get('Core', 'url_js') . '/bootstrap-' . $version . '.min.js';
        
        if (str_replace(BASEURL, BASEDIR, $url))
            return $this->file($url);
    }
}
