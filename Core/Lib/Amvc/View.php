<?php
namespace Core\Lib\Amvc;

use Core\Lib\Errors\Exceptions\InvalidArgumentException;

/**
 * View.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
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
        $this->app = $app;
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
        // Handle objects
        if (is_object($val)) {

            // Handle buildable objects
            if (method_exists($val, 'build')) {
                $val = $val->build();
            }

            // Handle data container
            elseif (method_exists($val, 'getArray')) {
                $val = $val->getArray();
            }
            // Handle all other objects
            else {
                $val = get_object_vars($val);
            }
        }

        // Another lazy thing. It's for accessing vars in the view by ->var_name
        $this->__magic_vars[$key] = $val;

        return $this;
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
        // prevent DI from getting put into the views vars array
        if ($var == 'di') {
            $this->di = $val;
            return;
        }

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
     * Returns a dump of all set vars.
     *
     * @return string
     */
    public final function dump()
    {
        ob_start();
        echo var_dump($this->__magic_vars);

        return ob_end_flush();
    }

    /**
     * Shorthand method für htmlE() or htmlS().
     *
     * @param string|number $val
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function html($val, $mode = 's')
    {
        switch ($mode) {
            case 'e':
                return $this->htmlE($val);
            case 's':
                return $this->htmlS($val);
        }

        Throw new InvalidArgumentException(sprintf('Mode "%s" is a not supported View::html() output mode.', $mode));
    }

    /**
     * Wrapper method for encoding a value by htmlspecialchars($var, ENT_COMPAT, 'UTF-8')
     *
     * @param string|number $var
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function htmlS($val)
    {
        if (is_array($val) || is_object($val)) {
            Throw new InvalidArgumentException('It is not allowed to uses arrays or objects for htmlS() output.');
        }

        return htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Wrapper method for encoding a value by htmlenteties($val, ENT_COMPAT, 'UTF-8')
     *
     * @param string|number $val
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function htmlE($val)
    {
        if (is_array($val) || is_object($val)) {
            Throw new InvalidArgumentException('It is not allowed to uses arrays or objects for htmlE() output.');
        }

        return htmlentities($val, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Default Index()
     */
    public function Index()
    {}

    /**
     * Default Edit()
     */
    public function Edit()
    {
        if ($this->isVar('form')) {
            echo $this->form;
        }
    }
}
