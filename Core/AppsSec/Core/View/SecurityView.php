<?php

namespace Core\AppsSec\Core\View;

use Core\Lib\Amvc\View;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 *
 */
final class SecurityView extends View
{

	public function Login()
	{
		echo $this->form;
	}

}

