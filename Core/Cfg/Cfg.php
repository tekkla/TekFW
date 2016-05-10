<?php
namespace Core\Cfg;

use Core\Data\Connectors\Db\Db;
use phpFastCache\CacheManager;
use function Core\stringIsSerialized;

/**
 * Cfg.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Cfg
{
    /**
     * Storage array for config values grouped by app name
     *
     * @var AppCfg
     */
    public $data;

    /**
     * Flattened version of config definition grouped by app name
     *
     * @var array
     */
    public $structure = [];

    /**
     * Storage array for config definitions grouped by app names
     *
     * @var array
     */
    public $definitions = [];

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
        $this->data = new AppCfg();
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
        if (empty($key) && ! empty($this->data->{$app_name})) {
            return $this->data->{$app_name};
        }

        // Calls with app and key are normal cfg requests
        if (! empty($key)) {

            if (! isset($this->data->{$app_name}->{$key})) {
                Throw new CfgException(sprintf('Config "%s" of app "%s" does not exist.', $key, $app_name));
            }

            return $this->data->{$app_name}->{$key};
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
        $this->data->{$app_name}->{$key} = $val;
    }

    /**
     * Checks the state of a cfg setting
     *
     * Returns true for set and false for not set.
     *
     * @param string $app_name
     * @param string $key
     *
     * @return boolean
     */
    public function exists($app_name, $key = null)
    {
        // No app found = false
        if (! isset($this->data->{$app_name})) {
            return false;
        }

        // app found and no key requested? true
        if (! isset($key)) {
            return true;
        }

        // key requested and found? true
        return isset($this->data->{$app_name}->{$key}) && ! empty($this->data->{$app_name}->{$key});
    }

    /**
     * Init config.
     * Parameter is used as initial core config
     *
     * @param array $cfg
     *
     * @throws CfgException
     *
     * @return void
     */
    public function init(array $cfg = [])
    {
        $this->data->Core = new AppCfg();

        if (! empty($cfg)) {

            foreach ($cfg as $key => $value) {
                $this->data->Core->{$key} = $cfg;
            }
        }
    }

    /**
     * Loads config from database
     *
     * @param boolean $refresh
     *            Optional flag to force a refresh load of the config that updates the cached config too
     *
     * @return void
     */
    public function load($refresh = false)
    {
        $cache = CacheManager::getInstance();

        $config = $cache->get('Core.Config');

        // Refresh config when there is no config in cache or a refresh has been requested
        if (empty($config) || $refresh == true) {

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

                if (! isset($this->data->{$row['app']})) {
                    $this->data->{$row['app']} = new AppCfg();
                }

                $this->data->{$row['app']}->{$row['cfg']} = $row['val'];
            }

            $cache->set('Core.Config', $this->data);
        }
        else {
            $this->data = $config;
        }
    }

    /**
     * Adds app related file paths to the config.
     *
     * @param string $app_name
     * @param array $dirs
     *
     * @return \Core\Cfg\Cfg
     */
    public function addPaths($app_name = 'Core', array $dirs = [])
    {
        // Write dirs to config storage
        foreach ($dirs as $key => $val) {
            $this->data->{$app_name}->{'dir.' . $key} = BASEDIR . $val;
        }

        return $this;
    }

    /**
     * Adds app related urls to the config.
     *
     * @param string $app_name
     * @param array $urls
     *
     * @return \Core\Cfg\Cfg
     */
    public function addUrls($app_name = 'Core', array $urls = [])
    {
        // Write urls to config storage
        foreach ($urls as $key => $val) {
            $this->data->{$app_name}->{'url.' . $key} = BASEURL . $val;
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
     * @return \Core\Cfg\Cfg
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
     * Sets config default values
     *
     * This method works recursive and checks the app given configstructure against the configdata loaded from db.
     * It checks for serialize flags in config definition and deserializes the config value if needed.
     * Fills the class structure property using the combined key as index and the controldefinition as data.
     *
     * @param string $app_name
     *            Name of the app this config belongs to.
     * @param array $array
     *            Array with groups and/or controls to check for cfg values and default value and serialize state.
     * @param string $prefix
     *            Prefix used to build config key. Will be the current prefix + glue + current key.
     * @param string $glue
     *            The glue which combines prefix and key.
     */
    private function checkDefaults($app_name, array $definition, $prefix = '', $glue = '.')
    {
        // First step, check for controls
        if (! empty($definition['controls'])) {

            foreach ($definition['controls'] as $name => $control) {

                // Create the config key using the prefix passed as argument and the name used as index
                $cfg = (! empty($prefix) ? $prefix . $glue : '') . $name;

                if (! isset($this->data->{$app_name}->{$cfg}) && ! empty($control['default'])) {
                    $this->data->{$app_name}->{$cfg} = $control['default'];
                }

                if (! empty($control['serialize']) && stringIsSerialized($this->data->{$app_name}->{$cfg})) {
                    $this->data->{$app_name}->{$cfg} = unserialize($this->data->{$app_name}->{$cfg});
                }

                $this->structure[$app_name][$cfg] = $control;
            }
        }

        // Do we have subgroups in this definition?
        if (! empty($definition['groups'])) {
            foreach ($definition['groups'] as $name => $group) {
                $this->checkDefaults($app_name, $group, (! empty($prefix) ? $prefix . $glue : '') . $name);
            }
        }
    }

    /**
     * Cleans config table by deleting all config entries that do not exist in config definition anymore
     *
     * @return void
     */
    public function cleanConfig()
    {

        // Get name of all apps that have values in config table
        $app_names = array_keys($this->data);

        // Cleanup each apps config values in db
        foreach ($app_names as $app_name) {

            // Get all obsolete config keys
            $obsolete = array_diff(array_keys($this->data->{$app_name}), array_keys($this->definitions[$app_name]));

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

    public function __get($offset)
    {
        return $this->data->{$offset};
    }
}
