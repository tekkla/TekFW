<?php
namespace Core\Asset;

use Core\Error\CoreExceptionInterface;

/**
 * AssetManagerException.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class AssetManagerException extends \Exception implements CoreExceptionInterface
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Error\CoreExceptionInterface::getCleanBuffer()
     *
     */
    public function getCleanBuffer()
    {
        return false;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Error\CoreExceptionInterface::getErrorLog()
     *
     */
    public function getErrorLog()
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Error\CoreExceptionInterface::getSendMail()
     *
     */
    public function getSendMail()
    {
        return false;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Error\CoreExceptionInterface::getFatal()
     *
     */
    public function getFatal()
    {
        return false;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Error\CoreExceptionInterface::getToDb()
     *
     */
    public function getToDb()
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Error\CoreExceptionInterface::getPublic()
     *
     */
    public function getPublic()
    {
        return false;
    }
}
