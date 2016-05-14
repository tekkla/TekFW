<?php
namespace Core\Logger\Streams;

/**
 * EchoStream.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class EchoStream extends StreamAbstract
{

    /**
     *
     * {@inheritDoc}
     *
     * @see \Psr\Log\LoggerInterface::log()
     */
    public function log($level, $message, array $context = array())
    {
        // TODO Auto-generated method stub
    }
}
