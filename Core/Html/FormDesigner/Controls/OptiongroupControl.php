<?php
namespace Core\Html\FormDesigner\Controls;

use Core\Html\Controls\OptionGroup;

/**
 * OptiongroupControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class OptiongroupControl extends OptionGroup implements ControlsCollectionInterface
{

    public function getControls()
    {
        return $this->controls;
    }

    public function clearControls()
    {
        $this->controls = [];
    }
}
