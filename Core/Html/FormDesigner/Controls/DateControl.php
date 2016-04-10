<?php
namespace Core\Html\FormDesigner\Controls;

use Core\Html\Controls\DateTimePicker;

/**
 * DateControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
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
