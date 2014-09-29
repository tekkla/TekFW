<?php
namespace Core\Lib\Content;

/**
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 *
 */
class LinktreeElement
{

	private $url;
	private $tile;
	private $text;

	public function setUrl($url)
	{
		$this->url = $url;
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


	public function getUrl()
	{
		return $this->url;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getText()
	{
		return $this->text;
	}
}
