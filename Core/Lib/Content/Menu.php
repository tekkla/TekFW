<?php
namespace Core\Lib\Content;

/**
 * Handles menuhandler actions.
 * Gives r/w access to menu_buttons.
 * Checks for menu handler method in handler app and throws error if method is missing.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class Menu
{
	private $root_items = [];

	public function __construct()
	{
	}

	/**
	 * Ajax refresh of menu
	 */
	public function refreshMenu($menu_item_name)
	{
		// Create ajax command to refresh #main_menu
		$this->ajax->command([
			'selector' => '#' . $menu_item_name,
			'args' => $this->compile(),
			'fn' => 'html',
			'type' => 'dom'
		]);
	}

	public function createRootItem($name, $text, $url=null)
	{
		return $this->root_items[$name] = $this->createMenuItem($name, $text, $url);
	}

	/**
	 * Creates a new menu item with the given arguments, adds it to the menu item
	 * storage and returns a reference to the item in storage
	 * @param string $name
	 * @param string $text
	 * @param string $url
	 * @param boolean $is_root
	 * @return MenuItem
	 */
	public function createMenuItem($name, $text, $url=null)
	{
		$menu_item = new MenuItem();
		$menu_item->setName($name);
		$menu_item->setText($text);

		if ($url !== null){
			$menu_item->setUrl($url);
		}

		return $menu_item;
	}

	/**
	 * Method to add a menu item to the appropriate item storage by checking
	 * the root property of the item.
	 *
	 * @param MenuItem $menu_item
	 * @return MenuItem
	 */
	public function &addMenuItem(MenuItem &$menu_item)
	{
		// Determine which type of item we have. Root or child?
		$storage = $menu_item->isRoot() ? 'root_items' : 'menu_items';

		// Store menu item
		$this->{$storage}[$menu_item->getName()] = $menu_item;

		// And return a reference to the stored items
		return $this->{$storage}[$menu_item->getName()];
	}

	/**
	 * Compiles the menu itemsand creates an html menu as output
	 */
	public function getMenu()
	{
		return $this->root_items;
	}
}
