<?php
namespace Core\Lib\Exceptions;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 *        
 */
class InvalidArgumentException extends \InvalidArgumentException
{

    /**
     * => *
     * => * @param => message[optional]
     *
     * => * @param => code[optional]
     *
     * => * @param => previous[optional]
     *
     * => =>
     */
    public function __construct($message, $code, $param = array(), $previous)
    {
        $message .= 

        parent::__construct($message, $code, $previous);
    }
}

