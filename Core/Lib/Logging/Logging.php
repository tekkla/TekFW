<?php
namespace Core\Lib\Logging;

use Core\Lib\Data\Container;
use Core\Lib\Data\Adapter\Database;
use Core\Lib\Data\DataAdapter;
/**
 *
 * @author mzorn
 *
 */
class Logging
{
    /**
     *
     * @var Database
     */
    private $adapter;

    public function __construct(DataAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function log($text, $type='general')
    {
        $time = time();

        $message = New Container();

        $message['text'] = $text;
        $message['type'] = $type;
        $message['logdate'] = date('Y-m-d H:i:s', $time);
        $message['logstamp'] = $time;
        $message['client'] = $_SERVER['HTTP_USER_AGENT'];
        $message['ip'] = $_SERVER['REMOTE_ADDR'];
        $message['url'] = $_SERVER['REQUEST_URI'];

        $this->saveLog($message);
    }


    public function general($text) {

        $this->log($text);

        return $this;

    }

    public function app($text, $app)
    {
        $this->log($text, $app);

        return $this;
    }

    public function security($text)
    {
        $this->log($text, 'security');

        return $this;
    }

    public function error($text)
    {
        $this->log($text, 'error');

        return $this;
    }

    public function user($text)
    {
        $this->log($text, 'user');

        return $this;
    }

    public function language($text)
    {
        $this->log($text, 'language');

        return $this;
    }

    public function database($text)
    {
        $this->log($text, 'database');

        return $this;
    }

    private function saveLog(Container $message)
    {
        $query = [
            'table' => 'logs',
            'data' => $message
        ];

        $this->adapter->query($query);
        $this->adapter->execute();
    }
}
