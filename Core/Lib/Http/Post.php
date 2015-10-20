<?php
namespace Core\Lib\Http;

use Core\Lib\Data\Container;
use Core\Lib\Traits\StringTrait;
use Core\Lib\Security\Security;

/**
 * Post.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Post
{

    use StringTrait;

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
     * @var Security
     */
    private $security;

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
    public function __construct(Router $router, Security $security)
    {
        $this->router = $router;
        $this->security = $security;
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
     * Cleans the global $_POST variable by setting an empty array.
     */
    public function clean()
    {
        $_POST = [];
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
        // Checks for posted data.
        $post = $this->checkPost($app, $key);

        if (! $post) {
            return false;
        }

        // Get container from matching app
        $container = $this->di->get('core.amvc.creator')
            ->getAppInstance($app)
            ->getContainer($key);

        // No container from app recieved? Create a generic one!
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

    private function checkPost(&$app = '', &$key = '')
    {
        // Do only react on POST requests
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST)) {
            return false;
        }

        // Validate posted data with session token
        if (! $this->security->validatePostToken()) {

            // Log attempt and start ban process with max 3 tries.
            $this->security->logSuspicious('Form data without proper token received. All data will be dropped.', 3);

            return false;
        }

        // Use values provided by request for missing app and model name
        if (empty($app) || empty($key)) {
            $app = $this->router->getApp();
            $key = $this->router->getController();
        }

        $app_small = $this->uncamelizeString($app);
        $key_small = $this->uncamelizeString($key);

        // Return false on missing data
        if (! isset($_POST[$app_small][$key_small])) {
            return false;
        }

        // Trim data
        array_walk_recursive($_POST[$app_small][$key_small], function (&$data)
        {
            $data = trim($data);
        });

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
