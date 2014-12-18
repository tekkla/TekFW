<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Controls\DateTimePicker;

/**
 * FormDesigner Time 12h with seconds and meridian Control
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 */
class Time12sControl extends DateTimePicker
{

    protected $format = 'hh:mm A/PM';

    protected $data = [
        'form-mask' => '99:99'
    ];

    protected $attribute = [
        'maxlenght' => 5,
        'size' => 5
    ];

    // @todo 'showMeridian' => true
}
