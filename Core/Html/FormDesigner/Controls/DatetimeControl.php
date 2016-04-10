<?php
namespace Core\Html\FormDesigner\Controls;

use Core\Html\Controls\DateTimePicker;

/**
 * DatetimeControl.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DatetimeControl extends DateTimePicker
{

    protected $format = 'YYYY-MM-DD HH:mm';

    protected $data = [
        'form-mask' => '9999-99-99 99:99'
    ];

    protected $attribute = [
        'maxlength' => 10,
        'size' => 16,
    ];

    /*
    public function __construct()
    {
        // Input mask
        $this->data['form-mask'] = '9999-99-99 99:99';

        $this->attribute['maxlength'] = 10;
        $this->attribute['size'] = 16;
    }
    */
}
