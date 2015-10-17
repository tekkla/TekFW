<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Form\Input;

/**
 * PhoneControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class PhoneControl extends Input
{
    use ControlTrait;

    protected $type = 'tel';
}
