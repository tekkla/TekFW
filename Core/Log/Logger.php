<?php
namespace Core\Log;

/**
 * Logger.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Logger
{
    private $streams = [];

    /**
     *
     * @param unknown $id
     * @param LogstreamAbstract $stream
     */
    public function register($id, LogstreamAbstract $stream)
    {
        $this->streams[$id] = $stream;

        return $this;
    }

    /**
     *
     * @param string $id Stream id
     *
     * @return LogstreamAbstract
     */
    public function stream($id)
    {
        return $this->stream($id);
    }

    public function create($id, $stream_type) {

    }
}
