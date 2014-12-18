<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Form\Button;

/**
 * FormDesigner Reset Control
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 */
class ResetControl extends Button
{

    protected $type = 'reset';

    protected $inner = 'txt-btn_reset';
}
