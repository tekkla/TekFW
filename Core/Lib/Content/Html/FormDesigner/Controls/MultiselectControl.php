<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Form\Select;

/**
 * MultiselectControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class MultiselectControl extends Select
{
    use ControlTrait;

    protected $attribute = [
        'multiple' => false,
        'size' => 10
    ];
}
