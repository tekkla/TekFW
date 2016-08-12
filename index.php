<?php
use Core\Framework\Core;

/**
 * Entry file for Core framework
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2016
 */

// Register composer classloader
require_once (__DIR__ . '/vendor/autoload.php');

// Create new Core instance
$core = new Core(__DIR__);

// Run it, baby!
$core->bootstrap();