<?php
namespace Core\Security;

use Core\Data\Connectors\Db\Db;
use Core\Config\Config;
use Core\Log\Log;

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
    private $config;

    /**
     *
     * @var Log
     */
    private $log;

    /**
     * Constructor
     *
     * @param Db $db
     *            Db dependency
     * @param Config $config
     *            Cfg service dependency
     * @param Token $token
     *            Token service dependency
     * @param Log $log
     *            Log service dependendy
     */
    public function __construct(Db $db, Config $config, Token $token, Log $log)
    {
        $this->db = $db;
        $this->config= $config;
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
        if (empty($username)) {
            Throw new SecurityException('Cannot create user without username.', 'user.username.empty');
        }

        // Check for already existing username
        $exists = $this->db->count('core_users', 'username=:username', [
           ':username' => $username
        ]);

        if ($exists > 0) {
            Throw new SecurityException(sprintf('The username "%s" is already in use.', $username), 'user.username.exists');
        }

        if (empty($password)) {
            Throw new SecurityException('Cannot create user without password.', 'user.password.empty');
        }

        $data = [
            'username' => $username
        ];

        // enhance password with our pepper
        $password .= $this->config->Core['security.encrypt.pepper'];

        // Create password hash
        $password = password_hash($password, PASSWORD_DEFAULT);

        // Autoactivation without activation process?
        $autoactive = $activate ? 0 : 1;

        $data['password'] = $password;
        $data['state'] = $autoactive;

        $this->db->beginTransaction();

        $this->db->qb([
            'table' => 'core_users',
            'data' => $data
        ], true);

        // Get our new user id
        $id_user = $this->db->lastInsertId();

        $this->db->endTransaction();

        return $id_user;
    }

    /**
     * Returns user id for an activation key
     *
     * @param unknown $token
     * @return boolean
     */
    private function getUserIdByActivationKey($key)
    {
        // Lets try to find the user id in our activation token table
        $result = $this->token->getActivationTokenDataFromKey($key);

        if (empty($result)) {
            return false;
        }

        // Do not trust any result that has more then one entry!!!
        if (count($result) > 1) {

            $this->log->suspicious('There is more than one user with identical activationkey!');

            Throw new SecurityException('There is more than one user with identical activationkey!');
        }

        return $result[0]['id_user'];
    }

    public function denyActivation($key)
    {
        // Get tokendate from db
        $id_user = $this->getUserIdByActivationKey($key);

        // Nothings to do when already removed
        if (empty($id_user)) {
            return false;
        }

        // Remove the user and the token
        $this->deleteUser($id_user);
        $this->token->deleteActivationTokenByUserId($id_user);

        return true;
    }

    /**
     * Actives user by using a key
     *
     * @param string $key
     *            Key to use for activation
     */
    public function activateUser($key)
    {

        // Get tokendate from db
        $activation_token_data = $this->token->getActivationTokenDataFromKey($key);

        if (empty($activation_token_data)) {
            return false;
        }

        // We need the seperated token stored as part in the key
        $token = $this->token->getTokenFromKey($key);

        // Matching hashes?
        if (! hash_equals($activation_token_data['token'], $token)) {
            return false;
        }

        // Activate user
        $this->db->qb([
            'table' => 'core_users',
            'method' => 'UPDATE',
            'fields' => 'state',
            'filter' => 'id_user=:id_user',
            'params' => [
                ':state' => 1,
                ':id_user' => $activation_token_data['id_user']
            ]
        ], true);

        // and delete the token of this user
        $this->token->deleteActivationTokenByUserId($activation_token_data['id_user']);

        // And finally return user id
        return $activation_token_data['id_user'];
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
        $password .= $this->config->Core['security.encrypt.pepper'];

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
        $this->db->delete('core_users', 'id_user=:id_user', [
            ':id_user' => $id_user
        ]);
    }

    public function checkBan()
    {
        $ban_duration = $this->config->Core['security.ban.ttl.ban'];

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
        $max_tries = $this->config->Core['security.ban.tries'];

        // Max tries of 0 means no ban check at all
        if ($max_tries == 0) {
            return false;
        }

        // Get seconds for how long the ban log entries are relevant for ban check
        $ban_log_relevance_duration = $this->config->Core['security.ban.ttl.log'];

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

