<?php
namespace Core\Ajax;

use Core\Ajax\Commands\AjaxCommandException;

/**
 * AbstractAjaxCommand.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractAjaxCommand
{

    const ACT = 'act';

    const DOM = 'dom';

    /**
     * Kind of command
     *
     * @var string
     */
    protected $type = 'dom';

    /**
     * Parameters to pass into the controlleraction
     *
     * @var mixed
     */
    protected $args;

    /**
     * The type of the current ajax.
     *
     * @var string
     */
    protected $fn = 'html';

    /**
     * Identifier settable to use mainly on debugging.
     *
     * @var string
     */
    protected $id = '';

    /**
     * Sets the ajax command arguments array
     *
     * @param mixed $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }

    /**
     * Sets ajax command group type
     *
     * @param string $type
     *            Type of thsi command can by either be 'dom' oder 'act'
     */
    public function setType($type)
    {
        if (empty($fn)) {
            Throw new AjaxCommandException('Empty commandtype is not permitted.');
        }

        if ($type != self::ACT && $type != self::DOM) {
            Throw new AjaxCommandException(sprintf('%s is no valid commandtype', $type));
        }

        $this->type = $type;
    }

    /**
     * Sets function type of ajax command
     *
     * @param string $fn
     *            jQuery functionname of this command
     */
    public function setFunction($fn)
    {
        if (empty($fn)) {
            Throw new AjaxCommandException('Empty functionname is not permitted.');
        }

        $this->fn = $fn;
    }

    /**
     * Returns ajax command type
     *
     * @return the $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns ajax command arguments
     *
     * @return mixed $args
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Returns ajax command function
     *
     * @return the $fn
     */
    public function getFn()
    {
        return $this->fn;
    }

    /**
     * Sets an optional identifier for this command
     *
     * @param string $id
     *            Command identifier
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

}
