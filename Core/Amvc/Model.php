<?php
namespace Core\Amvc;

use Core\Data\Connectors\Db\Db;
use Core\Security\Security;
use Core\Data\Validator\Validator;
use Core\Amvc\ModelException;
use Core\Traits\ArrayTrait;
use Core\Router\UrlTrait;
use Core\Language\TextTrait;
use Core\Cfg\AppCfg;

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
    use ArrayTrait;

    /**
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
     *
     * @var Security
     */
    protected $security;

    /**
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
    final public function __construct($name, App $app, Security $security, AppCfg $cfg)
    {
        // Set Properties
        $this->name = $name;
        $this->app = $app;
        $this->security = $security;
        $this->cfg = $cfg;
    }

    /**
     * Wrapper function for $this->app->getModel($model_name).
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

        if (empty($scheme['filter'])) {
            return $data;
        }

        $filter = [];

        foreach ($scheme['filter'] as $f => $r) {
            $filter[$f] = $r;
        }

        if (empty($filter)) {
            return $data;
        }

        $result = filter_var_array($data, $filter);

        if (empty($result)) {
            return $data;
        }

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

        if (empty($fields)) {

            if (empty($this->scheme['fields'])) {
                return;
            }

            $fields = $this->scheme['fields'];
        }

        if (empty($fields)) {
            return;
        }

        foreach ($data as $key => $val) {

            if (in_array($key, $skip)) {
                continue;
            }

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

            if (empty($fields[$key]['validate'])) {
                continue;
            }

            if (empty($validator)) {
                $validator = new Validator();
            }

            if (! is_array($fields[$key]['validate'])) {
                $fields[$key]['validate'] = (array) $fields[$key]['validate'];
            }

            $validator->validate($val, $fields[$key]['validate']);

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

    /**
     * Adds an error for a specific field (or global) to the models errorstack
     *
     * @param string $key
     *            Fieldname the error belongs to. Use '@' to add a global and non field specific error. @-Errors will be
     *            recognized by FormDesigner and shown on top of the form.
     * @param string $error
     *            The error text to add
     *
     * @return \Core\Amvc\Model
     */
    final public function addError($key, $error)
    {
        $this->errors[$key][] = $error;

        return $this;
    }

    /**
     * Creates an associative array based on the fields and default values of the scheme
     *
     * Throws an exception when calling this method without a scheme or with a scheme but with missing fieldlist in it.
     *
     * @throws ModelException
     *
     * @return array
     */
    final protected function getDataFromScheme()
    {
        if (empty($this->scheme) || empty($this->scheme['fields'])) {
            Throw new ModelException('There is no scheme/fields in scheme in this model');
        }

        $data = [];

        foreach ($this->scheme['fields'] as $key => $field) {
            $data[$key] = ! empty($field['default']) ? $field['default'] : '';
        }

        return $data;
    }
}
