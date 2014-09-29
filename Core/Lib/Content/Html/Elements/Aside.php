<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Abstracts\HtmlAbstract;

// Check for direct file access
if (!defined('TEKFW'))
	die('Cannot run without TekFW framework...');

/**
 * Aside Html Element
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Elements
 * @license MIT
 * @copyright 2014 by author
 */
class Aside extends HtmlAbstract
{
	protected $element = 'aside';
}

