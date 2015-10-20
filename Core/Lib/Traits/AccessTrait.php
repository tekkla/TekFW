<?php
namespace Core\Lib\Traits;

use Core\Lib\Errors\Exceptions\RuntimeException;

/**
 * AccessTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait AccessTrait
{

    /**
     * Checks the current user to have access on the base of the provided permissions.
     *
     * @param array $perms
     * @param string $force
     *
     * @throws RuntimeException
     *
     * @return boolean
     */
    protected function checkAccess($perms = [], $force = false)
    {
        if (! property_exists($this, 'di')) {
            Throw new RuntimeException('TextTrait::txt() method cannot work without access to DI service container. Make sure that the object using this trait has this property set.');
        }

        return (bool) $this->di->get('core.sec.security')->checkAccess($perms, (bool) $force);
    }
}
