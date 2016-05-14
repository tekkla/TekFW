<?php
namespace Core\Security;

use Core\Data\Connectors\Db\Db;
use Core\Log\Log;

class Token
{

    /**
     *
     * @var Db
     */
    private $db;

    /**
     *
     * @var Log
     */
    private $log;

    public function __construct(Db $db, Log $log)
    {
        $this->db = $db;
        $this->log = $log;
    }

    /**
     * Removes the token of a user from auth_token table and all tokens expired.
     *
     * @param int $id_user
     */
    private function deleteAuthTokenFromDb($id_user)
    {
        // Yep! Delete token and return false for failed autologin
        $this->db->qb([
            'table' => 'core_auth_tokens',
            'method' => 'DELETE',
            'filter' => 'expires < :expires OR id_user=:id_user',
            'params' => [
                ':expires' => date('Y-m-d H:i:s'),
                ':id_user' => $id_user
            ]
        ], true);
    }

    public function removeExpiredTokens()
    {
        $token_tables = [
            'core_activation_tokens',
            'core_auth_tokens'
        ];

        foreach ($token_tables as $table) {
            $this->db->delete($table, 'expires < :time', [
                'time' => time()
            ]);
        }
    }

    /**
     * Generates a random session token
     *
     * @param number $size
     *            Size of token
     *
     * @return Security
     */
    public function generateRandomSessionToken($size = 32, $force = false)
    {
        if (! isset($_SESSION['Core']['token']) || $force == true) {

            // Store random token in session
            $_SESSION['Core']['token'] = hash('sha256', $this->generateRandomToken($size));
        }

        return $this;
    }

    /**
     * Generates a random token
     *
     * @param number $size
     *            Size of token
     *
     * @return string
     */
    public function generateRandomToken($size = 32)
    {
        return function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes($size) : mcrypt_create_iv($size);
    }

    /**
     * Validates $token against the random token stored in session
     *
     * @param string $token
     *            Token to compare
     *
     * @return boolean
     */
    public function validateRandomSessionToken($token)
    {
        return $token == $_SESSION['Core']['token'];
    }

    /**
     * Checks $_POST for sent token value and validates this token (if present) against the random token stored in
     * session.
     *
     * @return boolean
     */
    public function validatePostToken()
    {
        // Show warning in error log when using a form without a token
        if (empty($_POST['token'])) {
            return false;
        }

        // Token sent so let's check it
        if (! $this->validateRandomSessionToken($_POST['token'])) {

            // Log attempt and start ban process with max 3 tries.
            $this->log->suspicious('Form data without proper token received. All data will be dropped.', 3);

            return false;
        }

        unset($_POST['token']);

        return true;
    }

    public function getPartFromKey($key, $id)
    {
        $key = urldecode($key);

        $parts = explode(':', $key);

        // Without selector or token no activation!!!
        if (empty($parts[$id])) {
            return false;
        }
        return $parts[$id];
    }

    public function getTokenFromKey($key)
    {
        return $this->getPartFromKey($key, 1);
    }

    public function getSelectorFromKey($key)
    {
        return $this->getPartFromKey($key, 0);
    }

    /**
     * Creates an activation token for a user in db and returns selector:token string
     *
     * @param integer $id_user
     *            The user id this token belongs to
     * @param integer $ttl
     *            Optional TTL in seconds when the token expires and gets deleted
     * @param Db $db
     *            Optional Db connector to use the database logic within this method inside a running transaction
     *
     * @return string
     */
    public function createActivationToken($id_user, $ttl = 864000, Db $db = null)
    {
        // First: clean all expired tokens!
        $this->removeExpiredTokens();

        if (empty($db)) {
            $db = $this->db;
        }

        // Delete all existing tokens of this user
        $db->delete('core_activation_tokens', 'id_user=:id_user', [
            'id_user' => $id_user
        ]);

        // Make sure the selector is not in use. Such case is very uncommon and rare but can happen.
        $in_use = true;

        while ($in_use == true) {

            // Generate random selector and token
            $selector = bin2hex($this->generateRandomToken(6));

            // And check if it is already in use
            $in_use = $this->checkSelectorInUse($selector);
        }

        $token = $this->generateRandomToken(32);

        $hash = hash('sha256', $token);

        $db->qb([
            'table' => 'core_activation_tokens',
            'data' => [
                'id_user' => $id_user,
                'selector' => $selector,
                'token' => $hash,
                'expires' => time() + $ttl
            ]
        ], true);

        return $selector . ':' . $hash;
    }

    private function checkSelectorInUse($selector)
    {
        return $this->db->count('core_activation_tokens', 'selector = :selector', [
            'selector' => $selector
        ]) > 0;
    }

    public function getActivationTokenDataFromKey($key)
    {
        // First: clean all expired tokens!
        $this->removeExpiredTokens();

        $this->db->qb([
            'table' => 'core_activation_tokens',
            'fields' => [
                'id_user',
                'token',
                'expires'
            ],
            'filter' => 'selector=:selector',
            'params' => [
                ':selector' => $this->getSelectorFromKey($key)
            ]
        ]);

        $result = $this->db->all();

        if (empty($result)) {
            return false;
        }

        // Only one token record allowed!!!!
        if (count($result) > 1) {
            Throw new SecurityException(sprintf('There is more than one token found with selector "%s"', $this->getSelectorFromKey($key)));
        }

        return $result[0];
    }

    public function deleteActivationTokenByUserId($id_user)
    {
        $this->db->delete('core_activation_tokens', 'id_user=:id_user', [
            'id_user' => $id_user
        ]);
    }

    public function getActivationDataOfUser($id_user)
    {
        // First: clean all expired tokens!
        $this->removeExpiredTokens();

        return $this->db->find('core_activation_tokens', 'id_user', $id_user);
    }
}
