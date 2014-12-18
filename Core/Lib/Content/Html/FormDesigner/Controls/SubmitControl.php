<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Form\Button;

/**
 * FormDesigner Submit Control
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 */
class SubmitControl extends Button
{

    protected $type = 'submit';

    protected $inner = 'txt-btn_save';

    protected $icon = 'save';

    protected $button_type = 'primary';
}
