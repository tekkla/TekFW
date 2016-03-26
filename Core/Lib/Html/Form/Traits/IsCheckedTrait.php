<?php
namespace Core\Lib\Html\Form\Traits;

/**
 * IsCheckedTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait IsCheckedTrait
{

    /**
     * Sets checked attribute.
     *
     * @return boolena|\Core\Lib\Html\Form\Input
     */
    public function isChecked()
    {
        $this->attribute['checked'] = false;

        return $this;
    }

    /**
     * Removes a possible set checked attribute.
     *
     * @return boolena|\Core\Lib\Html\Form\Input
     */
    public function notChecked()
    {
        $this->removeAttribute('checked');

        return $this;
    }

    /**
     * Returns current checked attribute state
     *
     * @return boolean
     */
    public function getChecked()
    {
        return $this->checkAttribute('checked');
    }
}

