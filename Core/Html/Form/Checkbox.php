<?php
namespace Core\Html\Form;

use Core\Html\Form\Traits\ValueTrait;
use Core\Html\Form\Traits\IsCheckedTrait;

/**
 * Checkbox.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Checkbox extends Input
{
    use ValueTrait;
    use IsCheckedTrait;

    protected $type = 'checkbox';

    protected $element = 'input';

    protected $data = [
        'control' => 'checkbox'
    ];

    protected $attribute = [
        'value' => 1
    ];
}

