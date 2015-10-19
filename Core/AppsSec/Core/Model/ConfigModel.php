<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Data\Container;

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

    public function loadByApp($app_name)
    {
        // Try to get a config defintion from the app
        $cfg = $this->di->get('core.amvc.creator')
            ->getAppInstance($app_name)
            ->getConfig();

        // Do we have a defintion?
        if ($cfg) {

            // Output needs to be a container which will be used in FormDesigner
            $data = $this->getGenericContainer();

            // Add a field for a hidden control containing the app name
            $data->createField('app_name', 'string', null, false, false, null, 'Hidden', $app_name);
            $data['app_name'] = $app_name;

            // ... use those keys to check each one ...
            foreach ($cfg as $key => &$def) {

                // ... to get the config value either from config storage ...
                $val = $this->di->get('core.cfg')->get($app_name, $key);

                // check
                $control = isset($def['control']) ? $def['control'] : 'Text';

                if (is_string($control)) {
                    $control = $this->camelizeString($control);
                }

                switch ($control) {
                    case 'Number':
                    case 'Switch':
                        $def['type'] = 'int';
                        break;

                    case 'Optiongroup':
                    case 'Multiselect':
                        $def['type'] = 'array';
                        break;

                    default:
                        $def['type'] = 'string';
                        break;
                }

                // Generate container field
                $data->createField($key, $def['type'], null, false, false, null, $control);

                if (isset($cfg['serialize']) && $cfg['serialize'] == true) {
                    $val = unserialize($val);
                }

                // Set value
                $data[$key] = $val;
            }

            return $data;
        }

        // return structure
        return false;
    }

    public function saveConfig(Container $data)
    {
        // Store the appname this config is for
        $app_name = $data['app_name'];

        // Remove appname and btn values from data
        unset($data['app_name'], $data['btn_submit']);

        // Get config definition from app
        $app_cfg = $this->di->get('core.amvc.creator')
            ->getAppInstance($app_name)
            ->getConfig();

        // Add validation rules to fields in data container
        foreach ($data as $key => $fld) {

            $cfg = $app_cfg[$key];

            // Validation rules?
            if (isset($cfg['validate'])) {
                $data->setValidation($fld->getName(), $cfg['validate']);
            }
        }

        // Validate!
        $data->validate();

        // Was walidation a success or did we get some errors?
        if ($data->hasErrors()) {

            $this->extendContainer($data, $app_name);

            return $data;
        }

        // Data validated successfully. Go on and store config

        $fld_list = array_keys($app_cfg);

        $adapter = $this->getDbAdapter();

        // Start transaction
        $adapter->beginTransaction();

        // Delete current config
        $adapter->qb([
            'table' => $this->table,
            'method' => 'Delete',
            'filter' => 'app = :app',
            'params' => [
                ':app' => $app_name
            ]
        ], true);

        // Prepare insert query
        $adapter->qb([
            'table' => $this->table,
            'method' => 'INSERT',
            'fields' => [
                'app',
                'cfg',
                'val',
            ],
            'params' => [
                ':app' => $app_name
            ]
        ]);

        #$adapter->bindValue(':app_name', $app_name);

        // Create config entries
        foreach ($fld_list as $key) {

            $adapter->bindValue(':cfg', $key);

            $val = $data[$key];

            if (isset($app_cfg[$key]['serialize']) && $app_cfg[$key]['serialize'] == true) {
                $val = serialize($val);
            }

            $adapter->bindValue(':val', $val);
            $adapter->execute();
        }

        $adapter->endTransaction();
    }

    private function extendContainer(Container $data, $app_name)
    {
        // Try to get a config defintion from the app
        $cfg = $this->di->get('core.amvc.creator')
            ->getAppInstance($app_name)
            ->getConfig();

        // Do we have a defintion?
        if ($cfg) {

            // Add a field for a hidden control containing the app name
            $data->createField('app_name', 'string', null, false, false, null, 'Hidden', $app_name);
            $data['app_name'] = $app_name;

            // ... use those keys to check each one ...
            foreach ($cfg as $key => $def) {

                // check
                $control = isset($def['control']) ? $def['control'] : 'Text';

                if (is_string($control)) {
                    $control = $this->camelizeString($control);
                }

                // When a field type is not set explicitly
                // if (empty($def['type'])) {

                switch ($control) {
                    case 'Number':
                    case 'Switch':
                        $type = 'int';
                        break;

                    default:
                        $type = 'string';
                        break;
                }

                $data->getField($key)->setType($type);
            }
        }
    }
}
