<?php
namespace Core\Lib\Http;

use Core\Lib\Data\Container;

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
     *
     * @var string
     */
    private $app;

    /**
     *
     * @var string
     */
    private $key;

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
     * Returns the value of $_POST[appname][key] as container or an array.
     * Default is 'container'
     *
     * @param string $app
     * @param string $key
     * @param string $return_type Optional: Type of data to return. Default: Container
     *
     * @return array|Container|boolean
     */
    public function get($app = '', $key = '', $return_type = 'container')
    {
        switch ($return_type) {
            case 'array':
                return $this->getArray($app, $key);

            case 'container':
            default:
                return $this->getContainer($app, $key);
        }
    }

    /**
     * Returns POST data as a container
     *
     * @param string $app
     * @param string $key
     *
     * @return boolean|Container
     */
    public function getContainer($app = '', $key = '')
    {
        $post = $this->checkPost($app, $key);

        if (! $post) {
            return false;
        }

        // Get container from matching app
        $container = $this->di->get('core.amvc.creator')
            ->create($this->app)
            ->getContainer($this->key);

        if (! $container) {
            $container = $this->di->get('core.data.container');
        }

        // Fill data into container
        $container->fill($post);

        return $container;
    }

    /**
     * Returns POST data as an array
     *
     * @param string $app
     * @param string $key
     *
     * @return boolean|array
     */
    public function getArray($app = '', $key = '')
    {
        return $this->checkPost($app, $key);
    }

    private function checkPost($app = '', $key = '')
    {
        // Do only react on POST requests
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST)) {
            return false;
        }

        // Use values provided by request for missing app and model name
        if (empty($app) || empty($key)) {
            $this->app = $this->router->getApp();
            $this->key = $this->router->getCtrl();
        }

        $app_small = $this->uncamelizeString($this->app);
        $key_small = $this->uncamelizeString($this->key);

        // Return false on missing data
        if (! isset($_POST[$app_small][$key_small])) {
            return false;
        }

        return $_POST[$app_small][$key_small];
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
