<?php
namespace Core\Lib\Cfg;

// Data Libs
use Core\Lib\Data\Connectors\Db\Db;

// Traits
use Core\Lib\Traits\StringTrait;
use Core\Lib\Traits\ArrayTrait;

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
    use ArrayTrait;

    /**
     * Storage array for config values grouped by app names
     *
     * @var array
     */
    public $data = [];

    /**
     * Storage array for config definitions grouped by app names
     *
     * @var array
     */
    public $definitions = [
        'flat' => [],
        'raw' => []
    ];

    /**
     *
     * @var Db
     */
    private $db;

    /**
     * Constructor
     *
     * @param Db $db
     *            DB servie
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
    public function get($app_name, $key = null)
    {
        // Calls only with app name indicates, that the complete app config is requested
        if (empty($key) && ! empty($this->data[$app_name])) {
            return $this->data[$app_name];
        }

        // Calls with app and key are normal cfg requests
        if (! empty($key)) {
            if (! array_key_exists($key, $this->data[$app_name])) {
                Throw new CfgException(sprintf('Config "%s" of app "%s" does not exist.<br><br>Current config:<pre>%s</pre>"', $key, $app_name, print_r($this->data[$app_name], true)));
            }

            return $this->data[$app_name][$key];
        }

        // All other will result in an error exception
        Throw new CfgException(sprintf('Config "%s" of app "%s" not found.', $key, $app_name));
    }

    /**
     * Set a cfg value.
     *
     * @param string $app_name
     * @param string $key
     * @param mixed $val
     */
    public function set($app_name, $key, $val)
    {
        $this->data[$app_name][$key] = $val;
    }

    /**
     * Checks the state of a cfg setting.
     *
     * Returns true for set and false for not set.
     *
     * @param string $app_name
     * @param string $key
     */
    public function exists($app_name, $key = null)
    {
        // No app found = false
        if (! isset($this->data[$app_name])) {
            return false;
        }

        // app found and no key requested? true
        if (! isset($key)) {
            return true;
        }

        // key requested and found? true
        return isset($this->data[$app_name][$key]) && ! empty($this->data[$app_name][$key]);
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
            'table' => 'core_configs',
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
     * @param string $app_name
     * @param array $dirs
     *
     * @return \Core\Lib\Cfg\Cfg
     */
    public function addPaths($app_name = 'Core', array $dirs = [])
    {
        // Write dirs to config storage
        foreach ($dirs as $key => $val) {
            $this->data[$app_name]['dir.' . $key] = BASEDIR . $val;
        }

        return $this;
    }

    /**
     * Adds app related urls to the config.
     *
     * @param string $app_name
     * @param array $urls
     *
     * @return \Core\Lib\Cfg\Cfg
     */
    public function addUrls($app_name = 'Core', array $urls = [])
    {
        // Write urls to config storage
        foreach ($urls as $key => $val) {
            $this->data[$app_name]['url.' . $key] = BASEURL . $val;
        }

        return $this;
    }

    /**
     * Adds an array of config definitions of an app
     *
     * @param string $app_name
     *            The name of app
     * @param array $definition
     *            Array of definitions
     *
     * @return \Core\Lib\Cfg\Cfg
     */
    public function addDefinition($app_name, array $definition)
    {
        // Store flattened config. The flattening process also takes care of missing definition data
        $this->definitions[$app_name] = $definition;

        // Check existing config for missing entries and set default values on empty config values
        $this->checkDefaults($app_name, $definition);

        return $this;
    }

    /**
     * Flattens a multidimensional array
     *
     * @param array $array
     *            The array to flatten
     * @param string $glue
     *            Optional glue to get flattened array with this glue as return value
     * @param boolean $preserve_flagged_arrays
     *            With this optional flag and a set __preserve key in the array the array will be still flattended but
     *            also be stored as array with an ending .array key. Those arrays will not be flattened further more.
     *            This means any nesting array will stay arrays in this array.
     *
     * @return string|array
     */
    private function checkDefaults($app_name, array $array, $prefix = '', $glue = '.')
    {
        $result = [];

        foreach ($array as $key => $value) {

            if (array_key_exists('name', $value) && is_string($value['name'])) {

                $cfg = $prefix . $key;

                if (empty($this->data[$app_name][$cfg]) && ! empty($value['default'])) {
                    $this->data[$app_name][$cfg] = $value['default'];
                }
            }
            else {
                // Subarrray handling needed?
                $result = $result + $this->checkDefaults($app_name, $value, $prefix . $key . $glue, $glue);
            }
        }

        return $result;
    }

    /**
     * Cleans config table by deleting all config entries that do not exist in config definition anymore
     */
    public function cleanConfig()
    {

        // Get name of all apps that have values in config table
        $app_names = array_keys($this->data);

        // Cleanup each apps config values in db

        foreach ($app_names as $app_name) {

            // Get all obsolete config keys
            $obsolete = array_diff(array_keys($this->data[$app_name]), array_keys($this->definitions[$app_name]));

            // Create prepared IN statemen and
            $prepared = $this->db->prepareArrayQuery('c', $obsolete);

            $qb = [
                'table' => 'core_configs',
                'method' => 'DELETE',
                'filter' => 'app=:app and cfg IN (' . $prepared['sql'] . ')',
                'params' => [
                    ':app' => $app_name
                ]
            ];

            // Prepared params to qb params
            $qb['params'] += $prepared['params'];

            $this->db->qb($qb, true);
        }
    }
}
