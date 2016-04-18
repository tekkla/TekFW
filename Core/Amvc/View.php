<?php
namespace Core\Amvc;

/**
 * View.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
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
     * @param string $name
     *            Objects name
     * @param string $app
     *            Related App object
     */
    public final function __construct($name, App $app)
    {
        $this->name = $name;
        $this->app = $app;
    }

    /**
     * Renders the view and returns the result
     *
     * @param string $func
     *            Name of render method
     * @param array $params
     *            Optional: Parameterlist to pass to render function
     */
    public final function render($action, $params = array())
    {
        if (! method_exists($this, $action)) {
            return false;
        }

        // Render into own outputbuffer
        ob_start();

        $this->di->invokeMethod($this, $action, $params);

        return ob_get_clean();
    }

    /**
     * Passes a value by name to the view
     *
     * If $val is an obect, it will be checked for a build() and a getArray() method.
     * Does is exist, it will be called and the return value stored as value for the views var.
     *
     * @param string $key
     *            The name of the var
     * @param mixed $val
     *            The vars value
     *
     * @return \Core\Amvc\View
     */
    public final function setVar($key, $val)
    {
        // Handle objects
        if (is_object($val)) {

            switch (true) {

                // Handle buildable objects
                case method_exists($val, 'build'):
                    $val = $val->build();
                    break;

                // Handle all other objects
                default:
                    $val = get_object_vars($val);
                    break;
            }
        }

        // Another lazy thing. It's for accessing vars in the view by ->var_name
        $this->__magic_vars[$key] = $val;

        return $this;
    }

    /**
     * Returns the value of a set var
     *
     * Nearly the same as magic method __get() but in this method will throw an
     * ViewException when var does not exist.
     *
     * @param string $key
     *
     * @throws ViewException
     *
     * @return mixed
     */
    final public function getVar($key)
    {
        if (! array_key_exists($key, $this->__magic_vars)) {
            Throw new ViewException(sprintf('The requested var "%s" does not exist in current view.', $key));
        }

        return $this->__magic_vars[$key];
    }

    /**
     * Checks if the $key exists in the view
     *
     * @param string $key
     *            The vars name
     *
     * @return boolean
     */
    public final function isVar($key)
    {
        return array_key_exists($key, $this->__magic_vars);
    }

    /**
     * Magic method for setting the view vars
     *
     * @param string $key
     *            The name of the var
     * @param mixed $val
     *            The value to set
     */
    public final function __set($key, $val)
    {
        // prevent DI from getting put into the views vars array
        if ($key == 'di') {
            $this->di = $val;
            return;
        }

        $this->setVar($key, $val);
    }

    /**
     * Magic method for accessing the view vars
     *
     * @param string $key
     *            The name of the var
     *
     * @return Ambigous <boolean, multitype
     */
    public final function __get($key)
    {
        return array_key_exists($key, $this->__magic_vars) ? $this->__magic_vars[$key] : 'var:' . $key;
    }

    /**
     * Magic isset
     *
     * @param string $key
     */
    public final function __isset($key)
    {
        return array_key_exists($key, $this->__magic_vars);
    }

    /**
     * Returns a dump of all set vars
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
     *            The value to encode
     *
     * @throws ViewException
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

        Throw new ViewException(sprintf('Mode "%s" is a not supported View::html() output mode.', $mode));
    }

    /**
     * Wrapper method for encoding a value by htmlspecialchars($key, ENT_COMPAT, 'UTF-8')
     *
     * @param string|number $val
     *            The value to encode
     *
     * @throws ViewException
     *
     * @return string
     */
    protected function htmlS($val)
    {
        if (is_array($val) || is_object($val)) {
            Throw new ViewException('It is not allowed to uses arrays or objects for htmlS() output.');
        }

        return htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Wrapper method for encoding a value by htmlenteties($val, ENT_COMPAT, 'UTF-8')
     *
     * @param string|number $val
     *            The value to encode
     *
     * @throws ViewException
     *
     * @return string
     */
    protected function htmlE($val)
    {
        if (is_array($val) || is_object($val)) {
            Throw new ViewException('It is not allowed to uses arrays or objects for htmlE() output.');
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
