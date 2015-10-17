<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Controls\DataSelect;

/**
 * DataselectControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DataselectControl extends DataSelect
{
    use ControlTrait;

    protected $css = [
        'form-select'
    ];
}
