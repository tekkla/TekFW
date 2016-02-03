<?php
namespace Core\Lib\Html\FormDesigner\Controls;

use Core\Lib\Html\Controls\DateTimePicker;

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

    public function __construct()
    {
        // Input mask
        $this->data['form-mask'] = '9999-99-99 99:99';
        
        $this->attribute['maxlength'] = 10;
        $this->attribute['size'] = 16;
    }
}
