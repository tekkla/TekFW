<?php
namespace Core\Lib\Content\Html\Form\Traits;

use Core\Lib\Content\Html\Form\Select;
use Core\Lib\Content\Html\Form\Textarea;

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
            case ($this instanceof Select && !empty($this->value)):
                return $this->value;
            case ($this instanceof Textarea && !empty($this->inner)):
                return $this->inner;
            case (isset($this->attribute['value'])):
                return $this->attribute['value'];
            default:
                return  null;
        }
    }
}
