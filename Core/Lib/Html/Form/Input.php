<?php
namespace Core\Lib\Html\Form;

use Core\Lib\Html\FormAbstract;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;
use Core\Lib\Html\Form\Traits\ValueTrait;
use Core\Lib\Html\Form\Traits\MaxlengthTrait;
use Core\Lib\Html\Form\Traits\PlaceholderTrait;
use Core\Lib\Html\Form\Traits\IsCheckedTrait;
use Core\Lib\Html\Form\Traits\IsMultipleTrait;

/**
 * Input Form Element
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014 by author
 * @license MIT
 */
class Input extends FormAbstract
{
    use ValueTrait;
    use MaxlengthTrait;
    use PlaceholderTrait;
    use IsCheckedTrait;
    use IsMultipleTrait;


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
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Html\Form\Input
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
            Throw new InvalidArgumentException('Your type "' . $type . '" is no valid input control type. Allowed are ' . implode(', ', $types));
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

    /**
     * Sets size attribute.
     *
     * @param int $size
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Html\Form\Input
     */
    public function setSize($size)
    {
        if (empty((int) $size)) {
            Throw new InvalidArgumentException('A html form inputs size needs to be an integer.');
        }

        $this->attribute['size'] = $size;

        return $this;
    }

    public function build()
    {
        $this->attribute['type'] = $this->type;

        return parent::build();
    }
}
