<?php
namespace Core\Lib\Amvc;

// DataLibs
use Core\Lib\Data\Connectors\Db\Db;

// Traits
use Core\Lib\Traits\ArrayTrait;
use Core\Lib\Router\UrlTrait;
use Core\Lib\Cfg\CfgTrait;
use Core\Lib\Language\TextTrait;
use Core\Lib\Security\Security;
use Core\Lib\Data\Validator\Validator;

/**
 * Model.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Model extends MvcAbstract
{

    use UrlTrait;
    use TextTrait;
    use CfgTrait;
    use ArrayTrait;

    /**
     * MVC component type
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     *
     * @var array
     */
    protected $scheme = [];

    /**
     * Access om Security service
     *
     * @var Security
     */
    protected $security;

    /**
     * Storage for model errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Constructor
     *
     * @param string $name
     * @param App $app
     * @param Security $security
     */
    final public function __construct($name, App $app, Security $security)
    {
        // Set Properties
        $this->name = $name;
        $this->app = $app;
        $this->security = $security;

        $this->loadScheme();
    }

    /**
     * Loads scheme when exists
     */
    private function loadScheme()
    {
        $ref = new \ReflectionClass(get_called_class());

        $name = str_replace('Model', 'Scheme', $ref->getShortName());
        $ns = $ref->getNamespaceName() . '\\Scheme';

        $filename = BASEDIR . '/' . str_replace('\\', '/', $ns) . '/' . $name . '.php';

        if (file_exists($filename)) {
            $this->scheme = include ($filename);
        }
    }

    /**
     * Wrapper function for $this->appgetModel($model_name).
     *
     * There is a little difference in using this method than the long term. Not setting a model name
     * means, that you get a new instance of the currently used model.
     *
     * @param string $model_name
     *            Optional: When not set the name of the current model will be used
     *
     * @return Model
     */
    final public function getModel($model_name = '')
    {
        if (empty($model_name)) {
            $model_name = $this->getName();
        }

        return $this->app->getModel($model_name);
    }

    /**
     * Creates a database connector
     *
     * @param string $resource_name
     *            Name of the registered db factory
     * @param string $prefix
     *            Optional table prefix.
     *
     * @return Db
     */
    final protected function getDbConnector($resource_name = 'db.default', $prefix = '')
    {
        if (! $this->di->exists($resource_name)) {
            Throw new ModelException(sprintf('A database service with name "%s" ist not registered', $resource_name));
        }

        /* @var $db Db */
        $db = $this->di->get($resource_name);

        if ($prefix) {
            $db->setPrefix($prefix);
        }

        return $db;
    }

    /**
     * Filters the fields value by using the set filter statements
     *
     * It is possible to filter the field with multiple filters.
     * This method uses filter_var_array() to filter the value.
     *
     * @return array
     */
    final protected function filter(array &$data, array $scheme = [])
    {
        if (empty($scheme)) {
            $scheme = $this->scheme;
        }

        // No filter in scheme? End here!
        if (empty($scheme['filter'])) {
            return $data;
        }

        $filter = [];

        // Get all filter
        foreach ($scheme['filter'] as $f => $r) {
            $filter[$f] = $r;
        }

        // No filter to use? End here!
        if (empty($filter)) {
            return $data;
        }

        // Run filter agains data
        $result = filter_var_array($data, $filter);

        // No result? End here!
        if (empty($result)) {
            return $data;
        }

        // Copy filtered results into data
        foreach ($result as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Validates data against the set validation rules
     *
     * Returns boolean true when successful validate without errors.
     *
     * @param array $skip
     *            Optional array of fieldnames to skip on validation
     *
     * @return boolean
     */
    protected function validate(array &$data, array $fields = [], $filter_before_validate = true, array $skip = [])
    {
        static $validator;

        // No fields provided?
        if (empty($fields)) {

            // When there is no scheme with fields in this model we have to end here
            if (empty($this->scheme['fields'])) {
                return;
            }

            // Otherwise use the fields list from models scheme
            $fields = $this->scheme['fields'];
        }

        // No fields, no validation
        if (empty($fields)) {
            return;
        }

        // Let's validate the data!
        foreach ($data as $key => $val) {

            // Skip field? Next please!
            if (in_array($key, $skip)) {
                continue;
            }

            // Run some filters prior to field validation?
            if ($filter_before_validate && ! empty($fields[$key]['filter'])) {

                if (! is_array($fields[$key]['filter'])) {
                    $fields[$key]['filter'] = (array) $fields[$key]['filter'];
                }

                foreach ($fields[$key]['filter'] as $filter) {

                    $options = [];

                    if (is_array($filter)) {
                        $options = $filter[1];
                        $filter = $filter[0];
                    }

                    $result = filter_var($val, $filter, $options);

                    if ($result === false) {
                        $this->addError($key, sprintf($this->text('validator.filter'), $filter));
                    }
                    else {
                        $data[$key] = $result;
                    }
                }
            }

            // No validation rules to call? Next please!
            if (empty($fields[$key]['validate'])) {
                continue;
            }

            // Only instantiate validator once
            if (empty($validator)) {
                $validator = new Validator();
            }

            if (! is_array($fields[$key]['validate'])) {
                $fields[$key]['validate'] = (array) $fields[$key]['validate'];
            }

            // Validate the value against the rules
            $validator->validate($val, $fields[$key]['validate']);

            // Errors on validation?
            if (! $validator->isValid()) {
                $this->errors[$key] = $validator->getResult();
            }
        }

        return $data;
    }

    /**
     * Checks for existing model errors and returns boolean true or false
     *
     * @return boolean
     */
    final public function hasErrors()
    {
        return ! empty($this->errors);
    }

    /**
     * Returns the models error array (can be empty)
     *
     * @return array
     */
    final public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Resets models error array
     */
    final public function resetErrors()
    {
        $this->errors = [];

        return $this;
    }

    final public function addError($key, $error)
    {
        $this->errors[$key][] = $error;

        return $this;
    }

    final protected function getDataFromScheme() {

        if (empty($this->scheme)) {
            Throw new ModelException('There is no scheme/fields in scheme in this model');
        }

        $data = [];

        foreach ($this->scheme['fields'] as $key => $field) {
            $data[$key] = !empty($field['default']) ? $field['default'] : '';
        }

        return $data;

    }
}
