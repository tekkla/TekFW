<?php
namespace Core\Lib\Html\FormDesigner\Controls;

use Core\Lib\Html\Controls\DateTimePicker;

/**
 * Time24Control.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Time24Control extends DateTimePicker
{

    protected $format = 'HH:mm';

    protected $data = [
        'form-mask' => '99:99'
    ];

    protected $attribute = [
        'maxlenght' => 5,
        'size' => 5
    ];
}
