<?php
namespace Core\Lib\Errors\Exceptions;

/**
 * BasicException class
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015 by author
 * @license MIT
 *
 */
class BasicException extends \ErrorException
{

    /**
     * Flag that indicates the weight of the exception.
     *
     * @var boolean
     */
    protected $fatal = true;

    /**
     * Flag to set the
     *
     * @var boolean
     */
    protected $public = false;

    /**
     * Is this a fatal exception?
     *
     * @return boolean
     */
    public function getFatal()
    {
        return is_bool($this->fatal) ? $this->fatal : true;
    }

    /**
     * Should error message be shown to non admin users?
     *
     * @return boolean
     */
    public function getPublic()
    {
        return is_bool($this->public) ? $this->public : false;
    }
}