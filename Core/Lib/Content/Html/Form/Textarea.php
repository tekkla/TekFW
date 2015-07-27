<?php
namespace Core\Lib\Content\Html\Form;

use Core\Lib\Content\Html\FormAbstract;

/**
 * Textarea Form Element
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015
 */
class Textarea extends FormAbstract
{

    protected $element = 'textarea';

    protected $data = [
        'control' => 'textarea'
    ];

    private $value = null;

    public function setPlaceholder($placeholder)
    {
        $this->attribute['placeholder'] = $placeholder;

        return $this;
    }

    public function setCols($cols)
    {
        if (! is_int($cols)) {
            Throw new \InvalidArgumentException('A html form textareas cols attribute need to be of type integer');
        }

        $this->attribute['cols'] = $cols;

        return $this;
    }

    public function setRows($rows)
    {
        if (! is_int($rows)) {
            Throw new \InvalidArgumentException('A html form textareas rows attribute needs to be of type integer');
        }

        $this->attribute['rows'] = $rows;

        return $this;
    }

    public function setMaxlength($maxlength)
    {
        if (! is_int($maxlength)) {
            Throw new \InvalidArgumentException('A html form textareas maxlenght attribute needs to be of type integer.');
        }

        $this->attribute['maxlength'] = $maxlength;

        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function build()
    {
        if ($this->value === null) {
            $this->value = '';
        }

        $this->inner = $this->value;

        return parent::build();
    }
}
