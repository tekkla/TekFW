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
            case ($this instanceof Select):
                return !empty($this->value) ? $this->value : null;
            case ($this instanceof Textarea):
                return !empty($this->inner) ? $this->inner : null;
            default:
                return isset($this->attribute['value']) ? $this->attribute['value'] : null;
        }
    }
}
