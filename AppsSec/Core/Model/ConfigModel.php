<?php
namespace AppsSec\Core\Model;

use Core\{
    Amvc\Model,
    Validator\Validator
};

use function Core\{
    arrayAssignByKeys,
    arrayAssignByPath,
    arrayFlatten
};


/**
 * ConfigModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class ConfigModel extends Model
{

    protected $scheme = [
        'table' => 'core_configs',
        'alias' => 'cfg',
        'primary' => 'id_config',
        'fields' => [
            'id_config' => [
                'type' => 'int'
            ],
            'app' => [
                'type' => 'string'
            ],
            'cfg' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ]
            ],
            'val' => [
                'type' => 'string',
                'serialize' => true,
                'validate' => [
                    'empty'
                ]
            ]
        ]
    ];

    public function getConfigGroups($app_name)
    {
        return array_keys($this->di->get('core.config')->definitions[$app_name]['groups']);
    }

    public function getDefinition($name)
    {
        return $this->di->get('core.config')['definitions'];
    }

    /**
     * Checks a config definition for missing settings and adds them on demand
     *
     * @param array $def
     *            Definition array to check for missing settings
     */
    public function checkDefinitionFields(&$def)
    {
        $check_default = [
            'serialize' => false,
            'translate' => false,
            'data' => false,
            'validate' => [],
            'filter' => [],
            'default' => '',
            'control' => 'text'
        ];

        foreach ($check_default as $property => $default) {
            if (! isset($def[$property])) {
                $def[$property] = $default;
            }
        }

        // Set default value as value
        $def['value'] = $def['default'];

        // Define field type by control type
        switch ($def['control']) {
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
    }

    public function saveConfig(&$data)
    {

        // Store the appname this config is for
        $app_name = $data['app'];
        unset($data['app']);

        $flat = arrayFlatten($data);

        $this->validateConfig($app_name, $flat);

        if ($this->hasErrors()) {
            return $data;
        }

        // Data validated successfully. Go on and store config
        $db = $this->getDbConnector();

        // Start transaction
        $db->beginTransaction();

        // Prepare insert query
        $qb = [
            'scheme' => $this->scheme,
            'data' => [
                'app' => $app_name
            ]
        ];

        // Create config entries one by one
        foreach ($flat as $cfg => $val) {

            // Delete current config value
            $db->delete($this->scheme['table'], 'app=:app AND cfg=:cfg', [
                ':app' => $app_name,
                ':cfg' => $cfg
            ]);

            // Add config value
            $qb['data']['cfg'] = $cfg;

            if (is_array($val) || $val instanceof \Serializable) {
                $val = serialize($val);
            }

            $qb['data']['val'] = $val;

            $db->qb($qb, true);
        }

        $db->endTransaction();

        return $data;
    }

    private function validateConfig($app_name, $data)
    {
        static $validator;

        foreach ($data as $key => $val) {

            $definition = $this->di->get('core.config')->structure[$app_name][$key];

            // Any validation rules in structur on this level?
            if (! empty($definition['validate'])) {

                if (empty($validator)) {
                    $validator = new Validator();
                }

                $validator->validate($val, $definition['validate']);

                // Any errors?
                if (! $validator->isValid()) {

                    // and create error informations
                    arrayAssignByKeys($this->errors, explode('.', $key), $validator->getResult());
                }

                // next please!
                continue;
            }
        }
    }

    public function getData($app_name, $group_name)
    {
        $data = [];

        $configs = $this->di->get('core.config')->{$app_name};

        foreach ($configs as $path => $value) {
            arrayAssignByPath($data, $path, $value);
        }

        if (! empty($data[$group_name])) {
            $data = $data[$group_name];
        }

        return $data;
    }

    public function getAllRoutes()
    {
        $out = [];

        $router = $this->di->get('core.router');
        $routes = $router->getRoutes();

        foreach ($routes as $route) {
            $out[$route[3]] = $route[3] . ' (' . $route[0] . ': ' . $route[1] . ')';
        }

        return $out;
    }
}
