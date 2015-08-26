<?php
namespace Core\Lib\Content\Html\Form\Traits;

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
     * Sets and get the checked attribute.
     *
     * @param bool Optional $state Set this to set or change the attribute. Leave blank to get attribute state.
     *
     * @return boolena|\Core\Lib\Content\Html\Form\Input
     */
    public function isChecked($bool = null)
    {
        $attrib = 'checked';

        if (! isset($bool)) {
            return $this->checkAttribute($attrib);
        }

        if ((bool) $bool == false) {
            $this->removeAttribute($attrib);
        }
        else {
            $this->attribute[$attrib] = false;
        }

        return $this;
    }
}

