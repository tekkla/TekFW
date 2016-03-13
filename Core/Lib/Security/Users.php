<?php
namespace Core\Lib\Security;

use Core\Lib\Data\Connectors\Db\Db;
use Core\Lib\Cfg\Cfg;
use Core\Lib\Log\Log;

/**
 * Users.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Users
{

    /**
     *
     * @var Db
     */
    private $db;

    /**
     *
     * @var Token
     */
    private $token;

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     *
     * @var Logging
     */
    private $log;

    /**
     * Constructor
     *
     * @param Db $db
     *            Db dependency
     * @param Cfg $cfg
     *            Cfg service dependency
     * @param Token $token
     *            Token service dependency
     * @param Log $log
     *            Log service dependendy
     */
    public function __construct(Db $db, Cfg $cfg, Token $token, Log $log)
    {
        $this->db = $db;
        $this->cfg = $cfg;
        $this->token = $token;
        $this->log = $log;
    }

    /**
     * Creates a new user
     *
     * Uses given username and password and returns it's user id.
     * Optional state flag to activate user on creation.
     *
     * Given password will be hashed by password_hash($password, PASSWORD_DEFAULT) by default.
     *
     * @param string $username
     *            Username
     * @param string $password
     *            Password
     * @param boolean $state
     *            Optional: Stateflag. 0=inactive | 1=active (default: 0)
     *
     * @return integer
     */
    public function createUser($username, $password, $activate)
    {
        if (! $username) {
            Throw new SecurityException('Cannot create user without username.');
        }

        if (! $password) {
            Throw new SecurityException('Cannot create user without password.');
        }

        $data = [
            'username' => $username
        ];

        // enhance password with our pepper
        $password .= $this->cfg->data['Core']['security.pepper'];

        // Create password hash
        $password = password_hash($password, PASSWORD_DEFAULT);

        // Autoactivation without activation process?
        $autoactive = $activate ? 0 : 1;

        $data['password'] = $password;
        $data['state'] = $autoactive;

        $this->db->beginTransaction();

        $this->db->qb([
            'table' => 'users',
            'data' => $data
        ], true);

        // Get our new user id
        $id_user = $this->db->lastInsertId();

        if ($autoactive == 1) {
            return $id_user;
        }

        $this->createActivationToken($id_user);

        $this->db->endTransaction();

        return $id_user;
    }

    /**
     * Creates an activation token for a user in db and returns selector:token string
     *
     * @param int $id_user
     *            Id of user
     *
     * @return string
     */
    public function createActivationToken($id_user)
    {
        // Delete all existing tokens of this user
        $this->db->delete('activation_tokens', 'id_user=:id_user', [
            'id_user' => $id_user
        ]);

        // Activation process has it's own table. We need a seperate container for it.
        $data = [];

        // Generate random selector and token
        $selector = bin2hex($this->token->generateRandomToken(6));
        $token = $this->token->generateRandomToken(32);

        $hash = hash('sha256', $token);

        $data['id_user'] = $id_user;
        $data['selector'] = $selector;
        $data['token'] = $hash;
        $data['expires'] = time() + $this->cfg->data['Core']['security.activation.ttl'];

        $this->db->qb([
            'table' => 'core_activation_tokens',
            'data' => $data
        ], true);

        return $selector . ':' . $hash;
    }

    /**
     * Actives user by
     *
     * @param string $key
     *            Key to use for activation
     */
    public function activateUser($key)
    {
        $selector = '';
        $token = '';

        list ($selector, $token) = explode(':', $key);

        // Without selector or token no activation!!!
        if (empty($selector) || empty($token)) {
            return false;
        }

        // Lets try to find the user id in our activation token table
        $this->db->qb([
            'table' => 'core_activation_tokens',
            'fields' => [
                'id_user',
                'token',
                'expires'
            ],
            'filter' => 'selector=:selector',
            'params' => [
                ':selector' => $selector
            ]
        ]);

        $activations = $this->db->all();

        foreach ($activations as $activation) {

            // Matches hash?
            if (! hash_equals($activation['token'], $token)) {
                continue;
            }

            // Activation token expired?
            if ($activation['expires'] < time()) {
                continue;
            }

            // Reaching this point means we have a valid activation. Flag user as active.
            $this->db->qb([
                'table' => 'core_users',
                'method' => 'UPDATE',
                'fields' => 'state',
                'filter' => 'id_user=:id_user',
                'params' => [
                    ':state' => 1,
                    ':id_user' => $activation['id_user']
                ]
            ], true);

            // Delete activation token
            $this->db->delete('activation_tokens', 'id_user=:id_user', [
                ':id_user' => $activation['id_user']
            ]);

            // And finally return user id
            return $activation['id_user'];
        }

        // Falling through here means activation failed
        return false;
    }

    /**
     * Changes password of a user
     *
     * @param int $id_user
     *            Id of user
     * @param string $old
     *            It's old password
     * @param string $new
     *            The new password
     */
    public function changePassword($id_user, $password)
    {
        // enhance password with our pepper
        $password .= $this->cfg->data['Core']['security.pepper'];

        // Check the old password
        $this->db->qb([
            'table' => 'core_users',
            'method' => 'UPDATE',
            'fields' => 'password',
            'filter' => 'id_user=:id_user',
            'params' => [
                ':id_user' => $id_user,
                ':password' => password_hash($password, PASSWORD_DEFAULT)
            ]
        ], true);
    }

    public function deleteUser($id_user)
    {
        // Check the old password
        $this->db->delete('users', 'id_user=:id_user', [
            ':id_user' => $id_user
        ]);
    }

    public function checkBan()
    {
        $ban_duration = $this->cfg->data['Core']['security.ban.ttl.ban'];

        // No ban without ban time
        if ($ban_duration == 0) {
            return false;
        }

        // Is this ip already banned?
        $time_of_last_ban = $this->log->getBanActiveTimestamp($_SERVER['REMOTE_ADDR']);

        // If so, is the ban still active or already expired?
        if ($time_of_last_ban + $ban_duration > time()) {
            return true;
        }

        // Get max tries until get banned from config
        $max_tries = $this->cfg->data['Core']['security.ban.tries'];

        // Max tries of 0 means no ban check at all
        if ($max_tries == 0) {
            return false;
        }

        // Get seconds for how long the ban log entries are relevant for ban check
        $ban_log_relevance_duration = $this->cfg->data['Core']['security.ban.ttl.log'];

        // Zero sconds means that this check is not needed.
        if ($ban_log_relevance_duration == 0) {
            return false;
        }

        // Get ban counter for the visitors IP
        $counter = $this->log->countBanLogEntries($_SERVER['REMOTE_ADDR'], $ban_log_relevance_duration);

        // As long as the ban counter is smaller than allowed the check is ok.
        if ($counter < $max_tries) {
            return false;
        }

        // User is banned
        $this->log->ban('User got banned because of too many tries.', 2);

        return true;
    }
}

