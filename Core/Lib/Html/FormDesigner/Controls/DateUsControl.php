<?php
namespace Core\Lib\Html\FormDesigner\Controls;

use Core\Lib\Html\Controls\DateTimePicker;

/**
 * DateUsControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DateUsControl extends DateTimePicker
{

    protected $format = 'mm/dd/yyyy';

    protected $data = [
        'form-mask' => '99/99/9999'
    ];

    protected $attribute = [
        'maxlenght' => 10,
        'size' => 10
    ];
}
