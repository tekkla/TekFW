<?php
namespace Core\Html\Form;

use Core\Html\FormAbstract;
use Core\Errors\Exceptions\InvalidArgumentException;
use Core\Html\Form\Traits\ValueTrait;
use Core\Html\Form\Traits\MaxlengthTrait;
use Core\Html\Form\Traits\PlaceholderTrait;

/**
 * Textarea.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Textarea extends FormAbstract
{

    use ValueTrait;
    use MaxlengthTrait;
    use PlaceholderTrait;

    protected $element = 'textarea';

    protected $data = [
        'control' => 'textarea'
    ];

    /**
     * Sets cols attribute
     *
     * @param integer $cols
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Html\Form\Textarea
     */
    public function setCols($cols)
    {
        if (empty((int) $cols)) {
            Throw new InvalidArgumentException('A html form textareas cols attribute need to be of type integer');
        }

        $this->attribute['cols'] = $cols;

        return $this;
    }

    /**
     * Sets rows attribute.
     *
     * @param int $rows
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Html\Form\Textarea
     */
    public function setRows($rows)
    {
        if (empty((int) $rows)) {
            Throw new InvalidArgumentException('A html form textareas rows attribute needs to be of type integer');
        }

        $this->attribute['rows'] = $rows;

        return $this;
    }

    /**
     * Sets a value as inner value of the textarea
     *
     * @param string $value
     *
     * @return \Core\Html\Form\Textarea
     */
    public function setValue($value)
    {
        $this->inner = $value;

        return $this;
    }

    public function build()
    {
        return parent::build();
    }
}
