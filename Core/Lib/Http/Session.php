<?php
namespace Core\Lib\Http;

use Core\Lib\Data\DataAdapter;

/**
 * Session object class
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class Session
{

    /**
     *
     * @var Database
     */
    private $adapter;

    /**
     * Constructor
     *
     * @param DataAdapter $adapter
     */
    public function __construct(DataAdapter $adapter)
    {
        $this->adapter = $adapter;

        // Lifetime auf eine Stunde setzen
        ini_set('session.gc_maxlifetime', 3600);

        // gc mit einer Wahrscheinlichkeit von 1% aufrufen
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);

        // Set handler to overide SESSION
        session_set_save_handler([
            $this,
            "open"
        ], [
            $this,
            "close"
        ], [
            $this,
            "read"
        ], [
            $this,
            "write"
        ], [
            $this,
            "destroy"
        ], [
            $this,
            "gc"
        ]);
    }

    /**
     * Access on data stored in session.
     *
     * @param string $key
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        else {
            Throw new \InvalidArgumentException('Session key "' . $key . '" not found.');
        }
    }

    /**
     * Stores data in session under the set key
     *
     * @param string $key
     * @param mixed $val
     *
     * @return Session
     */
    public function set($key, $val)
    {
        $_SESSION[$key] = $val;

        return $this;
    }

    /**
     * Adds data to existing data in session.
     * Tries to find data by using the set key. Converts non array data
     * into an array. Adds set $val at end of data array.
     *
     * @param string $key
     * @param mixed $val
     *
     * @return \Core\Lib\Http\Session
     */
    public function add($key, $val)
    {
        if (! isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        else {
            if (! is_array($_SESSION[$key])) {
                $_SESSION[$key] = (array) $_SESSION[$key];
            }
        }

        $_SESSION[$key][] = $val;

        return $this;
    }

    /**
     * Checks for existing data by it's key.
     *
     * @param boolean $key
     */
    public function exists($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Removes data from session by it's key.
     *
     * @param string $key
     *
     * @return \Core\Lib\Http\Session
     */
    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }

        return $this;
    }

    /**
     * Init session
     */
    public function init()
    {
        // Start the session
        session_start();

        if (! isset($_SESSION['id_user'])) {
            $_SESSION['id_user'] = 0;
            $_SESSION['logged_in'] = false;
        }

        // Create session id constant
        define('SID', session_id());
    }

    /**
     * Open session
     */
    public function open()
    {
        // If successful return true
        return $this->adapter ? true : false;
    }

    /**
     * Close session
     */
    public function close()
    {
        // Close the database connection - If successful return true
        return $this->adapter->close() ? true : false;
    }

    /**
     * Read session
     */
    public function read($id_session)
    {
        // Set query
        $this->adapter->query([
            'tbl' => 'sessions',
            'fields' => 'data',
            'filter' => 'id_session = :id_session',
            'params' => [
                ':id_session' => $id_session
            ]
        ]);

        $data = $this->adapter->single();

        return $data ? $data['data'] : '';
    }

    /**
     * Write session
     */
    public function write($id_session, $data)
    {
        // Set query
        $this->adapter->query([
            'method' => 'REPLACE',
            'tbl' => 'sessions',
            'fields' => [
                'id_session',
                'access',
                'data'
            ],
            'params' => [
                ':id_session' => $id_session,
                ':access' => time(),
                ':data' => $data
            ]
        ]);

        // Attempt Execution - If successful return true
        return $this->adapter->execute() ? true : false;
    }

    /**
     * Destroy
     */
    public function destroy($id_session)
    {
        // Set query
        $this->adapter->query([
            'method' => 'DELETE',
            'tbl' => 'sessions',
            'filter' => 'id_session=:id_session',
            'params' => [
                'id_session' => $id_session
            ]
        ]);

        // Attempt execution - If successful return True
        return $this->adapter->execute() ? true : false;
    }

    /**
     * Garbage Collection
     */
    public function gc($max)
    {
        // Calculate what is to be deemed old
        $old = time() - $max;

        // Set query
        $this->adapter->query([
            'method' => 'DELETE',
            'tbl' => 'sessions',
            'filter' => 'access<:old',
            'params' => [
                ':old' => $old
            ]
        ]);

        // Attempt execution
        return $this->adapter->execute() ? true : false;
    }
}
