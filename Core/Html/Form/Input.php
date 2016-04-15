<?php
namespace Core\Html\Form;

use Core\Html\FormAbstract;
use Core\Html\Form\Traits\ValueTrait;
use Core\Html\Form\Traits\MaxlengthTrait;
use Core\Html\Form\Traits\PlaceholderTrait;
use Core\Html\Form\Traits\IsCheckedTrait;
use Core\Html\Form\Traits\IsMultipleTrait;
use Core\Html\HtmlException;

/**
 * Input.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
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
     * @return \Core\Html\Form\Input
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
            Throw new HtmlException('Your type "' . $type . '" is no valid input control type. Allowed are ' . implode(', ', $types));
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
     * @return \Core\Html\Form\Input
     */
    public function setSize($size)
    {
        if (empty((int) $size)) {
            Throw new HtmlException('A html form inputs size needs to be an integer.');
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
