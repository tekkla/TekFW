<?php
namespace Core\Lib\Amvc;

use Core\Lib\Abstracts\MvcAbstract;

/**
 * Basic view class.
 * Each app view has to be a child of this class.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class View extends MvcAbstract
{

    /**
     * Storage for lazy view vars to access by __get()
     *
     * @var array
     */
    private $__magic_vars = [];

    /**
     * Making the contructor private to only allow object creation by Factory method
     *
     * @param string $app
     */
    public final function __construct($name, App $app)
    {
        $this->name = $name;
    }

    /**
     * Renders the view and returns the result
     *
     * @param string $func Name of render method
     * @param array $params Optional: Parameterlist to pass to render function
     */
    public final function render($action, $params = array())
    {
        if (! method_exists($this, $action)) {
            return false;
        }
        
        return $this->di->invokeMethod($this, $action, $params);
    }

    /**
     * Passes a value by name to the view.
     * If $val is an obect, it will be checked for a build() method.
     * Does is exist, it will be called and the return value stored as value for the views var.
     *
     * @param string $key
     * @param $val
     */
    public final function setVar($key, $val)
    {
        // Objects with Create methods can be passed as object, because the
        // create method is called automatically
        if (is_object($val) && method_exists($val, 'build')) {
            $val = $val->build();
        }
        
        // Pass a model object as view var and only the data will be used.
        if (is_object($val) && $val instanceof Model) {
            $val = $val->data;
        }
        
        // Another lazy thing. It's for accessing vars in the view by ->var_name
        $this->__magic_vars[$key] = $val;
    }

    /**
     * Checks if the $var exists in the view.
     *
     * @param string $var
     * @return boolean
     */
    public final function isVar($var)
    {
        return isset($this->__magic_vars[$var]);
    }

    /**
     * Magic method for setting the view vars
     *
     * @param string $var
     * @param mixed $val0
     */
    public final function __set($var, $val)
    {
        $this->setVar($var, $val);
    }

    /**
     * Magic method for accessing the view vars
     *
     * @param string $var
     * @return Ambigous <boolean, multitype
     */
    public final function __get($var)
    {
        return isset($this->__magic_vars[$var]) ? $this->__magic_vars[$var] : 'var:' . $var;
    }

    /**
     * Magic isset
     *
     * @param string $key
     */
    public final function __isset($key)
    {
        return isset($this->__magic_vars[$key]);
    }

    /**
     * Returns a dumps all seth vars
     *
     * @return string
     */
    public final function dump()
    {
        ob_start();
        echo var_dump($this->__magic_vars);
        return ob_end_flush();
    }
}
