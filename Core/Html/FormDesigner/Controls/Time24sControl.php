<?php
namespace Core\Html\FormDesigner\Controls;

use Core\Html\Controls\DateTimePicker;

/**
 * Time24sControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Time24sControl extends DateTimePicker
{

    protected $format = 'HH:mm:ss';

    protected $data = [
        'form-mask' => '99:99:99'
    ];

    protected $attribute = [
        'maxlenght' => 8,
        'size' => 8
    ];
}
