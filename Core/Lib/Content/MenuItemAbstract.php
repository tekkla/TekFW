<?php
namespace Core\Lib\Content;

/**
 *
 * @author mzorn
 *
 */
abstract class MenuItemAbstract
{
    /**
     * Items childs.
     *
     * @var array
     */
    private $items = [];

    /**
     * Method to add a menu item as child.
     *
     * @param MenuItem $menu_item
     *
     * @return MenuItem Reference to the added menu item.
     */
    public function &addItem(MenuItem $menu_item)
    {
        $this->items[$menu_item->getName()] = $menu_item;

        return $this->items[$menu_item->getName()];
    }

    /**
     * Creates new menuitem and adss it to current menu items child list.
     *
     * @param string $name Internal name of item
     * @param string $text Text to show
     * @param string $url Optional url for linking
     *
     * @return MenuItem Reference to the created child item.
     */
    public function &createItem($name, $text, $url = null)
    {
        $menu_item = new MenuItem();

        $menu_item->setName($name);
        $menu_item->setText($text);

        if ($url !== null) {
            $menu_item->setUrl($url);
        }

        $this->items[$name] = $menu_item;

        return $this->items[$name];
    }

    /**
     * Checks for exisitng childs and returns a boolean result.
     *
     * @return boolean
     */
    public function isParent()
    {
        return ! empty($this->items);
    }

    /**
     * Returns all child items.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
}
