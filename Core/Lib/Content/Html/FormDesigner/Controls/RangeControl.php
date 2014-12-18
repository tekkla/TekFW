<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Form\Input;

/**
 * FormDesigner Range Control
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
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
