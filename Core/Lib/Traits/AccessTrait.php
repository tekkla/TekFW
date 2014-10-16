<?php
namespace Core\Lib\Traits;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 *
 */
trait AccessTrait
{
	protected function checkAccess($perms = [], $force = false)
	{
		return $this->di['core.sec.security']->checkAccess($perms, $force);
	}
}
