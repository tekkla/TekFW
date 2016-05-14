<?php
namespace Core\Data\Connectors;

use Core\Error\CoreExceptionInterface;

/**
 * ConnectorException.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class ConnectorException implements CoreExceptionInterface
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Error\CoreExceptionInterface::getCleanBuffer()
     *
     */
    public function getCleanBuffer()
    {
        return true;
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
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Error\CoreExceptionInterface::getFatal()
     *
     */
    public function getFatal()
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Error\CoreExceptionInterface::getToDb()
     *
     */
    public function getToDb()
    {
        return false;
    }

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

?>