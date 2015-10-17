<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Controls\UiButton;

/**
 * AjaxiconControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class AjaxiconControl extends UiButton
{
    use ControlTrait;

    protected $type = 'icon';

    protected $mode = 'ajax';
}
