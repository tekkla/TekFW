<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Controls\UiButton;

/**
 * FormDesigner Ajaxbutton Control
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 */
class AjaxbuttonControl extends UiButton
{
    protected $type = 'button';
    protected $mode = 'ajax';
}
