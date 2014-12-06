<?php
namespace Core\Lib\Amvc;

use Core\Lib\Data\Adapter\Db\Connection;
use Core\Lib\Data\DataAdapter;

/**
 * Model class
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014  by author
 * @license MIT
 * @version 1.0
 */
class Model extends MvcAbstract
{
	use \Core\Lib\Traits\SerializeTrait;
	use \Core\Lib\Traits\ArrayTrait;
	use \Core\Lib\Traits\ConvertTrait {
		\Core\Lib\Traits\SerializeTrait::isSerialized insteadof \Core\Lib\Traits\ConvertTrait;
	}

	/**
	 * MVC component type
	 *
	 * @var string
	 */
	protected $type = 'Model';

	/**
	 * Constructor
	 */
	final public function __construct($name, App $app)
	{
		// Set Properties
		$this->name = $name;
		$this->app = $app;
	}

	/**
	 * Access to the apps config.
	 * Without any paramter set this method returns the complete config.
	 * With only key set, it returns the value associated with it.
	 * Set key and value, and the config will be updated.
	 *
	 * @param string $key
	 * @param string $val
	 */
	public final function cfg($key = null, $val = null)
	{
		return $this->app->cfg($key, $val);
	}

	/**
	 * Add an error to the models errorlist.
	 * If you want do set global and not field related errors, set $fld to '@'.
	 *
	 * @param string $fld
	 * @param string $msg
	 */
	public final function addError($fld, $msg)
	{
		if (! isset($this->errors[$fld])) {
			$this->errors[$fld] = [];
		}

		if (! is_array($msg)) {
			$msg = (array) $msg;
		}

		foreach ($msg as $val) {
			$this->errors[$fld][] = $val;
		}

		return $this;
	}

	/**
	 * Checks errors in the model and returns true or false
	 *
	 * @return boolean
	 */
	public final function hasErrors()
	{
		return ! empty($this->errors);
	}

	/**
	 * Checks for no errors in the model and returns true or false
	 *
	 * @return boolean
	 */
	public final function hasNoErrors()
	{
		return empty($this->errors);
	}

	/**
	 * Returns the models errorlist
	 *
	 * @return array
	 */
	public final function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Wrapper function for $this->appgetModel($model_name).
	 * There is a little
	 * difference in using this method than the long term. Not setting a model name
	 * means, that you get a new instance of the currently used model.
	 *
	 * @param string $model_name Optional: When not set the name of the current model will be used
	 * @return Model
	 */
	public final function getModel($model_name = null)
	{
		if (! isset($model_name)) {
			$model_name = $this->getName();
		}

		return $this->app->getModel($model_name);
	}

	/**
	 * Creates a database Dataadapter object
	 *
	 * Uses default db DataAdapter when no Connection object is set.
	 *
	 * @param Connection $conn Optional Connection object
	 * @param string $prefix Optional table prefix.
	 * @param array $fields Optional field definition list to be used as container scheme
	 *
	 * @return \Core\Lib\Data\DataAdapter
	 */
	protected final function getDbAdapter(Connection $conn = null, $prefix = '', array $fields = [])
	{
		if ($conn === null) {
			$adapter = $this->di->get('db.default');

			if ($prefix) {
				$adapter->setPrefix($prefix);
			}
		} else {

			if (! $prefix) {
				$prefix = $this->di->get('db.default.prefix');
			}

			$adapter = new DataAdapter('db', [
				'conn' => $conn,
				'prefix' => $prefix
			]);
		}

		if ($fields) {
			$adapter->createContainer($fields);
		}

		return $adapter;
	}
}
