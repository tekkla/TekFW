<?php
namespace Core\Security;

use Core\Data\Connectors\Db\Db;
use Core\Cfg\Cfg;
use Core\Log\Log;
use Core\Http\Cookie\Cookies;

/**
 * Login.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Login
{

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
        if (empty($login)) {

            // Log login try with not existing username
            $this->logLogin($username, true, false);
            return false;
        }

        // User needs activation?
        if ($login['state'] == 0) {
            $_SESSION['Core']['display_activation_notice'] = true;
            $this->log->suspicious(sprintf('User "%s" treid to login on not activated account.', $username));
            return false;
        }

        // Append pepper to password
        $password .= $this->cfg->data['Core']['security.encrypt.pepper'];

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

            // Refresh session id and delete old session
            session_regenerate_id(true);

            // Store essential userdata in session
            $_SESSION['Core']['logged_in'] = true;
            $_SESSION['Core']['user']['id'] = $login['id_user'];

            // Remember for autologin?
            if ($remember_me === true) {
                $this->setAutoLoginCookies($login['id_user']);
            }

            // Remove possible login_failed flag from session
            unset($_SESSION['Core']['login_failed']);

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
     * Logout
     *
     * Logs out the user, removes all it's data from session, creates a new session token, removes all autologin cookies
     * and logs the logout to the log table.
     *
     * @return boolean
     */
    public function doLogout()
    {
        $id_user = $_SESSION['Core']['user']['id'];

        // Refresh session id and delete old session
        session_regenerate_id(true);

        // Clean up session
        $_SESSION['Core']['autologin_failed'] = true;
        $_SESSION['Core']['user'] = [
            'id' => 0
        ];
        $_SESSION['Core']['logged_in'] = false;

        // Create a new session token
        $this->token->generateRandomSessionToken(64);

        // Calling logout means to revoke autologin cookies
        $this->cookies->remove($this->getCookieName());

        $this->log->logout('User:' . $id_user);

        return true;
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
        if (! empty($_SESSION['Core']['logged_in'])) {
            return true;
        }

        // Get the cookiename of our autologin token
        $cookie_name = $this->getCookieName();

        // Remove all autologin cookies when autlogin is off in config
        if (empty($this->cfg->data['Core']['login.autologin.active'])) {
            $this->cookies->remove($cookie_name);
            return false;
        }

        // No autologin when autologin already failed
        if (! empty($_SESSION['Core']['autologin_failed'])) {

            // Remove fragments/all of autologin cookies
            $this->cookies->remove($cookie_name);

            // Remove the flag which forces the log off
            unset($_SESSION['Core']['autologin_failed']);

            return false;
        }

        // No autologin cookie no autologin ;)
        if (! $this->cookies->exists($cookie_name)) {
            return false;
        }

        // Let's find the user for the token in cookie
        list ($selector, $token) = explode(':', $this->cookies->get($cookie_name));

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

                // Refresh session id and delete old session
                session_regenerate_id(true);

                // Refresh autologin cookie so the user stays logged in
                // as long as he comes back before his cookie has been expired.
                $this->setAutoLoginCookies($auth_token['id_user']);

                // Login user, set session flags and return true
                $_SESSION['Core']['logged_in'] = true;
                $_SESSION['Core']['user']['id'] = $auth_token['id_user'];

                // Remove possible autologin failed flag
                unset($_SESSION['Core']['autologin_failed']);

                return true;
            }
        }

        // !!! Reaching this point means autologin validation failed in all ways
        // Clean up the mess and return a big bad fucking false as failed autologin result.

        // Remove token cookie
        $this->cookies->remove($cookie_name);

        // Set flag that autologin failed
        $_SESSION['Core']['autologin_failed'] = true;

        // Set logged in flag explicit to false
        $_SESSION['Core']['logged_in'] = false;

        // Set id of user explicit to 0 (guest)
        $_SESSION['Core']['user']['id'] = 0;

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
        // Create expire date
        $expires = time() + 3600 * 24 * $this->cfg->data['Core']['login.autologin.expires_after'];

        // Create selector
        $selector = bin2hex($this->token->generateRandomToken(6));

        // Create token
        $token = $this->token->generateRandomToken(64);

        // hash token
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
                ':expires' => date('Y-m-d H:i:s', $expires)
            ]
        ], true);

        // Set autologin token cookie only when token is stored successfully in db!!!
        if ($this->db->lastInsertId()) {

            // Get new cookie
            $cookie = $this->cookies->createCookie();

            // Expiretime for both cookies
            $cookie->setExpire($expires);

            // Set token cookie
            $cookie->setName($this->getCookieName());
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
        return $_SESSION['Core']['logged_in'] == true && ! empty($_SESSION['Core']['user']['id']) ? true : false;
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

        /* @var $router \Core\Http\Router */
        $router = $this->di->get('core.router');
        $router->setApp('Core');
        $router->setController('Login');
        $router->setAction('Login');

        return false;
    }

    /**
     * Logs login process
     *
     * @param boolean $username
     *            Username to use in logentries text
     * @param boolean $error_username
     *            Flag to signal that there was a problem with the username
     * @param boolean $error_password
     *            Flag to signal that there was a problem with the password
     * @param boolean $ban
     *            Flag to signal that this is a banable action
     *
     * @return void
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
     * Creates the name of the autologin token cookie based on the cookie name set in core config
     *
     * @return string
     */
    private function getCookieName()
    {
        return $this->cfg->data['Core']['cookie.name'] . 'Token';
    }
}