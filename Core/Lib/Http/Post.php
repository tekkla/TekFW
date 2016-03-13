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
    public function get($prefer_core = true)
    {
        // Do only react on POST requests and when there is $_POST data
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST)) {
            return false;
        }

        // Prefer data grouped in 'core' array from FormDesigner forms?
        if ($prefer_core == true && ! empty($_POST['core'])) {
            return $_POST['core'];
        }

        // Return complete $_POST data
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
