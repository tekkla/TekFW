<?php
namespace Core\Lib\Content\Html\Form\Traits;

/**
 * ValueTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait ValueTrait
{

    private $value;

    /**
     * Sets value attribute.
     *
     * @param string|number $value
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return isset($this->attribute['value']) ? $this->attribute['value'] : null;
    }
}
