<?php
namespace Core\Http;

use Core\Http\Cookie\Cookies;

/**
 * Http.php
 *
 * Wrapper service to provide access to Post and Cookies service via one service.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Http
{
    /**
     * Post Service
     *
     * @var \Core\Http\Post
     */
    public $post;

    /**
     * Cookies Service
     *
     * @var \Core\Http\Cookie\Cookies
     */
    public $cookies;

    /**
     * Header Service
     *
     * @var Header
     */
    public $header;

    public function __construct(Cookies $cookies, Post $post, Header $header)
    {
        $this->cookies = $cookies;
        $this->post = $post;
        $this->header = $header;
    }
}
