<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Form\Input;

/**
 * FormDesigner Number Conrol
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 */
class NumberControl extends Input
{

    protected $type = 'number';

    protected $attribute = [
        'min' => 0,
    ];
}
