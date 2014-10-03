<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Abstracts\HtmlAbstract;

// Check for direct file access
if (! defined('TEKFW'))
    die('Cannot run without TekFW framework...');

/**
 * Creates a section html object
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Lib
 * @license MIT
 * @copyright 2014 by author
 */
class Section extends HtmlAbstract
{

    protected $element = 'section';
}

