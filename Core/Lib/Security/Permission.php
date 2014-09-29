<?php
namespace Core\Lib\Security;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 *
 */
class Permission
{

	static $permissions = [];

	/**
	 */
	function __construct()
	{}

	public function addPermission($appname, $permissions = [])
	{
		if ($permissions) {

			if (! is_array($permissions)) {
				$var = (array) $permissions;
			}

			foreach ($permissions as $perm) {
				self::$permissions[$appname] = $perm;
			}
		}
	}

	public function check($permission = [])
	{
		// Optimistic check. No permissions to check means check is ok
		if (! $permission) {
			return true;
		}

		// Administrators are supermen :P.
		if ($this->xxx['is_admin']) {
			return true;
		}

		if (! is_array($permission)) {
			$permission = (array) $permission;
		}

		// Check
		return count(array_intersect($permission, self::$permissions['permissions'])) != 0 ? true : false;
	}

	public function getPermmissions()
	{
		return self::$permissions;
	}
}
