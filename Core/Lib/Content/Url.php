<?php
namespace Core\Lib\Content;

/**
 * Url class for creating manual urls and by named routes
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class Url
{
    use\Core\Lib\Traits\StringTrait;

    /**
     * Name of route to compile
     * 
     * @var string
     */
    private $named_route;

    /**
     * Params for route compiling
     * 
     * @var array
     */
    private $param = [];

    /**
     * App name
     * 
     * @var string
     */
    private $app;

    /**
     * Controller name
     * 
     * @var string
     */
    private $ctrl;

    /**
     * Action to call
     * 
     * @var string
     */
    private $func;

    /**
     * Ajax flag
     * 
     * @var int
     */
    private $ajax = false;
    
    // ------------------------------------------
    // Global
    // ------------------------------------------
    
    /**
     * Target parameter
     * 
     * @var string
     */
    private $target;

    /**
     * Anchor parameter
     * 
     * @var string
     */
    private $anchor;

    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Factory method which returns an url string based on a compiled named route
     * 
     * @param string $named_route Optional name of route to compile
     * @param array $param Optional parameters to use on route
     * @return string
     */
    public function compile($named_route, $param = array())
    {
        $url = new self($this->di['core.request']);
        $url->setNamedRoute($named_route);
        $url->setParameter($param);
        
        return $url->getUrl();
    }

    /**
     * Sets name of route to be compiled
     * 
     * @param string $named_route
     * @todo Mayve it is a good idea to check for route existance at this point rather than on compiling.
     * @return \Core\Lib\Url
     */
    public function setNamedRoute($named_route)
    {
        $this->named_route = $named_route;
        return $this;
    }

    /**
     * Flags url to be ajax
     * 
     * @param bool $bool
     * @return \Core\Lib\Url
     */
    public function setAjax($bool = true)
    {
        $this->ajax = $bool;
        $this->param['is_ajax'] = $bool;
        return $this;
    }

    /**
     * Sets name of app for route compiling.
     * 
     * @param string $app
     * @return \Core\Lib\Url
     */
    public function setApp($app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Sets name of controller for route compiling.
     * 
     * @param string $ctrl
     * @return \Core\Lib\Url
     */
    function setCtrl($ctrl)
    {
        $this->ctrl = $ctrl;
        return $this;
    }

    /**
     * Sets route function
     * 
     * @param string $func
     * @return \Core\Lib\Url
     */
    function setFunc($func)
    {
        $this->func = $func;
        return $this;
    }

    /**
     * Adds target parameter
     * 
     * @param string $target
     * @return \Core\Lib\Url
     */
    function setTarget($target)
    {
        $this->param['target'] = $target;
        return $this;
    }

    /**
     * Adds one parameter in form of key and value or a list of parameters as assoc array.
     * Setting an array as $arg1 and leaving $arg2 empty means to add an assoc array of paramters
     * Setting $arg1 and $arg2 means to set on parameter by name and value.
     * 
     * @var string|array String with parametername or list of parameters of type assoc array
     * @var string $arg2 Needs only to be set when seting on paramters by name and value.
     * @var bool $reset Optional: Set this to true when you want to reset already existing parameters
     * @throws Error
     * @return \Core\Lib\Url
     */
    function setParameter($arg1, $arg2 = null, $reset = false)
    {
        if ($reset === true)
            $this->param = [];
        
        if ($arg2 === null && is_array($arg1) && ! empty($arg1)) {
            foreach ($arg1 as $key => $val)
                $this->param[$key] = $val;
        }
        
        if (isset($arg2))
            $this->param[$arg1] = $arg2;
        
        return $this;
    }

    /**
     * Same as setParameter but without resetting existing parameters.
     * 
     * @see setParameter()
     * @return \Core\Lib\Url
     */
    public function addParameter($arg1, $arg2 = null)
    {
        $this->setParameter($arg1, $arg2, false);
        return $this;
    }

    /**
     * Sets name of anchor
     * 
     * @param string $anchor
     * @return \Core\Lib\Url
     */
    function setAnchor($anchor)
    {
        $this->anchor = $anchor;
        return $this;
    }

    /**
     * Processes all parameters and returns a fully compiled url as string.
     * 
     * @return string
     */
    function getUrl($definition = array())
    {
        if ($definition) {
            foreach ($definition as $property => $value)
                if (property_exists($this, $property))
                    $this->{$property} = $value;
        }
        
        // if action isset, we have a smf url to build
        if (isset($this->named_route))
            return $this->request->getRouteUrl($this->named_route, $this->param);
        
        return false;
    }

    /**
     *
     * @param unknown $key
     */
    private function unsetData($key)
    {
        if (isset($this->{$key}))
            unset($this->{$key});
    }

    /**
     * Converts classical URLs into SEO friendly ones.
     * Urls like index.php?action=admin will become /admin/.
     *
     * @param unknown $match
     * @return unknown string
     */
    public static function convertSEF($raw_url)
    {
        // Parse the url
        $parsed = parse_url($raw_url[0]);
        
        // Without any querystring we return the url
        if (! isset($parsed['query']))
            return $raw_url[0];
            
            // Split query string into part
        $query_parts = explode(';', $parsed['query']);
        
        // On no parts the url is return untaimed
        if (empty($query_parts))
            return $raw_url[0];
        
        $parsed['params'] = [];
        
        // Prepare the query parts into a key/value par
        foreach ($query_parts as $pair) {
            if (strpos($pair, '=') !== false)
                list ($key, $val) = explode('=', $pair);
            else
                $key = $val = $pair;
            
            $parsed['params'][$key] = $val;
        }
        
        // Empty params or no 'action' set or not 'action' first query part? Return url unchanged
        if (empty($parsed['params']) || ! isset($parsed['params']['action']) || key($parsed['params']) != 'action')
            return $raw_url[0];
            
            // All checks done. Lets rewrite the url
        $url = self::factory();
        
        foreach ($parsed['params'] as $key => $val) {
            $method = 'set' . $this->camelizeString($key);
            
            if ($key != 'board' && $key != 'topic' && method_exists($url, $method))
                $url->{$method}($val);
            else
                $url->setParameter($key, $val);
        }
        
        // And finally return the rewritten url
        return $url->getUrl();
    }
}

