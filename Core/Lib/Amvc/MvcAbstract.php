<?php
namespace Core\Lib\Amvc;

// Traits
use Core\Lib\Security\AccessTrait;
use Core\Lib\Traits\StringTrait;

/**
 * MvcAbstract.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class MvcAbstract
{
    use StringTrait;
    use AccessTrait;

    /**
     * Name of the MVC object
     *
     * @var string
     */
    protected $name;

    /**
     * Holds injected App object this MVC object is used for
     *
     * @var App
     */
    public $app;

    /**
     * MVC objects need an app instance
     *
     * @param App $app
     *            App object to inject
     *
     * @return \Core\Lib\Abstracts\MvcAbstract
     */
    final public function injectApp(App $app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Sets the name of the MVC object
     *
     * @param string $name
     *            Name of this object
     *
     * @return \Core\Lib\Abstracts\MvcAbstract
     */
    final public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the name of the MVC object.
     *
     * @throws AmvcException
     *
     * @return string
     */
    final public function getName()
    {
        if (isset($this->name)) {
            return $this->name;
        }

        Throw new AmvcException('Name from MVC component is not set.');
    }

    /**
     * Returns the name of the App object insite the MVC object.
     *
     * @throws AmvcException
     *
     * @return string
     */
    final public function getAppName()
    {
        if (! isset($this->app)) {
            Throw new AmvcException('MVC component has no set app name.');
        }

        return $this->app->getName();
    }

    /**
     * Checks for a method in this object
     *
     * @param string $method_name
     *            Name of the method to check for
     * @param boolean $throw_error
     *            Optional flag for throwing an AmvcException when method does not exist.
     *
     * @throws AmvcException
     *
     * @return boolean
     */
    final public function checkMethodExists($method_name, $throw_error = true)
    {
        $return = method_exists($this, $method_name);

        if ($return == false && $throw_error == true) {
            Throw new AmvcException(sprintf('There is no method "%s" in %s-object "%s"', $method_name, $this->type, $this->getName()));
        }

        return $return;
    }
}
