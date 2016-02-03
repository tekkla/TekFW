<?php
namespace Core\Lib\Mailer;

use Core\Lib\Cfg\Cfg;
use Core\Lib\Logging\Logging;

/**
 * Mailer.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Mailer
{

    /**
     * Registered MTAs
     *
     * @var array
     */
    private $MTAs = [];

    /**
     * Mail queue
     *
     * @var array
     */
    private $mails = [];

    /**
     * Allowed secure protocols
     *
     * @var array
     */
    private $protocols = [
        'tls',
        'ssl'
    ];

    /**
     *
     * @var Logging
     */
    private $logging;

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     * Constructor
     *
     * @param Cfg $cfg
     *            Config dependency
     * @param Logging $logging
     *            Logging dependeny
     */
    public function __construct(Cfg $cfg, Logging $logging)
    {
        $this->cfg = $cfg;
        $this->logging = $logging;
    }

    /**
     * Registers default MTA with settins from config
     */
    public function init()
    {
        
        // Create default MTA
        $MTA = $this->createMTA('default');
        
        if ($this->cfg->get('Core', 'mail.mta.default.system') == 1) {
            
            // Is SMTP
            $MTA->isSMTP(true);
            $MTA->addHost($this->cfg->get('Core', 'mail.mta.default.host'));
            
            if ($this->cfg->get('Core', 'mail.mta.default.username')) {
                $MTA->useAuth(true);
                $MTA->setUsername($this->cfg->get('Core', 'mail.mta.default.username'));
                $MTA->setPassword($this->cfg->get('Core', 'mail.mta.default.password'));
            }
            
            $MTA->useProtocol($this->cfg->get('Core', 'mail.mta.default.protocol'));
            $MTA->setPort($this->cfg->get('Core', 'mail.mta.default.port'));
            
            if ($this->cfg->get('Core', 'mail.mta.default.accept_selfsigned') == 1) {
                $MTA->hasSelfSignedCert();
            }
        }
    }

    /**
     * Creates, registers and returns reference to a new MTA object
     *
     * @return \Core\Lib\Mailer\MTA
     */
    public function &createMTA($id)
    {
        $MTA = new MTA();
        
        $this->MTAs[$id] = $MTA;
        
        return $MTA;
    }

    /**
     * Registers a MTA object
     *
     * @param string $id
     *            Id of the MTA
     * @param MTA $MATA
     *            MTA Oobject to register
     *            
     * @return \Core\Lib\Mailer\Mailer
     */
    public function registerMTA($id, MTA $MATA)
    {
        $this->MTAs[$id] = $MATA;
        
        return $this;
    }

    /**
     * Returns a registered MTA
     *
     * @param string $id
     *            Id of the MTA
     *            
     * @throws MailerException
     *
     * @return \Core\Lib\Mailer\MTA
     */
    public function getMTA($id)
    {
        if (! array_key_exists($id, $this->MTAs)) {
            Throw new MailerException(sprintf('The requested MTA with id "%s" is not registered', $id));
        }
        
        return $this->MTAs[$id];
    }

    /**
     * Checks for registered MTA by it's id
     *
     * @param string $id
     *            Id of registered MTA
     *            
     * @return boolean
     */
    public function checkMTA($id)
    {
        return array_key_exists($id, $this->MTAs);
    }

    /**
     * Creates, registers and returns reference to a new mail object
     *
     * @return \Core\Lib\Mailer\Mail
     */
    public function &createMail()
    {
        $mail = new Mail();
        $mail->injectMailer($this);
        $mail->setMTA('default');
        
        $this->mails[] = $mail;
        
        return $mail;
    }

    /**
     * Adds a mail object to the mail queue
     *
     * @param Mail $mail            
     *
     * @return \Core\Lib\Mailer\Mailer
     */
    public function addMail(Mail $mail)
    {
        $this->mails[] = $mail;
        
        return $this;
    }

    /**
     * Sends all mails
     */
    public function send()
    {
        \FB::log(__METHOD__ . '::' . count($this->mails));
        
        $MTA_current = '';
        
        $log = [];
        
        /* @var $mail \Core\Lib\Mailer\Mail */
        foreach ($this->mails as $mail) {
            
            try {
                
                // Handle MTA
                $MTA_mail = $mail->getMTA();
                
                if ($MTA_current != $MTA_mail) {
                    
                    // Create new PHPMAiler instance only when MTA has changed
                    $phpmailer = new \PHPMailer();
                    
                    // Get the MTA
                    $MTA = $this->getMTA($MTA_mail);
                    
                    $MTA_current = $MTA_mail;
                }
                
                // Falg debugmode
                $debug = $this->cfg->get('Core', 'mail.general.smtpdebug');
                
                if ($debug) {
                    $phpmailer->SMTPDebug = 2;
                }
                
                $phpmailer->isSMTP($MTA->isSMTP());
                $phpmailer->Host = implode(';', $MTA->getHosts());
                $phpmailer->Port = $MTA->getPort();
                $phpmailer->SMTPSecure = $MTA->getProtocol();
                $phpmailer->SMTPAuth = $MTA->useAuth();
                
                if ($phpmailer->SMTPAuth) {
                    $phpmailer->Username = $MTA->getUsername();
                    $phpmailer->Password = $MTA->getPassword();
                }
                
                $smtp_options = $MTA->getSmtpOptions();
                
                if ($smtp_options) {
                    $phpmailer->SMTPOptions = $smtp_options;
                }
                
                // -----------------------------------------------
                // Handle mail
                // -----------------------------------------------
                
                // Basics
                $phpmailer->CharSet = $mail->getCharset();
                $phpmailer->Encoding = $mail->getEncoding();
                
                // Custom headers
                $headers = $mail->getHeaders();
                
                foreach ($headers as $name => $value) {
                    $phpmailer->addCustomHeader($name, $value);
                }
                
                // Priority
                $phpmailer->Priority = $mail->getPriority();
                
                // Sender
                $from = $mail->getFrom();
                
                $phpmailer->setFrom($from['from'], $from['name']);
                
                // Reply to
                $reply_to = $mail->getReplyto();
                
                foreach ($reply_to as $address => $name) {
                    $phpmailer->addReplyTo($address, $name);
                }
                
                // Send confirm mail to?
                $phpmailer->ConfirmReadingTo = $mail->getConfirmReadingTo();
                
                // Recipients
                $recipients = $mail->getRecipients();
                
                foreach ($recipients['to'] as $address => $name) {
                    $phpmailer->addAddress($address, $name);
                }
                
                foreach ($recipients['cc'] as $address => $name) {
                    $phpmailer->addCC($address, $name);
                }
                
                foreach ($recipients['bcc'] as $address => $name) {
                    $phpmailer->addBCC($address, $name);
                }
                
                // Content
                $phpmailer->Subject = $mail->getSubject();
                $phpmailer->Body = $mail->getBody();
                
                if ($mail->isHtml()) {
                    $phpmailer->isHTML();
                    $phpmailer->AltBody = $mail->getAltbody();
                }
                
                // Attachements
                $attachements = $mail->getAttachements();
                
                foreach ($attachements as $a) {
                    $phpmailer->addAttachment($a['path'], $a['name'], $a['encoding'], $a['type']);
                }
                
                // Images
                $images = $mail->getEmbeddedImages();
                
                foreach ($images as $i) {
                    $phpmailer->addEmbeddedImage($i['path'], $i['cid'], $i['name'], $i['encoding'], $i['type']);
                }
                
                if ($debug) {
                    ob_start();
                }
                
                if ($phpmailer->send()) {
                    $text = sprintf('Mail send ok.');
                    $code = 0;
                }
                else {
                    $text = sprintf('Mail send error: %s', $phpmailer->ErrorInfo);
                    $code = 1;
                }
                
                \FB::log($text);
                
                if ($debug) {
                    $text .= PHP_EOL . ob_get_clean();
                }
                
                $log[] = [
                    $text,
                    $code
                ];
            }
            catch (\phpmailerException $e) {
                $log[] = [
                    'Mailer exception caught: ' . $e->getMessage(),
                    1
                ];
            }
        }
        
        foreach ($log as $entry) {
            $this->logging->log($entry[0], 'mailer', $entry[1]);
        }
    }
}

