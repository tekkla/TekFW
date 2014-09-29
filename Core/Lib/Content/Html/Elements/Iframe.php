<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Abstracts\HtmlAbstract;

/**
 * Creates an iframe html object.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Element
 * @license MIT
 * @copyright 2014 by author
 */
class Iframe extends HtmlAbstract
{
	private $sandbox = [];
	protected $element = 'iframe';

	/**
	 * Factory pattern.
	 * @param string|Url $src
	 * Url of iframe. Accepts url as string and an url object.
	 * @return \Core\Lib\Content\Html\Elements\Iframe
	 */
	public static function factory($src)
	{
		$obj = new Iframe();
		$obj->setSrc($src);
		return $obj;
	}

	/**
	 * Sets src attribute of iframe..
	 * @param string|Url $src
	 * Url of iframe. Accepts url as string and an url object.
	 * @return \Core\Lib\Content\Html\Elements\Iframe
	 */
	public function setSrc($src)
	{
		$this->attribute['src'] = $src;
		return $this;
	}

	/**
	 * Sets the srcdoc attribute
	 * @param string $srcdoc
	 * Html code to show
	 * @return \Core\Lib\Content\Html\Elements\Iframe
	 */
	public function setSrcDoc($srcdoc)
	{
		$this->attribute['srcdoc'] = $srcdoc;
		return $this;
	}

	/**
	 * Adds a sandbox mode
	 * @param string $mode
	 * @throws NoValidParameterError
	 * @return \Core\Lib\Content\Html\Elements\Iframe
	 */
	public function addSandboxMode($mode)
	{
		$modes = array(
			'',
			'allow-forms',
			'allow-same-origin',
			'allow-scripts',
			'allow-top-navigation'
		);

		if (!in_array($mode, $modes))
			Throw new \InvalidArgumentException('Wrong sanbox mode for iFrame element.', 1000);

		if (!in_array($mode, $this->sandbox))
			$this->sandbox[] = $mode;

		return $this;
	}

	/**
	 * Sets the width attribute
	 * @param int $width
	 * @return \Core\Lib\Content\Html\Elements\Iframe
	 */
	public function setWidth($width)
	{
		$this->attribute['width'] = (int) $width;
		return $this;
	}

	/**
	 * Sets the height attribute
	 * @param int $height
	 * @return \Core\Lib\Content\Html\Elements\Iframe
	 */
	public function setHeight($height)
	{
		$this->attribute['height'] = (int) $height;
		return $this;
	}

	/**
	 * Sets the iframe to be seamless or not
	 * @param string $state
	 */
	public function setSeamless($state = true)
	{
		if ($state === true)
			$this->attribute['seamless'] = false;
		else
			$this->removeAttribute('seamless');

		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Core\Lib\Abstracts\HtmlAbstract::build()
	 */
	public function build()
	{
		if ($this->sandbox)
			$this->attribute['sandbox'] = implode(' ', $this->sandbox);

		return parent::build();
	}
}
