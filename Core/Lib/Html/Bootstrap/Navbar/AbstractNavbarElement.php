<?php
namespace Core\Lib\Html\Bootstrap\Navbar;

use Core\Lib\Errors\Exceptions\HtmlException;

/**
 * Abstract NavbarElement
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */
abstract class AbstractNavbarElement
{

    /**
     *
     * @var string
     */
    protected $type;

    /**
     *
     * @var string
     */
    protected $side;

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
     * @return \Core\Lib\Html\Bootstrap\Navbar\NavbarElementAbstract|boolean
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
     * Sets element alignment to "left" or "right".
     *
     * @param string $align
     *
     * @throws HtmlException
     *
     * @return \Core\Lib\Html\Bootstrap\Navbar\NavbarElementAbstract
     */
    final public function setAlign($align)
    {
        $sides = [
            'left',
            'right'
        ];

        if (! in_array($align, $sides)) {
            Throw new HtmlException(sprintf('Your "%s" is not a valid navbar element position. Allowed are %s.', $align, implode(', ', $sides)));
        }

        $this->side = $align;

        return $this;
    }

    /**
     * Sets element alignment to "left".
     *
     * @return \Core\Lib\Html\Bootstrap\Navbar\NavbarElementAbstract
     */
    final public function alignLeft()
    {
        $this->side = 'left';

        return $this;
    }

    /**
     * Sets element alignment to "right".
     *
     * @return \Core\Lib\Html\Bootstrap\Navbar\NavbarElementAbstract
     */
    final public function alignRight()
    {
        $this->side = 'right';

        return $this;
    }

    /**
     * Build method
     */
    abstract function build();
}

