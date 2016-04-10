<?php
namespace Core\Html\Form\Traits;

/**
 * IsMultipleTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait IsMultipleTrait
{

    /**
     * Sets the multiple attribute.
     *
     * @return boolena|\Core\Html\Form\Input
     */
    public function isMultiple()
    {
       $this->attribute['multiple'] = false;

        return $this;
    }

    /**
     * Removes possible set multiple attribute.
     *
     * @return boolena|\Core\Html\Form\Input
     */
    public function notMultiple()
    {
        $this->removeAttribute('multiple');

        return $this;
    }

    /**
     * Returns current multiple atribute state.
     *
     * @return boolena|\Core\Html\Form\Input
     */
    public function getMultiple()
    {
        return $this->checkAttribute('multiple');
    }

}

