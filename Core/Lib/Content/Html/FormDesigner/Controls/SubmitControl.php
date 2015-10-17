<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Form\Button;

/**
 * SubmitControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class SubmitControl extends Button
{
    use ControlTrait;

    protected $bound = false;

    protected $type = 'submit';

    protected $inner = 'txt-btn_save';

    protected $icon = 'save';

    protected $button_type = 'primary';
}
