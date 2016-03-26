<?php
namespace Core\Lib\Html\FormDesigner\Controls;

use Core\Lib\Html\Form\Input;

/**
 * RangeControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class RangeControl extends Input
{

    protected $type = 'number';

    protected $attribute = [
        'min' => 0,
        'max' => 100
    ];
}
