<?php
namespace Core\Lib\Mailer;

/**
 * MTA.php
 *
 * @author Michael "Tekkla" Zorn (tekkla@tekkla.de)
 * @copyright 2016
 * @license MIT
 */
class MTA
{

    private $smtp = true;

    /**
     * List of hosts
     *
     * @var array
     */
    private $hosts = [];

    /**
     * Use SMTP-Auth flag
     *
     * @var boolean
     */
    private $auth = true;

    /**
     * Username for SMTP-Auth
     *
     * @var string
     */
    private $username = '';

    /**
     * Password for SMTP-Auth
     *
     * @var string
     */
    private $password = '';

    /**
     * Protocole to use
     *
     * @var string
     */
    private $secure = 'tls';

    /**
     * Port to use
     *
     * @var int
     */
    private $port = 587;

    /**
     * Optional SMTP options
     *
     * @var array
     */
    private $smtpoptions = [];

    /**
     * Sets username for smtauth
     *
     * @param string $username            
     *
     * @return \Core\Lib\Mail\MTA
     */
    public function setUsername($username)
    {
        $this->username = $username;
        
        return $this;
    }

    /**
     * Returns username for smtpauth
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets passord for smtauth
     *
     * @param string $password            
     *
     * @return \Core\Lib\Mail\MTA
     */
    public function setPassword($password)
    {
        $this->password = $password;
        
        return $this;
    }

    /**
     * Returns set password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets hosts to use
     *
     * @param string $host
     *            Hostname or a ; seperates list of hostnames
     *            
     * @return \Core\Lib\Mail\MTA
     */
    public function setHost($host)
    {
        $this->hosts = explode(';', $host);
        
        return $this;
    }

    /**
     * Adds one hostname to the hosts list
     *
     * @param string $host
     *            Hostname
     *            
     * @return \Core\Lib\Mail\MTA
     */
    public function addHost($host)
    {
        $this->hosts[] = $host;
        
        return $this;
    }

    /**
     * Returns hostlist
     *
     * @return array
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * Sets secure protocol to use
     *
     * @param string $protocol
     *            Protocol to use. Valid options are "tls", "ssl"
     */
    public function useProtocol($protocol)
    {
        $this->secure = $protocol;
        
        return $this;
    }

    /**
     * Returns set secure protocol
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->secure;
    }

    public function useAuth($flag = null)
    {
        if (! isset($flag)) {
            return $this->auth;
        }
        
        $this->auth = (bool) $flag;
        
        return $this;
    }

    /**
     * Sets port
     *
     * @param integer $port
     *            Port to connect to
     *            
     * @return \Core\Lib\Mailer\MTA
     */
    public function setPort($port)
    {
        $this->port = (int) $port;
        
        return $this;
    }

    /**
     * Returns set port
     *
     * @return number
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets or returns state to use STMP in favor of phpmail
     *
     * @param boolean $flag
     *            With flag set the state is switched to flags boolean value.
     *            Without flag the method returns the current state.
     *            
     * @return boolean
     */
    public function isSMTP($flag = null)
    {
        if (! isset($flag)) {
            return $this->smtp;
        }
        
        $this->smtp = (bool) $flag;
        
        return $this;
    }

    /**
     * Sets ssl options to use selfsigned certificates
     *
     * @return \Core\Lib\Mailer\MTA
     */
    public function hasSelfSignedCert()
    {
        $this->smtpoptions['ssl'] = [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ];
        
        return $this;
    }

    /**
     * Removes all set ssl options
     *
     * @return \Core\Lib\Mailer\MTA
     */
    public function hasNoSelfSignedCert()
    {
        $this->smtpoptions = [];
        
        return $this;
    }

    public function getSmtpOptions()
    {
        return $this->smtpoptions;
    }
}
