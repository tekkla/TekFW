<?php
namespace Core\Lib\Logging;

use Core\Lib\Data\Container\Container;
use Core\Lib\Data\Connectors\Db\Db;
use Core\Lib\Http\Session;

/**
 * Logging.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Logging
{

    /**
     *
     * @var Db
     */
    private $db;

    /**
     *
     * @var Session
     */
    private $session;

    /**
     * Constructor
     *
     * @param Db $db
     *            Db dependency
     * @param Session $session
     *            Session dependency
     */
    public function __construct(Db $db, Session $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Creates log entry in Db and return log id
     *
     * @param string $text
     *            Logtext
     * @param string $type
     *            Optional logtype
     * @param number $code
     *            Optional logcode
     *            
     * @return integer
     */
    public function log($text, $type = 'general', $code = 0)
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
        
        $this->db->qb([
            'table' => 'logs',
            'data' => $message
        ], true);
        
        return $this->db->lastInsertId();
    }

    /**
     * Generates a general log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function general($text, $code = 0)
    {
        return $this->log($text, 'general', $code);
    }

    /**
     * Generates a system log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function core($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a app log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function app($text, $app, $code = 0)
    {
        return $this->log($text, $app, $code);
    }

    /**
     * Generates a general security log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function security($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a suspicious log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function suspicious($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a ban log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function ban($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a error log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function error($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a user log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function user($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a language log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function language($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a database log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function database($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a info log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function info($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a warning log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function warning($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a danger log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function danger($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a success log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function success($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a login log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function login($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a logout log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function logout($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Generates a file log message
     *
     * @param string $text
     *            Text to log
     * @param number $code
     *            Flexible code flag
     *            
     * @return integer
     */
    public function file($text, $code = 0)
    {
        return $this->log($text, __FUNCTION__, $code);
    }

    /**
     * Returns the number of ban entires in the log for an IP address.
     *
     * @param string $ip
     *            IP address to check
     *            
     * @return number
     */
    public function countBanLogEntries($ip, $duration)
    {
        $this->db->qb([
            'table' => 'logs',
            'fields' => 'COUNT(ip)',
            'filter' => 'ip=:ip AND type="ban" AND logstamp+:duration > :expires',
            'params' => [
                ':ip' => $ip,
                ':duration' => $duration,
                ':expires' => time()
            ]
        ]);
        
        return $this->db->value();
    }

    /**
     * Returns the timestamp from log when ban got active for this ip
     *
     * @param string $ip
     *            IP address to check
     *            
     * @return number
     */
    public function getBanActiveTimestamp($ip)
    {
        $this->db->qb([
            'table' => 'logs',
            'fields' => 'logstamp',
            'filter' => 'ip=:ip AND type="ban" AND code=2',
            'params' => [
                ':ip' => $ip
            ],
            'order' => 'logstamp DESC',
            'limit' => 1
        ]);
        
        $data = $this->db->value();
        
        return $data ? $data : 0;
    }
}
