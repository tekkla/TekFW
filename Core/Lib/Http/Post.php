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

    use \Core\Lib\Traits\StringTrait;

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
        if (empty($app) || empty($key)) {
            $app = $this->router->getApp();
            $ctrl = $this->router->getCtrl();
        }

        $app_small = $this->uncamelizeString($app);
        $ctrl_small = $this->uncamelizeString($ctrl);

        // Return false on missing data
        if (! isset($_POST[$app_small][$ctrl_small])) {
            return false;
        }

        // Get container from matching app
        $container = $this->di->get('core.amvc.creator')
            ->create($app)
            ->getContainer($ctrl);

        if (! $container) {
            $container = $this->di->get('core.data.container');
        }

        // Fill data into container
        $container->fill($_POST[$app_small][$ctrl_small]);

        return $container;
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
