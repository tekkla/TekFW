<?php
namespace Core\Lib;

use Core\Lib\Data\Connectors\Db\Db;
use Core\Lib\Traits\SerializeTrait;
use Core\Lib\Errors\Exceptions\ConfigException;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;

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
        if (isset($key)) {
            
            if (strpos($key, '.') === false) {
                Throw new InvalidArgumentException('Config keys need a leading groupname seperated by a . from the config key. For example: security.password_length');
            }
            
            list ($group, $key) = explode('.', $key);
            
            if (! isset($this->cfg[$app][$group])) {
                Throw new InvalidArgumentException(sprintf('Config group "%s" does not exist.', $group));
            }
            
            if (! isset($this->cfg[$app][$group][$key])) {
                Throw new InvalidArgumentException(sprintf('Config key "%s" does not exist in group "%s"', $key, $group));
            }
            
            return $this->cfg[$app][$group][$key];
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
        if (strpos($key, '.') === false) {
            Throw new InvalidArgumentException('Config keys need a leading groupname seperated by a . from the config key. For example: security.password_length');
        }
        
        list ($group, $key) = explode('.', $key);
        
        $this->cfg[$app][$group][$key] = $val;
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
        
        // check for config
        if (strpos($key, '.') === false) {
            Throw new InvalidArgumentException('Config keys need a leading groupname seperated by a . from the config key. For example: security.password_length');
        }
        
        list ($group, $key) = explode('.', $key);
        
        // key requested and found? true
        return isset($this->cfg[$app][$group][$key]) && ! empty($this->cfg[$app][$group][$key]);
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
        
        $this->cfg['Core'] = [];
        
        if ($cfg) {
            
            foreach ($cfg as $config => $val) {
                
                list ($group, $key) = explode('.', $config);
                
                $this->cfg['Core'][$group][$key] = $val;
            }
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
            $val = $this->isSerialized($row['val']) ? unserialize($row['val']) : $row['val'];
            
            // Extract group name and config key
            list ($group, $key) = explode('.', $row['cfg']);
            
            // Set config
            $this->cfg[$row['app']][$group][$key] = $val;
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
            $this->cfg[$app]['dir'][$key] = BASEDIR . $val;
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
            $this->cfg[$app]['url'][$key] = BASEURL . $val;
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
