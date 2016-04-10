<?php
namespace Core\Http;

use Core\Data\Connectors\Db\Db;

/**
 * Session.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Session
{

    /**
     *
     * @var Database
     */
    private $db;

    /**
     *
     * @var string
     */
    private $table = 'core_sessions';

    /**
     * Constructor
     *
     * Sets session garbage collector lifetime to one hour.
     * Sets run of garbage collector with a chance of 1%.
     * Sets custom database driven session handler
     *
     * @param Db $db
     *            Db dependency
     */
    public function __construct(Db $db)
    {
        $this->db = $db;

        ini_set('session.gc_maxlifetime', 3600);
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
     * Open session
     */
    public function open()
    {
        // If successful return true
        return $this->db ? true : false;
    }

    /**
     * Close session
     */
    public function close()
    {
        // Close the database connection - If successful return true
        return $this->db->close() ? true : false;
    }

    /**
     * Read session
     */
    public function read($id_session)
    {
        // Set query
        $this->db->qb([
            'table' => $this->table,
            'fields' => 'data',
            'filter' => 'id_session = :id_session',
            'params' => [
                ':id_session' => $id_session
            ]
        ]);

        $data = $this->db->single();

        return $data ? $data['data'] : '';
    }

    /**
     * Write session
     */
    public function write($id_session, $data)
    {
        // Set query
        $this->db->qb([
            'method' => 'REPLACE',
            'table' => $this->table,
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
        return $this->db->execute() ? true : false;
    }

    /**
     * Destroy
     */
    public function destroy($id_session)
    {
        // Set query
        $this->db->qb([
            'method' => 'DELETE',
            'table' => $this->table,
            'filter' => 'id_session=:id_session',
            'params' => [
                'id_session' => $id_session
            ]
        ]);

        // Attempt execution - If successful return True
        return $this->db->execute() ? true : false;
    }

    /**
     * Garbage Collection
     */
    public function gc($max)
    {
        // Calculate what is to be deemed old
        $old = time() - $max;

        // Set query
        $this->db->qb([
            'method' => 'DELETE',
            'table' => $this->table,
            'filter' => 'access<:old',
            'params' => [
                ':old' => $old
            ]
        ]);

        // Attempt execution
        return $this->db->execute() ? true : false;
    }
}
