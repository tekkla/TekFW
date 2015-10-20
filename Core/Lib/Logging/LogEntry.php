<?php
namespace Core\Lib\Logging;

/**
 * LogEntry.php
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
abstract class LogEntry
{

    private $date_time;

    private $timestamp;

    protected $type = 'general';

    private $message;

    protected $state = 0;

    private $id_user;

    private $url;

    private $client;


    public function __construct()
    {
        $now = time();

        $this->date_time = date('Y-m-d H:i:s', $now);
        $this->timestamp = $now;
    }

}
