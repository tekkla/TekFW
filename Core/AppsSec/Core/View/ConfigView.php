<?php
namespace Core\AppsSec\Admin\View;

use Core\Lib\Amvc\View;
if (! defined('TEKFW'))
    die('Cannot run without TekFW framework...');

/**
 * Admin Config view
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage AppSec\Admin
 */
final class ConfigView extends View
{

    public function Config()
    {
		echo '<h' . ->icon . '&nbsp;' . ->app_name . '</h' . ->
        form;
    }
}

