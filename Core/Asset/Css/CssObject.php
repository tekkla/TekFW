<?php
namespace Core\Asset\Css;

use Core\Asset\AbstractAssetObject;

/**
 * CssObject.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class CssObject extends AbstractAssetObject
{

    /**
     * Css type 'file'
     *
     * @var string
     */
    const TYPE_FILE = 'file';

    /**
     * Css type 'inline'
     *
     * @var string
     */
    const TYPE_INLINE = 'inline';

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AbstractAssetObject::setType()
     */
    public function setType($type)
    {
        $types = [
            self::TYPE_FILE,
            self::TYPE_INLINE
        ];

        if (!in_array($type, $types)) {
            Throw new CssException('Css type must be "inline" or "file".');
        }

        $this->type = $type;

        return $this;
    }
}
