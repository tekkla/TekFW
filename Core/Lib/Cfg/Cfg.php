<?php
namespace Core\Lib\Cfg;

// Data Libs
use Core\Lib\Data\Connectors\Db\Db;

// Traits
use Core\Lib\Traits\StringTrait;

/**
 * Cfg.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016s
 * @license MIT
 */
final class Cfg
{
    use StringTrait;

    /**
     *
     * @var array
     */
    public $data = [];

    /**
     *
     * @var Database
     */
    private $db;

    /**
     * Constructor
     *
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * Get a cfg value.
     *
     * @param string $app
     * @param string $key
     *
     * @throws ConfigException
     *
     * @return mixed
     */
    public function get($app, $key = null)
    {
        // Calls only with app name indicates, that the complete app config is requested
        if (! isset($key) && isset($this->data[$app])) {
            return $this->data[$app];
        }

        // Calls with app and key are normal cfg requests
        if (isset($key)) {
            if (! isset($this->data[$app][$key])) {
                Throw new CfgException(sprintf('Config "%s" of app "%s" does not exist."', $key, $app));
            }

            return $this->data[$app][$key];
        }

        // All other will result in an error exception
        Throw new CfgException(sprintf('Config "%s" of app "%s" not found.', $key, $app));
    }

    /**
     * Set a cfg value.
     *
     * @param string $app
     * @param string $key
     * @param mixed $val
     */
    public function set($app, $key, $val)
    {
        $this->data[$app][$key] = $val;
    }

    /**
     * Checks the state of a cfg setting.
     *
     * Returns true for set and false for not set.
     *
     * @param string $app
     * @param string $key
     */
    public function exists($app, $key = null)
    {
        // No app found = false
        if (! isset($this->data[$app])) {
            return false;
        }

        // app found and no key requested? true
        if (! isset($key)) {
            return true;
        }

        // key requested and found? true
        return isset($this->data[$app][$key]) && ! empty($this->data[$app][$key]);
    }

    /**
     * Init config.
     * Parameter is used as initial core config
     *
     * @param array $cfg
     *
     * @throws ConfigException
     */
    public function init($cfg = [])
    {
        if (! is_array($cfg)) {
            Throw new CfgException('Initial config needs to be an array', 0);
        }

        $this->data['Core'] = [];

        if ($cfg) {
            $this->data['Core'] = $cfg;
        }
    }

    /**
     * Loads config from database
     */
    public function load()
    {
        $this->db->qb([
            'table' => 'config',
            'fields' => [
                'app',
                'cfg',
                'val'
            ],
            'order' => 'app, cfg'
        ]);

        $results = $this->db->all();

        foreach ($results as $row) {

            // Check for serialized config value and unserialize it
            $val = $this->stringIsSerialized($row['val']) ? unserialize($row['val']) : $row['val'];

            // Set config
            $this->data[$row['app']][$row['cfg']] = $val;
        }
    }

    /**
     * Adds app related file paths to the config.
     *
     * @param string $app
     * @param array $dirs
     */
    public function addPaths($app = 'Core', array $dirs = [])
    {
        // Write dirs to config storage
        foreach ($dirs as $key => $val) {
            $this->data[$app]['dir.' . $key] = BASEDIR . $val;
        }
    }

    /**
     * Adds app related urls to the config.
     *
     * @param string $app
     * @param array $urls
     */
    public function addUrls($app = 'Core', array $urls = [])
    {
        // Write urls to config storage
        foreach ($urls as $key => $val) {
            $this->data[$app]['url.' . $key] = BASEURL . $val;
        }
    }

    /**
     * Returns complete config array.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->data;
    }
}
