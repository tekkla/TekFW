<?php
namespace Core\Lib\Content\Html\Bootstrap\Navbar;

/**
 * Abstract NavbarElement
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */
abstract class NavbarElementAbstract
{

    /**
     *
     * @var string
     */
    protected $type;

    /**
     *
     * @var bool
     */
    private $active = false;

    /**
     * Sets or gets active state of element
     *
     * @param bool $active
     *
     * @return \Core\Lib\Content\Html\Bootstrap\Navbar\NavbarElementAbstract|boolean
     */
    final public function isActive($active = null)
    {
        if (isset($active)) {
            $this->active = (bool) $active;
            return $this;
        }
        else {
            return $this->active;
        }
    }

    /**
     * Returns type of element.
     */
    final public function getType()
    {
        return $this->type;
    }

    /**
     * Build method
     */
    abstract function build()
    {}
}

