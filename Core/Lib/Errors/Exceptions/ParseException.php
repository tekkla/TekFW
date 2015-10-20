<?php
namespace Core\Lib\Errors\Exceptions;

/**
 * ParseException.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class ParseException extends BasicException
{
    protected $to_db = false;
}
