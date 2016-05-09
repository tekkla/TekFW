<?php
namespace Core\Error;

/**
 * CoreExceptionInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface CoreExceptionInterface
{

    /**
     * Returns flag if this exception is a fatal one
     *
     * @return boolean
     */
    public function getFatal();

    /**
     * Should error message be shown to non admin users?
     *
     * @return boolean
     */
    public function getPublic();

    /**
     * Is errorlogging active on this exception?
     *
     * @return boolean
     */
    public function getErrorLog();

    /**
     * Should output buffer be cleaned before error output?
     *
     * @return boolean
     */
    public function getCleanBuffer();

    /**
     * Send mail to inform admin about error?
     *
     * @return boolean
     */
    public function getSendMail();

    /**
     * Write error to db driven errorlog?
     *
     * @return boolean
     */
    public function getToDb();
}
