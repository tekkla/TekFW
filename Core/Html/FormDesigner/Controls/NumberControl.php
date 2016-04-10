<?php
namespace Core\Html\FormDesigner\Controls;

use Core\Html\Form\Input;

/**
 * NumberControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class NumberControl extends Input
{

    protected $type = 'number';

    protected $attribute = [
        'min' => 0
    ];
}
