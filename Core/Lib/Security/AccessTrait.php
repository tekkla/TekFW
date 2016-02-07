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
     * @param boolena $force
     *
     * @throws RuntimeException
     *
     * @return boolean
     */
    protected function checkAccess($permissions = [], $force = false)
    {
        static $instance;

        if (! property_exists($this, 'di')) {
            Throw new SecurityException('AccessTrait::checkAccess() method cannot work without access to DI service container. Make sure that the object using this trait has this property set.');
        }

        if (! $instance instanceof Security) {
            $instance = $this->di->get('core.security');
        }

        return (bool) $instance->checkAccess($permissions, (bool) $force);
    }
}
