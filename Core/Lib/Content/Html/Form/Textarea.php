<?php
namespace Core\Lib\Content\Html\Form;

use Core\Lib\Content\Html\FormAbstract;

/**
 * Textarea Form Element
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Form
 * @license MIT
 * @copyright 2014 by author
 */
class Textarea extends FormAbstract
{

    protected $element = 'textarea';

    protected $data = [
        'control' => 'textarea'
    ];

    public function setPlaceholder($placeholder)
    {
        $this->attribute['placeholder'] = $placeholder;
        return $this;
    }

    public function setCols($cols)
    {
        if (! is_int($cols))
            Throw new \InvalidArgumentException('A html form textareas cols attribute need to be of type integer');

        $this->attribute['cols'] = $cols;
        return $this;
    }

    public function setRows($rows)
    {
        if (! is_int($rows))
            Throw new \InvalidArgumentException('A html form textareas rows attribute needs to be of type integer');

        $this->attribute['rows'] = $rows;
        return $this;
    }

    public function setMaxlength($maxlength)
    {
        if (! is_int($maxlength))
            Throw new \InvalidArgumentException('A html form textareas maxlenght attribute needs to be of type integer.');

        $this->attribute['maxlength'] = $maxlength;
        return $this;
    }

    public function setValue($value) {
        $this->inner = $value;
    }
}
