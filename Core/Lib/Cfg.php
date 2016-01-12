<?php
namespace Core\Lib;

use Core\Lib\Data\Connectors\Db\Db;
use Core\Lib\Traits\SerializeTrait;
use Core\Lib\Errors\Exceptions\ConfigException;

/**
 * Cfg.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class Cfg
{
    use SerializeTrait;

    /**
     *
     * @var array
     */
    private $cfg = [];

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
        if (! isset($key) && isset($this->cfg[$app])) {
            return $this->cfg[$app];
        }

        // Calls with app and key are normal cfg requests
        if (isset($key) && isset($this->cfg[$app]) && isset($this->cfg[$app][$key])) {
            return $this->cfg[$app][$key];
        }

        // All other will result in an error exception
        Throw new ConfigException(sprintf('Config "%s" of app "%s" not found.', $key, $app));
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
        $this->cfg[$app][$key] = $val;
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
        if (! isset($this->cfg[$app])) {
            return false;
        }

        // app found and no key requested? true
        if (! isset($key)) {
            return true;
        }

        // key requested and found? true
        if (isset($key) && isset($this->cfg[$app][$key])) {
            return true;
        }

        // All other: false
        return false;
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
            Throw new ConfigException('Initial config needs to be an array', 0);
        }

        if ($cfg) {
            $this->cfg = array(
                'Core' => $cfg
            );
        }
    }

    /**
     * Loads config from database
     */
    public function load()
    {
        $this->db->qb([
            'table' => 'config',
            'order' => 'app, cfg'
        ]);

        $results = $this->db->all(\PDO::FETCH_NUM);

        foreach ($results as $row) {
            // Check for serialized data and unserialize it
            if ($this->isSerialized($row[3])) {
                $row[3] = unserialize($row[3]);
            }

            $this->cfg[$row[1]][$row[2]] = $row[3];
        }
    }

    /**
     * Adds app related file paths to the config.
     *
     * @param string $app
     * @param array $dirs
     */
    public function addPaths($app = 'Core', array $dirs = array())
    {
        // Write dirs to config storage
        foreach ($dirs as $key => $val) {
            $this->cfg[$app]['dir_' . $key] = BASEDIR . $val;
        }
    }

    /**
     * Adds app related urls to the config.
     *
     * @param string $app
     * @param array $urls
     */
    public function addUrls($app = 'Core', array $urls = array())
    {
        // Write urls to config storage
        foreach ($urls as $key => $val) {
            $this->cfg[$app]['url_' . $key] = BASEURL . $val;
        }
    }

    /**
     * Returns complete config array.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->cfg;
    }
}
