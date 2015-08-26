<?php
namespace Core\Lib\Errors\Exceptions;

/**
 * DatabaseException.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DatabaseException extends BasicException
{

    protected $to_db = false;

    protected $send_mail = true;

    protected $fatal = true;

}
