<?php
namespace Core\Lib\Html\Form\Traits;

/**
 * PlaceholderTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait PlaceholderTrait
{

    public function setPlaceholder($placeholder)
    {
        $this->attribute['placeholder'] = $placeholder;

        return $this;
    }

    public function getPlaceholder()
    {
        return $this->attribute['placeholder'];
    }

}
