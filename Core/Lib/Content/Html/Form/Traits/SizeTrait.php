<?php
namespace Core\Lib\Content\Html\Form\Traits;

use Core\Lib\Errors\Exceptions\InvalidArgumentException;

/**
 * SizeTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait SizeTrait
{
    /**
     * Sets size attribute.
     *
     * @param integer $size
     *
     * @throws InvalidArgumentException
     */
    public function setSize($size)
    {
        if (empty((int) $size)) {
            Throw new InvalidArgumentException('Html size attribute needs to be an integer.');
        }

        $this->attribute['size'] = $size;

        return $this;
    }

    /**
     * Returns size attribute value when existing.
     *
     * @return integer|null
     */
    public function getSize()
    {
        return isset($this->attribute['size']) ? $this->attribute['size'] : null;
    }
}
