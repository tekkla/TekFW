<?php
namespace Core\Lib\Html\Form\Traits;

use Core\Lib\Errors\Exceptions\InvalidArgumentException;

/**
 * MaxlengthTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait MaxlengthTrait
{

    /**
     * Sets maxlength attribute
     *
     * @param integer $maxlength
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Html\Form\Textarea
     */
    public function setMaxlength($maxlength)
    {
        if (empty((int) $maxlength)) {
            Throw new InvalidArgumentException('A html form textareas maxlength attribute needs to be of type integer.');
        }

        $this->attribute['maxlength'] = $maxlength;

        return $this;
    }

    /**
     * Returns maxlength attribute value.
     *
     * @return int
     */
    public function getMaxlength()
    {
        return (int) $this->attribute['maxlength'];
    }

}

