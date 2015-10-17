<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Form\Input;

/**
 * NumberControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class NumberControl extends Input
{
    use ControlTrait;

    protected $type = 'number';

    protected $attribute = [
        'min' => 0
    ];
}
