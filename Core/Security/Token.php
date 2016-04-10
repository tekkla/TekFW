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
            'table' => 'auth_tokens',
            'method' => 'DELETE',
            'filter' => 'expires < :expires OR id_user=:id_user',
            'params' => [
                ':expires' => date('Y-m-d H:i:s'),
                ':id_user' => $id_user
            ]
        ], true);
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
        if (! isset($_SESSION['token']) || $force == true) {

            // Store random token in session
            $_SESSION['token'] = hash('sha256', $this->generateRandomToken($size));
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
        return $token == $_SESSION['token'];
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
            $this->log->logSuspicious('Form data without proper token received. All data will be dropped.', 3);

            return false;
        }

        unset($_POST['token']);

        return true;
    }
}
