<?php
namespace Core\Config;

use function Core\stringIsSerialized;

/**
 * Config.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Config implements ConfigInterface
{

    /**
     * Storage array for config values grouped by app name
     *
     * @var array
     */
    public $storage = [];

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

    private $repository;

    public function setRepository(ConfigRepositoryInterface $repository)
    {
        $this->repository = $repository;
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
    public function get($storage_name, $key = null)
    {
        // Calls only with app name indicates, that the complete app config is requested
        if (empty($key) && ! empty($this->storage[$storage_name])) {
            return $this->storage[$storage_name];
        }

        // Calls with app and key are normal cfg requests
        if (! empty($key)) {

            if (! isset($this->storage[$storage_name]->{$key})) {
                Throw new ConfigException(sprintf('Config "%s" of app "%s" does not exist.', $key, $storage_name));
            }

            return $this->storage[$storage_name]->{$key};
        }

        // All other will result in an error exception
        Throw new ConfigException(sprintf('Config "%s" of app "%s" not found.', $key, $storage_name));
    }

    /**
     * Set a cfg value.
     *
     * @param string $storage_name
     * @param string $key
     * @param mixed $val
     */
    public function set($storage_name, $key, $val)
    {
        $this->storage[$storage_name]->{$key} = $val;
    }

    /**
     * Checks the state of a cfg setting
     *
     * Returns true for set and false for not set.
     *
     * @param string $storage_name
     * @param string $key
     *
     * @return boolean
     */
    public function exists($storage_name, $key = null)
    {
        // No app found = false
        if (! isset($this->storage[$storage_name])) {
            return false;
        }

        // app found and no key requested? true
        if (! isset($key)) {
            return true;
        }

        // key requested and found? true
        return isset($this->storage[$storage_name]->{$key}) && ! empty($this->storage[$storage_name]->{$key});
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
        $results = $this->repository->read();

        /* @var $config \Core\Config\ConfigObject */
        foreach ($results as $config) {

            $storage = $config->getStorage();
            $id = $config->getId();
            $value = $config->getValue();

            if (! isset($this->storage[$storage])) {
                $this->storage[$storage] = new ConfigStorage();
            }

            $this->storage[$storage]->{$id} = $value;
        }
    }

    /**
     * Adds file paths to a storage
     *
     * @param string $storage_name
     * @param array $dirs
     *
     * @return \Core\Cfg\Cfg
     */
    public function addPaths($storage_name, array $dirs = [])
    {
        // Write dirs to config storage
        foreach ($dirs as $key => $val) {
            $this->storage[$storage_name]->{'dir.' . $key} = $val;
        }

        return $this;
    }

    /**
     * Adds urls to a storage
     *
     * @param string $storage_name
     * @param array $urls
     *
     * @return \Core\Cfg\Cfg
     */
    public function addUrls($storage_name, array $urls = [])
    {
        // Write urls to config storage
        foreach ($urls as $key => $val) {
            $this->storage[$storage_name]->{'url.' . $key} = $val;
        }
    }

    /**
     * Adds an array of config definitions of a storage
     *
     * @param string $storage_name
     *            The name storage this definitions are for
     * @param array $definition
     *            Array of definitions
     */
    public function addDefinition($storage_name, array $definition)
    {
        // Store flattened config. The flattening process also takes care of missing definition data
        $this->definitions[$storage_name] = $definition;

        // Check existing config for missing entries and set default values on empty config values
        $this->checkDefaults($storage_name, $definition);
    }

    /**
     * Sets config default values
     *
     * This method works recursive and checks the app given configstructure against the configdata loaded from db.
     * It checks for serialize flags in config definition and deserializes the config value if needed.
     * Fills the class structure property using the combined key as index and the controldefinition as data.
     *
     * @param string $storage_name
     *            Name of the app this config belongs to.
     * @param array $array
     *            Array with groups and/or controls to check for cfg values and default value and serialize state.
     * @param string $prefix
     *            Prefix used to build config key. Will be the current prefix + glue + current key.
     * @param string $glue
     *            The glue which combines prefix and key.
     */
    private function checkDefaults($storage_name, array $definition, $prefix = '', $glue = '.')
    {
        // First step, check for controls
        if (! empty($definition['controls'])) {

            foreach ($definition['controls'] as $name => $control) {

                // Create the config key using the prefix passed as argument and the name used as index
                $key = (! empty($prefix) ? $prefix . $glue : '') . $name;

                if (! isset($this->storage[$storage_name]->{$key}) && ! empty($control['default'])) {
                    $this->storage[$storage_name]->{$key} = $control['default'];
                }

                if (! empty($control['serialize']) && stringIsSerialized($this->storage[$storage_name]->{$key})) {
                    $this->storage[$storage_name]->{$key} = unserialize($this->storage[$storage_name]->{$key});
                }

                $this->structure[$storage_name][$key] = $control;
            }
        }

        // Do we have subgroups in this definition?
        if (! empty($definition['groups'])) {
            foreach ($definition['groups'] as $name => $group) {
                $this->checkDefaults($storage_name, $group, (! empty($prefix) ? $prefix . $glue : '') . $name);
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
        $storage_names = array_keys($this->storage);

        // Cleanup each apps config values in db
        foreach ($storage_names as $storage_name) {

            // Get all obsolete config keys
            $obsolete = array_diff(array_keys($this->storage[$storage_name]), array_keys($this->definitions[$storage_name]));

            // Create prepared IN statemen and
            $prepared = $this->db->prepareArrayQuery('c', $obsolete);

            $qb = [
                'table' => 'core_configs',
                'method' => 'DELETE',
                'filter' => 'app=:app and cfg IN (' . $prepared['sql'] . ')',
                'params' => [
                    ':app' => $storage_name
                ]
            ];

            // Prepared params to qb params
            $qb['params'] += $prepared['params'];

            $this->db->qb($qb, true);
        }
    }

    public function __get($offset)
    {
        return $this->storage[$offset];
    }

    public function createDbRepository()
    {
        return new ConfigDbRepository();
    }

    public function createFileRepository()
    {
        return new ConfigFileRepository();
    }
}
