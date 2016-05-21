<?php
namespace Core\Asset;

/**
 * AssetFileHandlerInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface AssetFilehandlerInterface
{

    public function setFilename($filename);

    public function setTTL($ttl);

    public function setContent($content);

    public function write();

    public function checkTTL();
}
