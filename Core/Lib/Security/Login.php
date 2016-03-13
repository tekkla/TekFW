<?php
namespace Core\Lib\Security;

use Core\Lib\Data\Connectors\Db\Db;
use Core\Lib\Cfg\Cfg;
use Core\Lib\Log\Log;
use Core\Lib\Http\Cookie\Cookies;

class Login
{

    /**
     *
     * @var string
     */
    private $cookie_name = 'tekfw98751822';

    /**
     *
     * @var string
     */
    private $pepper = 'Sfgg$%fsa""sdfsddf#123WWdä,-$';

    /**
     *
     * @var int
     */
    private $days = 30;

    /**
     *
     * @var int
     */
    private $expire_time = 0;

    /**
     *
     * @var Db
     */
    private $db;

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     *
     * @var Cookies
     */
    private $cookies;

    /**
     *
     * @var Token
     */
    private $token;

    /**
     *
     * @var Log
     */
    private $log;

    /**
     * Constructor
     *
     * @param Db $db
     * @param Cfg $cfg
     * @param Security $security
     * @param Log $log
     */
    public function __construct(Db $db, Cfg $cfg, Cookies $cookies, Token $token, Log $log)
    {
        $this->db = $db;
        $this->cfg = $cfg;
        $this->cookies = $cookies;
        $this->token = $token;
        $this->log = $log;

        $this->cookie_name = $this->cfg->data['Core']['cookie.name'] . 'Token';
        $this->pepper = $this->cfg->data['Core']['security.pepper'];
        $this->days = $this->cfg->data['Core']['security.autologin_expire_days'];
    }

    /**
     * Validates the provided data against user data to perform user login.
     * Offers option to activate autologin.
     *
     * @param unknown $login
     *            Login name
     * @param unknown $password
     *            Password to validate
     * @param boolean $remember_me
     *            Option to activate autologin
     *
     * @return boolean|mixed
     */
    public function doLogin($username, $password, $remember_me = false)
    {
        // Empty username or password
        if (empty($username) || empty($password)) {

            if (empty($username)) {
                $username = 'none';
            }

            $this->logLogin($username, $username == 'none' ? true : false, empty($password));
            return false;
        }

        $username = trim($username);
        $password = trim($password);

        // Try to load user from db
        $this->db->qb([
            'table' => 'core_users',
            'fields' => [
                'id_user',
                'password',
                'state'
            ],
            'filter' => 'username=:username',
            'params' => [
                ':username' => $username
            ]
        ]);

        $login = $this->db->single();

        // No user found => login failed
        if (! $login) {

            // Log login try with not existing username
            $this->logLogin($username, true, false);
            return false;
        }

        // User needs activation?
        if ($login['state'] == 0) {
            $_SESSION['display_activation_notice'] = true;
            $this->log->suspicious(sprintf('User "%s" treid to login on not activated account.', $username));
            return false;
        }

        // Append pepper to password
        $password .= $this->pepper;

        // Password ok?
        if (password_verify($password, $login['password'])) {

            // Needs hash to be updated?
            if (password_needs_rehash($login['password'], PASSWORD_DEFAULT)) {
                $this->db->qb([
                    'table' => 'core_users',
                    'method' => 'UPDATE',
                    'fields' => [
                        'password'
                    ],
                    'filter' => 'id_user = :id_user',
                    'params' => [
                        ':password' => password_hash($password, PASSWORD_DEFAULT),
                        ':id_user' => $login['id_user']
                    ]
                ], true);
            }

            // Store essential userdata in session
            $_SESSION['logged_in'] = true;
            $_SESSION['id_user'] = $login['id_user'];

            // Remember for autologin?
            if ($remember_me === true) {
                $this->setAutoLoginCookies($login['id_user']);
            }

            // Remove possible login_failed flag from session
            unset($_SESSION['login_failed']);

            // Log successfull login
            $this->logLogin($username);

            // Login is ok, return user id
            return $login['id_user'];
        }
        else {

            // Log try with wrong password and start ban counter
            $this->logLogin($username, false, true);

            return false;
        }
    }

    /**
     * Logout of the user and clean up autologin cookies.
     */
    public function doLogout()
    {
        $id_user = $_SESSION['id_user'];

        // Clean up session
        $_SESSION['autologin_failed'] = true;
        $_SESSION['id_user'] = 0;
        $_SESSION['logged_in'] = false;

        // Calling logout means to revoke autologin cookies
        $this->cookies->remove($this->cookie_name);

        $this->log->logout('User:' . $id_user);
    }

    /**
     * Tries to autologin the user by comparing token stored in cookie with a generated token created of user
     * credentials.
     *
     * @return boolean
     */
    public function doAutoLogin()
    {
        // User already logged in?
        if ($_SESSION['logged_in'] === true) {
            return true;
        }

        // No autologin when autologin already failed
        if (isset($_SESSION['autologin_failed'])) {

            // Remove fragments/all of autologin cookies
            $this->cookies->remove($this->cookie_name);

            // Remove the flag which forces the log off
            unset($_SESSION['autologin_failed']);

            return false;
        }

        // No autologin cookie no autologin ;)
        if (! $this->cookies->exists($this->cookie_name)) {
            return false;
        }

        // Let's find the user for the token in cookie
        list ($selector, $token) = explode(':', $this->cookies->get($this->cookie_name));

        $this->db->qb([
            'table' => 'core_auth_tokens',
            'fields' => [
                'id_auth_token',
                'id_user',
                'token',
                'selector',
                'expires'
            ],
            'filter' => 'selector=:selector',
            'params' => [
                ':selector' => $selector
            ]
        ]);
        $data = $this->db->all();

        foreach ($data as $auth_token) {

            // Check if token is expired?
            if (strtotime($auth_token['expires']) < time()) {
                $this->deleteAuthTokenFromDb($auth_token['id_user']);
            }

            // Matches the hash in db with the provided token?
            if (hash_equals($auth_token['token'], $token)) {

                // Refresh autologin cookie so the user stays logged in
                // as long as he comes back before his cookie has been expired.
                $this->setAutoLoginCookies($auth_token['id_user']);

                // Login user, set session flags and return true
                $_SESSION['logged_in'] = true;
                $_SESSION['id_user'] = $auth_token['id_user'];

                // Remove possible autologin failed flag
                unset($_SESSION['autologin_failed']);

                return true;
            }
        }

        // !!! Reaching this point means autologin validation failed in all ways
        // Clean up the mess and return a big bad fucking false as failed autologin result.

        // Remove token cookie
        $this->cookies->remove($this->cookie);

        // Set flag that autologin failed
        $_SESSION['autologin_failed'] = true;

        // Set logged in flag explicit to false
        $_SESSION['logged_in'] = false;

        // Set id of user explicit to 0 (guest)
        $_SESSION['id_user'] = 0;

        // sorry, no autologin
        return false;
    }

    /**
     * Set auto login cookies with user generated token
     *
     * @param int $id_user
     *
     * @throws Error
     */
    private function setAutoLoginCookies($id_user)
    {
        // Check for empty expire time and generate time if it is empty
        if (! $this->expire_time) {
            $this->generateExpireTime();
        }

        $selector = bin2hex($this->token->generateRandomToken(6));
        $token = $this->token->generateRandomToken(64);

        $hash = hash('sha256', $token);

        // Store selector and hash in DB
        $this->db->qb([
            'table' => 'core_auth_tokens',
            'method' => 'INSERT',
            'fields' => [
                'selector',
                'token',
                'id_user',
                'expires'
            ],
            'params' => [
                ':selector' => $selector,
                ':token' => $hash,
                ':id_user' => $id_user,
                ':expires' => date('Y-m-d H:i:s', $this->expire_time)
            ]
        ], true);

        // Set autologin token cookie only when token is stored successfully in db!!!
        if ($this->db->lastInsertId()) {

            // Get new cookie
            $cookie = $this->cookies->createCookie();

            // Expiretime for both cookies
            $cookie->setExpire($this->expire_time);

            // Set token cookie
            $cookie->setName($this->cookie_name);
            $cookie->setValue($selector . ':' . $hash);
        }
    }

    /**
     * Returns login state of current user
     *
     * @return boolean
     */
    public function loggedIn()
    {
        return $_SESSION['logged_in'] == true && $_SESSION['id_user'] > 0 ? true : false;
    }

    /**
     * Checks login state and overrides the router current data to force display of loginform.
     *
     * @return boolean
     */
    public function forceLogin()
    {
        if ($this->loggedIn()) {
            return true;
        }

        /* @var $router \Core\Lib\Http\Router */
        $router = $this->di->get('core.router');
        $router->setApp('Core');
        $router->setController('Security');
        $router->setAction('Login');

        return false;
    }

    /**
     * Logs login process.
     *
     * @param boolean $username
     *            Username to use in logentries text
     * @param boolean $error_username
     *            Flag to signal that there was a problem with the username
     * @param boolean $error_password
     *            Flag to signal that there was a problem with the password
     * @param boolean $ban
     *            Flag to signal that this is a banable action
     */
    private function logLogin($username, $error_username = false, $error_password = false, $ban = true)
    {
        $text = sprintf('Login for user "%s"', $username);
        $state = 0;

        if ($error_username || $error_password) {

            $text .= ' failed because of wrong ';

            if ($error_username) {
                $state += 1;
                $text .= 'username';
            }

            if ($error_password) {
                $state += 2;
                $text .= 'password';
            }

            if ($ban == false) {
                $this->log->suspicious($text, $state);
                return;
            }
        }

        // Start ban process only when requested and only when state indicates a login error from user credentials
        if ($state > 0 && $ban) {
            $this->log->ban($text, 1);
            return;
        }

        // Still here? Log success!
        $this->log->login($text . ' success');
    }

    /**
     * Sets the number of days the login cookie should be valid when user requests autologin
     *
     * @param int $days
     *            Number of days
     *
     * @return \Core\Lib\Security\Security
     */
    public function setDaysUntilCookieExpires($days)
    {
        $this->days = (int) $days;

        // Auto calculate expiretime
        $this->generateExpireTime();

        return $this;
    }

    /**
     * Generates the expiring timestamp for cookies
     *
     * @return number
     */
    private function generateExpireTime()
    {
        // Create expire date of autologin
        return $this->expire_time = time() + 3600 * 24 * $this->days;
    }
}