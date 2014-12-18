<?php
namespace Core\Lib\Content\Html\FormDesigner\Controls;

use Core\Lib\Content\Html\Controls\DateTimePicker;

/**
 * FormDesigner Date Control
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 */
class DateControl extends DateTimePicker
{

    protected $format = 'YYYY-MM-DD';

    protected $data = [
        'form-mask' => '9999-99-99'
    ];

    protected $attribute = [
        'maxlenght' => 10,
        'size' => 10
    ];
}
