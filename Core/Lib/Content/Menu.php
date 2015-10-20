<?php
namespace Core\Lib\Content;

/**
 * Menu.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Menu extends MenuItemAbstract
{
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
}
