<?php
namespace Core\Lib\Http;

/**
 * Post.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Post
{
    /**
     * Returns the value of $_POST
     *
     * @return array
     */
    public function get()
    {
        // Do only react on POST requests
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST)) {
            return false;
        }

        return $_POST;

    }

    /**
     * Cleans the global $_POST variable by setting an empty array
     */
    public function clean()
    {
        $_POST = [];
    }
}
