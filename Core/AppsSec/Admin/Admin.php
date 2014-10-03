<?php
namespace Core\AppsSec\Admin;

// Uses classes
use Core\Lib\Amvc\App;
use Core\Lib\Url;
use Core\Lib\Txt;

/**
 * Mainclass for secured Admin app
 * This app handles all admin stuff about the framework and apps
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package Admin (AppSec)
 * @subpackage Main
 */
class Admin extends App
{

    /**
     * Inserts admin link into admin menu in menu buttons.
     * 
     * @param array $menu_buttons
     */
    public static function addAdminlink(&$menu_buttons)
    {
        if (! isset($menu_buttons['admin']))
            return;
        
        $menu_buttons['admin']['sub_buttons']['admin'] = array(
            'title' => Txt::get('framework_config'),
            'href' => Url::factory('admin_index')->getUrl(),
            'show' => true,
            'sub_buttons' => array(),
            'is_last' => false
        );
    }
}

