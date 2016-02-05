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
        
        $data = $this->getGenericContainer();
        
        foreach ($cfg as $key => $def) {
            
            // Do we have predefiend values to respect?
            switch (true) {
                
                // By full key name group.name
                case array_key_exists($key, $values):
                    $val = $values[$key];
                    break;
                
                // No value found => use the value from config
                default:
                    $val = $this->di->get('core.cfg')->get($app_name, $key);
                    break;
            }
            
            switch ($def['control']) {
                case 'Number':
                case 'Switch':
                    $type = 'int';
                    break;
                
                case 'Optiongroup':
                case 'Multiselect':
                    $type = 'array';
                    break;
                
                default:
                    $type = 'string';
                    break;
            }
            
            // Generate container field
            $field = $data->createField($key, $type, null, false, $def['serialize'], $def['validate'], $def['control'], $val, $def['filter']);
            
            // Config definition can have more properties. Here we look for and add to field
            $additional_data = [
                'data',
                'translate'
            ];
            
            foreach ($additional_data as $field_name) {
                $field[$field_name] = $def[$field_name];
            }
        }
        
        return $data;
    }

    public function saveConfig(&$data)
    {
        // Store the appname this config is for
        $app_name = $data['app_name'];
        
        // Store the current groups name
        $group_name = $data['group_name'];
        
        $unused = [
            'app_name',
            'group_name',
            'btn_submit'
        ];
        
        // Get config definition from app
        $data = $this->loadByApp($app_name, $data);
        
        // Filter
        $data->filter();
        
        // Was walidation a success or did we get some errors?
        if (! $data->validate()) {
            return $data;
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
        $db->qb([
            'table' => $this->table,
            'method' => 'INSERT',
            'fields' => [
                'app',
                'cfg',
                'val'
            ],
            'params' => [
                ':app' => $app_name
            ]
        ]);
        
        // $db->bindValue(':app_name', $app_name);
        
        // Create config entries
        foreach ($data as $key => $val) {
            
            if (in_array($key, $unused)) {
                continue;
            }
            
            $db->bindValue(':cfg', $key);
            
            if ($data->getField($key)->getSerialize()) {
                $val = serialize($val);
            }
            
            $db->bindValue(':val', $val);
            $db->execute();
        }
        
        $db->endTransaction();
        
        return $data;
    }
}
