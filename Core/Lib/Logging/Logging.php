<?php
namespace Core\Lib\Logging;

use Core\Lib\Data\Container;
use Core\Lib\Data\Adapter\Database;
use Core\Lib\Data\DataAdapter;
use Core\Lib\Http\Session;

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

    public function __construct(DataAdapter $adapter, Session $session)
    {
        $this->adapter = $adapter;
        $this->session = $session;
    }

    public function log($text, $type = 'general', $state = 0)
    {
        $time = time();

        $message = new Container();

        $message['text'] = $text;
        $message['type'] = $type;
        $message['logdate'] = date('Y-m-d H:i:s', $time);
        $message['logstamp'] = $time;
        $message['client'] = $_SERVER['HTTP_USER_AGENT'];
        $message['ip'] = $_SERVER['REMOTE_ADDR'];
        $message['url'] = $_SERVER['REQUEST_URI'];
        $message['id_user'] = $this->session->get('id_user');
        $message['state'] = $state;

        $this->saveLog($message);
    }

    /**
     * Generates a general log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function general($text, $state = 0)
    {
        $this->log($text, 'general', $state);

        return $this;
    }

    /**
     * Generates a system log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function core($text, $state = 0)
    {
        $this->log($text, __FUNCTION__, $state);

        return $this;
    }

    /**
     * Generates a app log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function app($text, $app, $state = 0)
    {
        $this->log($text, $app, $state);

        return $this;
    }

    /**
     * Generates a general security log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function security($text, $state = 0)
    {
        $this->log($text, __FUNCTION__, $state);

        return $this;
    }

    /**
     * Generates a suspicious log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function suspicious($text, $state = 0)
    {
        $this->log($text, 'security::suspicious', $state);

        return $this;
    }

    /**
     * Generates a error log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function error($text, $state = 0)
    {
        $this->log($text, __FUNCTION__, $state);

        return $this;
    }

    /**
     * Generates a user log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function user($text, $state = 0)
    {
        $this->log($text, __FUNCTION__, $state);

        return $this;
    }

    /**
     * Generates a language log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function language($text, $state = 0)
    {
        $this->log($text, __FUNCTION__, $state);

        return $this;
    }

    /**
     * Generates a database log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function database($text, $state = 0)
    {
        $this->log($text, __FUNCTION__, $state);

        return $this;
    }

    /**
     * Generates a info log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function info($text, $state = 0)
    {
        $this->log($text, __FUNCTION__, $state);

        return $this;
    }

    /**
     * Generates a warning log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function warning($text, $state = 0)
    {
        $this->log($text, __FUNCTION__, $state);

        return $this;
    }

    /**
     * Generates a danger log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function danger($text, $state = 0)
    {
        $this->log($text, __FUNCTION__, $state);

        return $this;
    }

    /**
     * Generates a success log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function success($text, $state = 0)
    {
        $this->log($text, __FUNCTION__, $state);

        return $this;
    }

    /**
     * Generates a login log message
     *
     * @param string $text Text to log
     * @param number $state Flexible state flag
     */
    public function login($text, $state = 0)
    {
        $this->log($text, __FUNCTION__, $state);

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
