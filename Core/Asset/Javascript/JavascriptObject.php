<?php
namespace Core\Asset\Javascript;

use Core\Asset\AbstractAssetObject;

/**
 * JavascriptObject.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class JavascriptObject extends AbstractAssetObject
{

    /**
     * Javascript type 'file'
     *
     * @var string
     */
    const TYPE_FILE = 'file';

    /**
     * Javascript type 'script'
     *
     * @var string
     */
    const TYPE_SCRIPT = 'script';

    /**
     * Javascript type 'block'
     *
     * @var string
     */
    const TYPE_BLOCK = 'block';

    /**
     * Javascript type 'ready'
     *
     * @var string
     */
    const TYPE_READY = 'ready';

    /**
     * Javascript type 'var'
     *
     * @var string
     */
    const TYPE_VAR = 'var';

    /**
     * Javascript position 'top'
     *
     * @var string
     */
    const AREA_TOP = 'top';

    /**
     * Javascript type 'below'
     *
     * @var string
     */
    const AREA_BELOW = 'below';

    /**
     * Header (false) or scripts (true) below body? This is the target for.
     *
     * @var bool
     */
    private $defer = false;

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\AbstractAssetObject::setType()
     *
     * @throws JavascriptException
     */
    public function setType($type)
    {
        $types = [
            self::TYPE_FILE,
            self::TYPE_SCRIPT,
            self::TYPE_READY,
            self::TYPE_BLOCK,
            self::TYPE_VAR
        ];

        if (!in_array($type, $types)) {
            Throw new JavascriptException('Javascript targets have to be "file", "script", "block", "var" or "ready"');
        }

        $this->type = $type;
    }

    /**
     * Sets the objects defer state.
     *
     * @param bool $defer
     */
    public function setDefer($defer)
    {
        $this->defer = (bool) $defer;
    }

    /**
     * Returns the objects defer state
     *
     * @return boolean
     */
    public function getDefer()
    {
        return $this->defer;
    }
}
