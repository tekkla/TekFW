<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Data\Container\Container;

/**
 * ConfigModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class ConfigModel extends Model
{

    private $table = 'config';

    public function getConfigGroups($app_name)
    {
        // Try to get a config defintion from the app
        $cfg = $this->di->get('core.amvc.creator')
            ->getAppInstance($app_name)
            ->getConfig()['raw'];

        return array_keys($cfg);
    }

    /**
     * Returns app config container groups
     *
     * @param string $app_name
     *            Name of app to get config from
     * @param string $exclude_group
     *            Exclude this group from config
     *
     * @return array
     */
    public function loadByApp($app_name, array $values = [])
    {
        // Try to get a config defintion from the app
        $cfg = $this->di->get('core.amvc.creator')
            ->getAppInstance($app_name)
            ->getConfig()['flat'];

        $data = [];

        foreach ($cfg as $key => &$def) {

            // Value provided by parameter?
            if (array_key_exists($key, $values)) {
                $def['value'] = $values[$key];
            }
            // Value existin in config?
            elseif ($this->di->get('core.cfg')->exists($app_name, $key)) {
                $def['value'] = $this->di->get('core.cfg')->get($app_name, $key);
            }
            // Default value from definition?
            elseif (isset($def['default'])) {
                $def['value'] = $def['default'];
            }
        }

        return $cfg;
    }

    public function saveConfig(&$data)
    {
        // Store the appname this config is for
        $app_name = $data['app.name'];

        $unused = [
            'app_name',
            'btn_submit'
        ];

        // Get config definition from app and set values
        $fulldata = $this->loadByApp($app_name, $data);

        // Create a data scheme on the fly
        $pseudo_scheme = [
            'table' => 'config',
            'fields' => $fulldata
        ];

        #$data = $this->filter($data, $pseudo_scheme);

        // Validate
        $this->validate($data, $pseudo_scheme);

        if ($this->hasErrors()) {

            \FB::log($this->getErrors());

            $data = $fulldata;
        }

       // Data validated successfully. Go on and store config
        $db = $this->getDbConnector();

        // Start transaction
        $db->beginTransaction();

        // Delete current config
        $db->delete($this->table, 'app=:app', [
            ':app' => $app_name
        ]);

        // Prepare insert query
        $qb = [
            'scheme' => $pseudo_scheme,
            'data' => [
                'app' => $app_name
            ],
        ];

        // Create config entries one by one
        foreach ($fulldata as $def) {

            \FB::log($def);

            $qb['data']['cfg'] = $def['name'];

            $val = $def['value'];

            if (!empty($def['serialize'])) {
                $val = serialize($val);
            }

            $qb['data']['val'] = $val;

            $db->qb($qb, true);

        }

        $db->endTransaction();

        $data = $fulldata;
    }
}
