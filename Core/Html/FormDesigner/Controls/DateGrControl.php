<?php
namespace Core\Html\FormDesigner\Controls;

use Core\Html\Controls\DateTimePicker;

/**
 * DateGrControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DateGrControl extends DateTimePicker
{

    protected $format = 'dd.mm.yyyy';

    protected $data = [
        'form-mask' => '99.99.9999'
    ];

    protected $attribute = [
        'maxlenght' => 10,
        'size' => 10
    ];
}
