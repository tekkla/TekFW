<?php
namespace Core\Lib\Security;

use Core\Lib\Data\DataAdapter;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 *
 */
class Permission
{

	/**
	 *
	 * @var array
	 */
	private static $permissions = [];

	/**
	 *
	 * @var DataAdapter
	 */
	private $adapter;

	public function __construct(DataAdapter $adapter)
	{
		$this->adapter = $adapter;
	}

	/**
	 * Adds one or more permissions to permissions list.
	 *
	 * @param string $app_name Name of permission related app
	 * @param array $permissions One or more permissions to add
	 */
	public function addPermission($app_name, $permissions = [])
	{
		if ($permissions) {

			if (! is_array($permissions)) {
				$permissions = (array) $permissions;
			}

			foreach ($permissions as $perm) {
				self::$permissions[] = $app_name . '_' . $perm;
			}
		}
	}

	/**
	 * Returns all or app related permissions
	 *
	 * @param string $app
	 *
	 * @return array
	 */
	public function getPermissions($app = '')
	{
		return $app ? self::$permissions[$app] : self::$permissions;
	}

	/**
	 * Loads all permissions from DB which are mathing the groups argument.
	 * Returns an empty array when groups argument is not set.
	 *
	 * @param unknown $group_id
	 *
	 * @return array
	 */
	public function loadPermission($groups = [])
	{
		// Queries without group IDs always results in an empty permission list
		if (empty($groups)) {
			return [];
		}

		// Convert group ID explicit into array
		if (! is_array($groups)) {
			$groups = (array) $groups;
		}

		// Create a prepared string and param array to use in query
		$prepared = $this->adapter->prepareArrayQuery('group', $groups);

		// Get and return the permissions
		$this->adapter->query('SELECT DISTINCT permission FROM {db_prefix}permissions WHERE id_group IN (' . $prepared['sql'] . ')');

		foreach ($prepared['values'] as $param => $value) {
			$this->adapter->bindValue($param, $value);
		}

		$this->adapter->execute();

		return $this->adapter->column();
	}
}
