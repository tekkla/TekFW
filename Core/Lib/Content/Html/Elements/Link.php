<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Content\Html\HtmlAbstract;

/**
 * Link Html Object
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package TekFW
 * @subpackage Html\Elements
 * @license MIT
 * @copyright 2014 by author
 */
class Link extends HtmlAbstract
{

	protected $element = 'link';

	public function setRel($rel)
	{
		$this->attribute['rel'] = $rel;

		return $this;
	}

	public function setType($type)
	{
		$this->attribute['type'] = $type;

		return $this;
	}

	public function setHref($href)
	{
		$this->attribute['href'] = $href;

		return $this;
	}

	public function setTtitle($title)
	{
		$this->attribute['title'] = $title;

		return $this;
	}
}
