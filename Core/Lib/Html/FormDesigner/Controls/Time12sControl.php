<?php
namespace Core\Lib\Html\FormDesigner\Controls;

use Core\Lib\Html\Controls\DateTimePicker;

/**
 * Time12sControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Time12sControl extends DateTimePicker
{

    protected $format = 'hh:mm::ss A/PM';

    protected $data = [
        'form-mask' => '99:99:99'
    ];

    protected $attribute = [
        'maxlenght' => 8,
        'size' => 8
    ];

    // @todo 'showMeridian' => true
}
