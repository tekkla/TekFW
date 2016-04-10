<?php
namespace Core\IO;

/**
 * IO.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class IO
{

    /**
     * Download Service
     *
     * @var Download
     */
    public $download;

    /**
     * Files Service
     *
     * @var Files
     */
    public $files;

    public function __construct(Files $files, Download $download)
    {
        $this->files = $files;
        $this->download = $download;
    }
}
