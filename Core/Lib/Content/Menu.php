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
