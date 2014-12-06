<?php
namespace Core\AppsSec\Admin\View;

use Core\Lib\Amvc\View;

/**
 * Admin Config view
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage AppSec\Admin
 */
final class ConfigView extends View
{

	public function Config()
	{
		echo '<h1>' . $this->icon . '&nbsp;' . $this->app_name . '</h1>' . $this->form;
	}
}

