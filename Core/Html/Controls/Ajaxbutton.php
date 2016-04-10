<?php
namespace Core\Html\Controls;

use Core\Html\Controls\UiButton;

/**
 * AjaxbuttonControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Ajaxbutton extends UiButton
{

    protected $type = 'button';

    protected $mode = 'ajax';
}
