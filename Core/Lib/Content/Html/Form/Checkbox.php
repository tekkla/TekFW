<?php
namespace Core\Lib\Content\Html\Form;

// Check for direct file access
if (! defined('TEKFW'))
    die('Cannot run without TekFW framework...');

/**
 * Checkbox Form Element
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Form
 * @license MIT
 * @copyright 2014 by author
 */
class Checkbox extends Input
{

    protected $type = 'checkbox';

    protected $element = 'input';

    protected $data = array(
        'control' => 'checkbox'
    );
}

