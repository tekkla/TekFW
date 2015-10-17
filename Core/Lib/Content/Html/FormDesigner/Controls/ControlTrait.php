<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

/**
 * ControlTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait ControlTrait
{
    private $bound=true;

    public function isBound()
    {
        $this->bound = true;

        return $this;
    }

    public function notBound()
    {
        $this->bound = false;

        return $this;
    }

    public function getBound()
    {
        return $this->bound;
    }
}
