<?php
namespace Core\Lib\Html\Form\Traits;

use Core\Lib\Html\Form\Select;
use Core\Lib\Html\Form\Textarea;

/**
 * ValueTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait ValueTrait
{

    /**
     * Sets value attribute.
     *
     * @param string|number $value
     */
    public function setValue($value)
    {
        switch (true) {
            case ($this instanceof Select):
                if (! is_array($value)) {
                    $value = (array) $value;
                }

                $this->value = $value;
                break;
            case ($this instanceof Textarea):
                $this->inner = $value;
                break;
            default:
                $this->attribute['value'] = $value;
                break;
        }

        return $this;
    }

    public function getValue()
    {
        switch (true) {
            case ($this instanceof Select):
                return $this->value;
            case ($this instanceof Textarea):
                return $this->inner;
            case (isset($this->attribute['value'])):
                return $this->attribute['value'];
            default:
                return false;
        }
    }

    public function unsetValue()
    {
        switch (true) {
        case ($this instanceof Select):
            $this->value = [];
            break;
        case ($this instanceof Textarea):
            $this->inner = '';
            break;
        default:
            $this->removeAttribute('value');
            break;
        }
    }
}
