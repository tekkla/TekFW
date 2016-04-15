<?php
namespace Core\Html\Form\Traits;

use Core\Html\HtmlException;

/**
 * MaxlengthTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
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
     * @return \Core\Html\Form\Textarea
     */
    public function setMaxlength($maxlength)
    {
        if (empty((int) $maxlength)) {
            Throw new HtmlException('A html form textareas maxlength attribute needs to be of type integer.');
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

