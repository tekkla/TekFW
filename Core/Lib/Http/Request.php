<?php
namespace Core\Lib\Http;

/**
 * Request.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Request
{

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getURI()
    {
        return $_SERVER['REQUEST_URI'];
    }
}
