<?php
namespace Core\Lib\Mailer;

use Core\Lib\Cfg\Cfg;
use Core\Lib\Log\Log;
use Core\Lib\Data\Connectors\Db\Db;

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
     * Cfg service
     *
     * @var Cfg
     */
    private $cfg;

    /**
     * Log service
     *
     * @var Log
     */
    private $log;

    /**
     * Default db service
     *
     * @var Db
     */
    private $db;

    /**
     * Mailer active state flag
     *
     * @var unknown
     */
    private $active = false;

    /**
     * Constructor
     *
     * @param Cfg $cfg
     *            Config dependency
     * @param Log $log
     *            Log dependeny
     * @param Db $db
     *            Db dependeny
     */
    public function __construct(Cfg $cfg, Log $log, Db $db)
    {
        $this->cfg = $cfg;
        $this->log = $log;
        $this->db = $db;
    }

    /**
     * Registers default MTA with settins from config
     */
    public function init()
    {}

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
        $log = [];

        /* @var $mail \Core\Lib\Mailer\Mail */
        foreach ($this->mails as $mail) {

            try {

                // -----------------------------------------------
                // Handle MTA
                // -----------------------------------------------

                // Get data of MTA mapped to this mail
                $MTA = $this->loadMta($mail->getMTA());

                $mailer = new \PHPMailer();

                $mailer->SMTPDebug = 0;
                $mailer->Debugoutput = function ($str, $level) {
                    \FB::log($str);
                };

                // Set wthich mail system is used by the MTA
                switch ($MTA['type']) {
                    case 1:
                        $mailer->isSMTP();
                        break;

                    case 2:
                        $mailer->isQmail();
                        break;
                }

                // Connection infos
                $mailer->Host = $MTA['host'];
                $mailer->Port = $MTA['port'];
                $mailer->SMTPSecure = $MTA['smtp_secure'];

                // Userlogin
                $mailer->SMTPAuth = $MTA['smtp_auth'];

                if ($mailer->SMTPAuth) {
                    $mailer->Username = $MTA['username'];
                    $mailer->Password = $MTA['password'];
                }

                if (! empty($MTA['smtp_options'])) {
                    $mailer->SMTPOptions = $MTA['smtp_options'];
                }

                // -----------------------------------------------
                // Handle mail
                // -----------------------------------------------

                // Basics
                $mailer->CharSet = $mail->getCharset();
                $mailer->Encoding = $mail->getEncoding();

                // Custom headers
                $headers = $mail->getHeaders();

                foreach ($headers as $name => $value) {
                    $mailer->addCustomHeader($name, $value);
                }

                // Priority
                $mailer->Priority = $mail->getPriority();

                // Sender
                $from = $mail->getFrom();

                $mailer->setFrom($from['from'], $from['name']);

                // Reply to
                $reply_to = $mail->getReplyto();

                foreach ($reply_to as $address => $name) {
                    $mailer->addReplyTo($address, $name);
                }

                // Send confirm mail to?
                $mailer->ConfirmReadingTo = $mail->getConfirmReadingTo();

                // Recipients
                $recipients = $mail->getRecipients();

                foreach ($recipients['to'] as $address => $name) {
                    $mailer->addAddress($address, $name);
                }

                foreach ($recipients['cc'] as $address => $name) {
                    $mailer->addCC($address, $name);
                }

                foreach ($recipients['bcc'] as $address => $name) {
                    $mailer->addBCC($address, $name);
                }

                // Content
                $mailer->Subject = $mail->getSubject();
                $mailer->Body = $mail->getBody();

                if ($mail->isHtml()) {
                    $mailer->isHTML();
                    $mailer->AltBody = $mail->getAltbody();
                }

                // Attachements
                $attachements = $mail->getAttachements();

                foreach ($attachements as $a) {
                    $mailer->addAttachment($a['path'], $a['name'], $a['encoding'], $a['type']);
                }

                // Images
                $images = $mail->getEmbeddedImages();

                foreach ($images as $i) {
                    $mailer->addEmbeddedImage($i['path'], $i['cid'], $i['name'], $i['encoding'], $i['type']);
                }

                if ($mailer->send()) {
                    $text = sprintf('Mail send ok.');
                    $code = 0;
                }
                else {
                    $text = sprintf('Mail send error: %s', $mailer->ErrorInfo);
                    $code = 1;
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
            $this->log->log($entry[0], 'mailer', $entry[1]);
        }
    }

    private function loadMTA($id)
    {
        if (array_key_exists($id, $this->MTAs)) {
            return $this->MTAs[$id];
        }

        $MTA = $this->db->find('core_mtas', 'id_mta', $id);

        if (empty($MTA)) {
            Throw new MailerException('MTA with id "%s" not found');
        }

        if (! empty($MTA['smtp_options'])) {
            $MTA['smtp_options'] = parse_ini_string($MTA['smtp_options'], true, INI_SCANNER_TYPED);
        }

        $this->MTAs[$id] = $MTA;

        return $this->MTAs[$id];
    }
}
