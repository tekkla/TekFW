<?php
namespace Core\Lib\Security;

class Permission
{

    /**
     *
     * @var array
     */
    private $permissions = [];

    /**
     * Adds one app permission
     *
     * @param string $app_name
     *            Name of app
     * @param string $permission
     *            Name of permission
     *
     * @throws SecurityException
     *
     * @return \Core\Lib\Security\Permission
     */
    public function addPermission($app_name, $permission)
    {
        if (! empty($this->permissions[$app_name]) && in_array($permission, $this->permissions[$app_name])) {
            Throw new SecurityException(sprintf('There is already a registered permission "%s" for app "%s"', $permission, $app_name));
        }

        $this->permissions[$app_name][] = $permission;

        return $this;
    }

    /**
     * Sets a list of permissions of an app by overwritin already existing permissions of this app.
     *
     * @param string $app_name
     *            Name of app
     * @param array $permissions
     *            Array with permission names
     *
     * @return \Core\Lib\Security\Permission
     */
    public function setPermissions($app_name, array $permissions)
    {
        $this->permissions[$app_name] = $permissions;

        return $this;
    }

    /**
     * Returns all stored permissions
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Returns all stored permissions of an app.
     * Returns boolean false when the app has no permissions.
     *
     * @param string $app_name
     *            Name of app
     *
     * @return boolean|array
     */
    public function getAppPermissions($app_name)
    {
        if (empty($this->permissions[$app_name])) {
            return false;
        }

        return $this->permissions[$app_name];
    }

    /**
     * Checks for existance of an app permission.
     *
     * @param string $app_name
     *            Name of app
     * @param string $permission
     *            Name of permission
     *
     * @return boolean
     */
    public function exists($app_name, $permission)
    {
        if (empty($this->permissions[$app_name])) {
            return false;
        }

        return in_array($permission, $this->permissions[$app_name]);
    }
}

