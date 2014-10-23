<?php
namespace Core\Lib\Content;

/**
 *
 * @author Michael
 *
 */
class Link
{
	private $links = [];

	public function setGeneric($attributes) {

		$this->links[] = $attributes;
	}

	public function setTouchIcon($href)
	{
		// Apple touch
		$this->links['apple-touch-icon']  = [
			'rel' => 'apple-touch-icon',
			'href' => $href
		];

		// Chrome
		$this->links['icon']  = [
			'rel' => 'icon',
			'sizes' => '196x196',
			'href' => $href
		];
	}

	public function setFavicon($href, $type)
	{
		$types = [
			'image/x-icon',
			'image/gif',
			'image/png'
		];

		if (! in_array($type, $types)) {
			Throw new \InvalidArgumentException('Type "' . $type . '" is no valid favicon image type. Valid types are ' . implode(', ', $types));
		}

		$this->links['canonical'] = [
			'rel' => 'shortcut icon',
			'href' => $href,
			'type' => $type
		];
	}

	public function setCanonicalUrl($href)
	{
		$this->links['canonical'] = [
			'rel' => 'canonical',
			'href' => $href,
		];
	}

	private function setLink($type, $title, $href)
	{
		$this->links[$type] = [
			'rel' => $type,
			'title' => $title,
			'href' => $href,
		];
	}

	public function setAuthor($title, $href)
	{
		$this->setLink('author', $title, $href);
	}

	public function setContents($title, $href)
	{
		$this->setLink('contents', $title, $href);
	}

	public function setIndex($title, $href)
	{
		$this->setLink('index', $title, $href);
	}

	public function setSearch($title, $href)
	{
		$this->setLink('search', $title, $href);
	}

	public function setHelp($title, $href)
	{
		$this->setLink('help', $title, $href);
	}

	public function setCopyright($title, $href)
	{
		$this->setLink('copyright', $title, $href);
	}

	public function setTop($title, $href)
	{
		$this->setLink('top', $title, $href);
	}

	public function setUp($title, $href)
	{
		$this->setLink('up', $title, $href);
	}

	public function setNext($title, $href)
	{
		$this->setLink('next', $title, $href);
	}

	public function setPrev($title, $href)
	{
		$this->setLink('prev', $title, $href);
	}

	public function setFirst($title, $href)
	{
		$this->setLink('first', $title, $href);
	}

	public function setLast($title, $href)
	{
		$this->setLink('last', $title, $href);
	}

	public function getLinkStack()
	{
		return $this->links;
	}
}
