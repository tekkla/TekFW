<?php
namespace Core\Lib\Content;

/**
 * BreadcrumbObject
 *
 * Class to use as element in Breadcrumb object
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2014
 */
class BreadcrumbObject
{
	/**
	 * Href url
	 * @var string
	 */
	private $href = '';

	/**
	 * Inner text
	 * @var string
	 */
	private $text = '';

	/**
	 * Title text
	 * @var string
	 */
	private $title = '';

	/**
	 * Active flag
	 * @var boolean
	 */
	private $active = false;

	/**
	 * Sets href to use in breadcrumb link
	 *
	 * @param string $href
	 *
	 * @return \Core\Lib\Content\BreadcrumbObject
	 */
	public function setHref($href)
	{
		$this->href = $href;

		return $this;
	}

	/**
	 * Sets title to use on breadcrumb
	 *
	 * @param string $title
	 *
	 * @return \Core\Lib\Content\BreadcrumbObject
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * Sets inner text
	 *
	 * @param string $text
	 *
	 * @return \Core\Lib\Content\BreadcrumbObject
	 */
	public function setText($text)
	{
		$this->text = $text;

		return $this;
	}

	/**
	 * Sets active flag to be used on control creation
	 *
	 * @param string $active
	 *
	 * @return \Core\Lib\Content\BreadcrumbObject
	 */
	public function setActive($active=true)
	{
		$this->active = $active;

		return $this;
	}

	/**
	 * Returns set href
	 *
	 * @return string
	 */
	public function getHref()
	{
		return $this->href;
	}

	/**
	 * Returns set title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Returns set text
	 *
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * Returns active flag
	 *
	 * @return boolean
	 */
	public function getActive()
	{
		return $this->active;
	}
}
