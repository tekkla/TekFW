<?php
namespace Core\Lib\Traits;

use Core\Lib\Amvc\App;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 *
 */
trait TextTrait
{

	/**
	 * Get text from language service.
	 *
	 * @param string $key
	 * @param string $app
	 *
	 * @return string
	 */
	public function txt($key)
	{
		if ($this instanceof App) {
			$app = $this->name;
		} elseif (property_exists($this, 'app')) {
			$app = $this->app->getName();
		} else {
			$app = 'Core';
		}

		return $this->di['core.content.lang']->getTxt($key, $app);
	}
}
