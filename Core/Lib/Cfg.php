<?php
namespace Core\Lib;

if (! defined('TEKFW'))
    die('Cannot run without TekFW framework...');

/**
 * Handles all TekFW low level config related stuff
 * 
 * @author Michael "Tekkla" Zorn (tekkla@tekkla.de)
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
final class Cfg
{
    use\Core\Lib\Traits\SerializeTrait;

    private $cfg;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get an cfg setting
     * 
     * @param string $app
     * @param string $key
     * @throws Error
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
        Throw new \RuntimeException(sprintf('Config "%s" of app "%s" not found.', $key, $app));
    }

    /**
     * Set a cfg setting
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
     * Returns true for set and false for not set.
     * 
     * @param string $app
     * @param string $key
     */
    public function exists($app, $key = null)
    {
        // No app found = false
        if (! isset($this->cfg[$app]))
            return false;
            
            // app found and no key requested? true
        if (! isset($key))
            return true;
            
            // key requested and found? true
        if (isset($key) && isset($this->cfg[$app][$key]))
            return true;
            
            // All other: false
        return false;
    }

    /**
     * Init config.
     * Parameter is used as initial core config
     * 
     * @param array $cfg
     */
    public function init($cfg = array())
    {
        if (! is_array($cfg))
            Throw new \InvalidArgumentException('Initial config needs to be an array');
        
        if ($cfg)
            $this->cfg = array(
                'Core' => $cfg
            );
    }

    /**
     * Loads config from database
     */
    public function load()
    {
        $this->db->query('SELECT * FROM {db_prefix}config ORDER BY app, cfg');
        $this->db->execute();
        
        $results = $this->db->resultset(\PDO::FETCH_NUM);
        
        foreach ($results as $row) {
            // Check for serialized data and unserialize it
            if ($this->isSerialized($row[3]))
                $row[3] = unserialize($row[3]);
            
            $this->cfg[$row[1]][$row[2]] = $row[3];
        }
    }

    public function addPaths($app = 'Core', $dirs = array())
    {
        // Write dirs to config storage
        foreach ($dirs as $key => $val)
            $this->cfg[$app]['dir_' . $key] = BASEDIR . $val;
    }

    public function addUrls($app = 'Core', $urls = array())
    {
        // Write urls to config storage
        foreach ($urls as $key => $val)
            $this->cfg[$app]['url_' . $key] = BASEURL . $val;
    }
}
