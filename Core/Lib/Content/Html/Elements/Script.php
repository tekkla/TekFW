<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Abstracts\HtmlAbstract;

/**
 * Div Html Element
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package TekFW
 * @subpackage Html\Elements
 * @license MIT
 * @copyright 2014 by author
 */
class Script extends HtmlAbstract
{

	protected $element = 'script';

	public function setType($type)
	{
		$this->attribute['type'] = $type;

		return $this;
	}

	public function setSrc($src)
	{
		$this->attribute['src'] = $src;

		return $this;
	}

	public function setCharset($charset)
	{
		$this->attribute['charset'] = $charset;

		return $this;
	}

	public function setFor($for)
	{
		$this->attribute['for'] = $for;

		return $this;
	}

	public function setDefer()
	{
		$this->addAttribute('defer');

		return $this;
	}

	public function setAsync()
	{
		$this->addAttribute('async');

		return $this;
	}
}
