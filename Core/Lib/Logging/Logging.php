<?php
namespace Core\Lib\Logging;

use Core\Lib\Data\Container;
use Core\Lib\Data\Adapter\Database;
use Core\Lib\Data\DataAdapter;
use Core\Lib\Http\Session;

/**
 * Logging.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
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

    private function log($text, $type = 'general', $code = 0)
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
        $message['code'] = $code;

        $this->saveLog($message);
    }

    /**
     * Generates a general log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function general($text, $code = 0)
    {
        $this->log($text, 'general', $code);

        return $this;
    }

    /**
     * Generates a system log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function core($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a app log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function app($text, $app, $code = 0)
    {
        $this->log($text, $app, $code);

        return $this;
    }

    /**
     * Generates a general security log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function security($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a suspicious log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function suspicious($text, $code = 0)
    {
        $this->log($text, 'security::suspicious', $code);

        return $this;
    }

    /**
     * Generates a ban log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function ban($text)
    {
        $this->log($text, __FUNCTION__, - 1);

        return $this;
    }

    /**
     * Generates a error log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function error($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a user log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function user($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a language log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function language($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a database log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function database($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a info log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function info($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a warning log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function warning($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a danger log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function danger($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a success log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function success($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a login log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function login($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a logout log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function logout($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Generates a file log message
     *
     * @param string $text Text to log
     * @param number $code Flexible code flag
     */
    public function file($text, $code = 0)
    {
        $this->log($text, __FUNCTION__, $code);

        return $this;
    }

    /**
     * Saves logentry to db
     *
     * @param Container $message
     */
    private function saveLog(Container $message)
    {
        $this->adapter->qb([
            'table' => 'logs',
            'data' => $message
        ], true);
    }

    /**
     * Returns the number of ban entires in the log for an IP address.
     *
     * @param string $ip IP address to check
     *
     * @return number
     */
    public function countBanLogEntries($ip)
    {
        $this->adapter->qb([
            'table' => 'logs',
            'fields' => 'COUNT(ip)',
            'filter' => 'ip=:ip AND type="ban"',
            'params' => [
                ':ip' => $ip
            ]
        ]);

        return $this->adapter->value();
    }

    /**
     * Returns the date of last ban log entry for an IP address.
     *
     * @param string $ip IP address to check
     *
     * @return string
     */
    public function getDateOfLastBanLogEntry($ip)
    {
        $query = [
            'table' => 'logs',
            'fields' => 'logdate',
            'filter' => 'ip=:ip AND type="ban"',
            'params' => [
                ':ip' => $ip
            ],
            'order' => 'logstamp DESC',
            'limit' => 1
        ];
    }
}
