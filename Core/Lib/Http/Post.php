<?php
namespace Core\Lib\Http;

/**
 * Http POST processor
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2014
 * @version 1.0
 */
class Post
{

    use\Core\Lib\Traits\StringTrait;

    /**
     *
     * @var array
     */
    private $post = [];

    /**
     *
     * @var Router
     */
    private $router;

    /**
     * Constructor
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Returns the value of $_POST[appname][key]
     *
     * @param string $app
     * @param string $key
     *
     * @return array|boolean
     */
    public function get($app = '', $key = '')
    {
        // Do only react on POST requests
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST)) {
            return false;
        }

        // Use values provided by request for missing app and model name
        if (! $app || ! $key) {
            $app = $this->router->getApp();
            $key = $this->router->getCtrl();
        }

        $app = $this->uncamelizeString($app);
        $key = $this->uncamelizeString($key);

        return isset($_POST[$app][$key]) ? $_POST[$app][$key] : false;
    }

    /**
     * Returns the complete POST array
     *
     * @return array
     */
    public function raw()
    {
        return $_POST;
    }
}
