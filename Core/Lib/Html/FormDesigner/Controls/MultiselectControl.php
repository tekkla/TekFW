<?php
namespace Core\Lib\Html\FormDesigner\Controls;

use Core\Lib\Html\Form\Select;

/**
 * MultiselectControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class MultiselectControl extends Select
{

    protected $attribute = [
        'multiple' => false,
        'size' => 10
    ];
}
