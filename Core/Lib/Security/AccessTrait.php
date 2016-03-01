<?php
namespace Core\Lib\Security;

/**
 * AccessTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
trait AccessTrait
{

    /**
     * Checks the current user to have access on the base of the provided permissions
     *
     * @param array $perms
     * @param boolean $force
     * @param string $app
     *
     * @throws SecurityException
     *
     * @return boolean
     */
    protected function checkAccess($permissions = [], $force = false, $app = '')
    {
        if (! property_exists($this, 'di')) {
            Throw new SecurityException('AccessTrait::checkAccess() method cannot work without access to DI service container. Make sure that the object using this trait has this property set.');
        }

        if (empty($app)) {

            if (! property_exists($this, 'app_name')) {

                if ($this instanceof \Core\Lib\Amvc\App) {
                    $app = $this->name;
                }
                elseif (property_exists($this, 'app')) {
                    $app = $this->app->getName();
                }
                else {
                    $app = 'Core';
                }
            }
            else {
                $app = $this->app_name;
            }
        }

        return $this->di->get('core.security.user.current')->checkAccess($permissions, $force, $app);
    }
}
