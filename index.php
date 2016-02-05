<?php
/**
 * Entry file for Core framework.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */

// Absolute path to site
define('BASEDIR', __DIR__);

// Include Core classfile
require_once (BASEDIR . '/Core/Lib/Core.php');

// Create new Core instance
$core = new Core();

// Run it, baby!
$core->run();