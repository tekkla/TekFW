<?php
namespace Core\Lib\Content\Html\Form\Traits;

use Core\Lib\Content\Html\Form\Select;

/**
 * ValueTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait ValueTrait
{

    private $value = null;

    /**
     * Sets value attribute.
     *
     * @param string|number $value
     */
    public function setValue($value)
    {
        if ($this instanceof Select) {
            $this->value = $value;
        }
        else {
            $this->attribute['value'] = $value;
        }

        return $this;
    }

    public function getValue()
    {
        if ($this instanceof Select) {
            return $this->value;
        }

        return isset($this->attribute['value']) ? $this->attribute['value'] : null;
    }
}
