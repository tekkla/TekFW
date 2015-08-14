<?php
namespace Core\Lib\Content\Html\Form;

/**
 * Checkbox Form Element
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2014 by author
 */
class Checkbox extends Input
{

    protected $type = 'checkbox';

    protected $element = 'input';

    protected $data = [
        'control' => 'checkbox'
    ];

    protected $attribute = [
        'value' => 1,
    ];
}

