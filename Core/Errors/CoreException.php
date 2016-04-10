<?php
namespace Core\Errors;

/**
 * CoreException.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class CoreException extends \ErrorException
{

    /**
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
     *
     * @var boolean
     */
    protected $error_log = true;

    /**
     *
     * @var boolean
     */
    protected $clean_buffer = false;

    /**
     *
     * @var boolean
     */
    protected $send_mail = false;

    /**
     *
     * @var boolean
     */
    protected $to_db = true;

    /**
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

    /**
     * Is errorlogging active on this exception?
     *
     * @return boolean
     */
    public function getErrorLog()
    {
        return is_bool($this->error_log) ? $this->error_log : false;
    }

    /**
     * Should output buffer be cleaned before error output?
     *
     * @return boolean
     */
    public function getCleanBuffer()
    {
        return is_bool($this->clean_buffer) ? $this->clean_buffer : false;
    }

    /**
     * Send mail to inform admin about error?
     *
     * @return boolean
     */
    public function getSendMail()
    {
        return is_bool($this->send_mail) ? $this->send_mail : false;
    }

    /**
     * Write error to db driven errorlog?
     *
     * @return boolean
     */
    public function getToDb()
    {
        return is_bool($this->to_db) ? $this->to_db : false;
    }

}
