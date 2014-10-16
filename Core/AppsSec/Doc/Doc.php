<?php
namespace Core\AppsSec\Doc;

use Core\Lib\Amvc\App;

// Check for direct file access
if (! defined('TEKFW'))
    die('Cannot run without TekFW framework...');

/**
 * Mainclass for secured Doc app
 * This app handles all admin stuff about the framework and apps
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package Doc (AppSec)
 * @subpackage Main
 */
class Doc extends App
{

    protected $secure = true;

    protected $css_file = true;

    protected $js_file = true;

    // public $js = true;
    protected $language = true;

    public $routes = array(
        array(
            'name' => 'url',
            'route' => '/',
            'ctrl' => 'Main',
            'action' => 'Index'
        ),
        array(
            'method' => 'GET|POST',
            'name' => 'docedit',
            'route' => '/[i:id_document]?/edit',
            'ctrl' => 'Document',
            'action' => 'Edit'
        )
    );
}

