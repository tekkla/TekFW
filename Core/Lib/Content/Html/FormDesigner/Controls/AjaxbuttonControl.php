<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Controls\UiButton;

/**
 * AjaxbuttonControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class AjaxbuttonControl extends UiButton
{

    use ControlTrait;

    protected $type = 'button';

    protected $mode = 'ajax';
}
