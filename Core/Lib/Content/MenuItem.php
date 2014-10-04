<?php
namespace Core\Lib\Content;

/**
 *
 * @author michael
 *
 */
class MenuItem
{

	/**
	 * Storage for used names
	 *
	 * @var array
	 */
	private static $used_names = [];

	/**
	 * Unique name of this menu item
	 * This name will be used as DOM id to address a menu item or, in case of
	 * sub_buttons, a complete menu tree.
	 * It is also important to know, that
	 * this name is used to attach menu items to each other in form of a
	 * parent >> child herachie
	 *
	 * @var string
	 */
	private $name = '';

	/**
	 * Text to be used as linktext
	 *
	 * @var string
	 */
	private $text = '';

	/**
	 * Text to be used as link title attribute
	 *
	 * @var string
	 */
	private $title = '';

	/**
	 * Text to be used as link alt attribute
	 *
	 * @var string
	 */
	private $alt = '';

	/**
	 * Strin to be used as link href attribute
	 *
	 * @var string
	 */
	private $url = '';

	/**
	 * Flags the menu item to be a root element or not.
	 *
	 * @var boolean
	 */
	private $is_root = false;

	/**
	 * String to be uses as link css class attribute.
	 *
	 * @var string
	 */
	private $css = '';

	/**
	 * Name of menu item this item is a child of.
	 *
	 * @var string
	 */
	private $parent = '';

	private $childs = [];

	/**
	 * Constructor
	 */
	public function _construct()
	{}

	/**
	 * Method to add a menu item as child
	 * @param MenuItem $menu_item
	 */
	public function addChild(MenuItem $menu_item) {
		$this->childs[$menu_item->getName()] = $menu_item;
	}

	/**
	 * Returns the name of menu item
	 *
	 * @return string $name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Sets name of menu item.
	 * This name has to be unique. Using an already set name will result in a
	 * InvalidArgumentException().
	 *
	 * @param string $name
	 * @return MenuItem
	 */
	public function setName($name)
	{
		if (in_array($name, self::$used_names)) {
			Throw new \InvalidArgumentException('The menuitem name "' . $name . '" is already in use.');
		}

		$this->name = $name;

		// Store name to get safe that it's only used once
		self::$used_names[] = $name;

		return $this;
	}

	/**
	 *
	 * @return the $text
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * Sets text to be used as linktxt
	 *
	 * @param string $text
	 * @return MenuItem
	 */
	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}

	/**
	 *
	 * @return the $alt
	 */
	public function getAlt()
	{
		return $this->alt;
	}

	/**
	 * Sets text to be used as link alt attribute
	 *
	 * @param string $alt
	 * @return MenuItem
	 */
	public function setAlt($alt)
	{
		$this->alt = $alt;
		return $this;
	}

	/**
	 * Sets or gets the is_root flag.
	 * When no argument provieded it will return the state of is_root property.
	 * With argument the same property is set.
	 *
	 * @param $bool Optional is_root argument
	 * @return bool|MenuItem
	 */
	public function isRoot($bool = null)
	{
		if (isset($bool)) {
			$this->is_root = is_bool($bool) ? $bool : false;
			return $this;
		} else {
			return $this->is_root;
		}
	}

	/**
	 * Returns the name of parent menu item this item is a child of.
	 * Return value will be booloean false when no parent is set.
	 *
	 * @return string|boolean
	 */
	public function getParent()
	{
		return $this->parent ? $this->parent : false;
	}

	/**
	 * Sets name of menu item this item is a child of.
	 *
	 * @param string $parent
	 * @return MenuItem
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 *
	 * @return the $title
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Sets text to be used as link title attribute
	 * @param string $title
	 * @return MenuItem
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * Returns the set url of the item. Will be boolean false when no
	 * @return the $url
	 */
	public function getUrl()
	{
		return $this->url ? $this->url : false;
	}

	/**
	 * Sets url to be used for menu link
	 * @param string $url
	 * @return MenuItem
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * Returns set css classes
	 * @return string
	 */
	public function getCss()
	{
		return $this->css;
	}

	/**
	 * Sets css classes to be used in menulink. Argument can an array and will
	 * be transformed into a string
	 * @param string $css
	 * @return MenuItem
	 */
	public function setCss($css)
	{
		if (is_array($css))
			$css = implode(' ', $css);

		$this->css = $css;

		return $this;
	}
}
