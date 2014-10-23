<?php
namespace Core\Lib\Content;

/**
 *
 * @author Michael
 *
 */
class BreadcrumbObject
{
	private $href = '';

	private $text = '';

	private $title = '';

	private $active = false;

	public function setHref($href)
	{
		$this->href = $href;

		return $this;
	}

	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	public function setText($text)
	{
		$this->text = $text;

		return $this;
	}

	public function setActive($active=true)
	{
		$this->active = $active;

		return $this;
	}

	public function getHref()
	{
		return $this->href;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getText()
	{
		return $this->text;
	}

	public function getActive()
	{
		return $this->active;
	}
}
