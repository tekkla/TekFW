<?php
namespace Core\Lib\Content\Html\Form\Traits;

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
     * Sets and get the multiple attribute.
     *
     * @param bool Optional $bool Set this to set or change the attribute. Leave blank to get attribute state.
     *
     * @return boolena|\Core\Lib\Content\Html\Form\Input
     */
    public function isMultiple($bool = null)
    {
        $attrib = 'multiple';

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

