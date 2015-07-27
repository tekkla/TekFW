<?php
namespace Core\Lib\Content\Html\Form;

use Core\Lib\Content\Html\FormAbstract;

/**
 * Input Form Element
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014 by author
 * @license MIT
 */
class Input extends FormAbstract
{

    // element specific value for
    // type: text|hidden|button|submit
    // default: text
    protected $type = 'text';

    protected $element = 'input';

    protected $data = [
        'control' => 'input'
    ];

    /**
     * Sets input type
     *
     * @param string $type
     *
     * @throws \InvalidArgumentException
     *
     * @return \Core\Lib\Content\Html\Form\Input
     */
    public function setType($type)
    {
        $types = [
            'button',
            'checkbox',
            'color',
            'date',
            'datetime',
            'datetime-local',
            'email',
            'file',
            'hidden',
            'image',
            'month',
            'number',
            'password',
            'radio',
            'range',
            'reset',
            'search',
            'submit',
            'tel',
            'text',
            'time',
            'url',
            'week '
        ];

        if (! in_array($type, $types)) {
            Throw new \InvalidArgumentException('Your type "' . $type . '" is no valid input control type. Allowed are ' . implode(', ', $types));
        }

        $this->type = $type;
        $this->attribute['type'] = $type;
        $this->data['control'] = $type == 'hidden' ? 'hidden' : 'input';

        return $this;
    }

    /**
     * Returns the input type attribute
     */
    public function getType()
    {
        return $this->type;
    }

    public function setValue($value)
    {
        $this->attribute['value'] = $value;

        return $this;
    }

    public function getValue()
    {
        return isset($this->attribute['value']) ? $this->attribute['value'] : null;
    }

    public function setSize($size)
    {
        if (! is_int($size))
            Throw new \InvalidArgumentException('A html form inputs size needs to be an integer.');

        $this->attribute['size'] = $size;
        return $this;
    }

    public function setMaxlength($maxlength)
    {
        if (! is_int($maxlength))
            Throw new \InvalidArgumentException('A html form inputs maxlenght needs to be an integer.');

        $this->attribute['maxlength'] = $maxlength;
        return $this;
    }

    public function setPlaceholder($placeholder)
    {
        $this->attribute['placeholder'] = $placeholder;
        return $this;
    }

    public function isChecked($state = null)
    {
        $attrib = 'checked';

        if (! isset($state))
            return $this->checkAttribute($attrib);

        if ($state == 0)
            $this->removeAttribute($attrib);
        else
            $this->attribute[$attrib] = false;

        return $this;
    }

    public function isMultiple($bool = true)
    {
        if ($bool == true)
            $this->attribute['multiple'] = false;
        else
            $this->removeAttribute('multiple');
    }

    public function build()
    {
        $this->attribute['type'] = $this->type;
        return parent::build();
    }
}
