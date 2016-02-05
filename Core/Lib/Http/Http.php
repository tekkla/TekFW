<?php
namespace Core\Lib\Http;

final class Http
{

    /**
     *
     * @var \Core\Lib\Http\Post
     */
    public $post;

    /**
     *
     * @var \Core\Lib\Http\Cookie
     */
    public $cookie;

    public function __construct(Cookie $cookie, Post $post)
    {
        $this->cookie = $cookie;
        $this->post = $post;
    }
}
