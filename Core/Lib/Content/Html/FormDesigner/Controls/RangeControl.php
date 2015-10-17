<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Form\Input;

/**
 * RangeControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class RangeControl extends Input
{
    use ControlTrait;

    protected $type = 'number';

    protected $attribute = [
        'min' => 0,
        'max' => 100
    ];
}
