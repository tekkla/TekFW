<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Controls\DateTimePicker;

/**
 * FormDesigner Time 24h with seconds Control
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
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
