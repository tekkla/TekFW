<?php
namespace Core\Lib\Content\Html;

use Core\Lib\Abstracts\HtmlAbstract;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 *
 */
class HtmlFactory
{

	use \Core\Lib\Traits\ArrayTrait;

	function __construct()
	{}

	/**
	 * Creates an html control / element / form element by using DI container instance method.
	 * Injects an instance of the HtmlFactory so the created html object can use it to create.
	 *
	 * @param string $class Short NS to used class like 'Controls\Button' or 'Elements\Div' or 'Form\Input'.
	 * @param array $args Optional assoc arguments array to be used as $html->$method($value) call.
	 *
	 * @throws \RuntimeException
	 *
	 * @return HtmlAbstract
	 */
	public function create($class, $args=[])
	{
		$class = '\Core\Lib\Content\Html\\' . $class;

		$html = $this->di->instance($class, 'core.content.html.factory');

		foreach ($args as $method => $arg) {

			if (! method_exists($html, $method)) {
				Throw new \RuntimeException('Html object has no "' . $method . '" method.');
			}

			if (is_array($arg))
			{
				if (!$this->isAssoc($arg)) {
					Throw new \InvalidArgumentException('Arrayed arguments for html objects created by HtmlFactory have to be associative.');
				}

				foreach ($arg as $attr => $val) {
					$html->$method($attr, $val);
				}
			}
			else {
				$html->$method($arg);
			}


		}

		return $html;
	}
}