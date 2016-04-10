<?php
namespace Core\Mailer;

use Core\Traits\ArrayTrait;

/**
 * Mail.php
 *
 * @author Michael "Tekkla" Zorn (tekkla@tekkla.de)
 * @copyright 2016
 * @license MIT
 */
class Mail
{

    use ArrayTrait;

    /**
     *
     * @var string
     */
    private $subject = '';

    /**
     *
     * @var string
     */
    private $body = '';

    /**
     *
     * @var string
     */
    private $altbody = '';

    /**
     *
     * @var array
     */
    private $attachements = [];

    /**
     *
     * @var array
     */
    private $images = [];

    /**
     *
     * @var boolean
     */
    private $html = false;

    /**
     *
     * @var string
     */
    private $charset = 'UTF-8';

    /**
     *
     * @var string
     */
    private $encoding = '8bit';

    /**
     *
     * @var string
     */
    private $from = '';

    /**
     *
     * @var string
     */
    private $fromname = '';

    /**
     *
     * @var string
     */
    private $sender = '';

    private $replyto = [];

    /**
     *
     * @var array
     */
    private $recipients = [
        'to' => [],
        'cc' => [],
        'bcc' => []
    ];

    /**
     *
     * @var string
     */
    private $confirm_reading_to = '';

    /**
     *
     * @var int
     */
    private $priority = 3;

    /**
     *
     * @var array
     */
    private $headers = [];

    /**
     *
     * @var string
     */
    private $MTA = '';

    /**
     *
     * @var Mailer
     */
    private $mailer;

    /**
     * Returns subject text
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets subject text
     *
     * @param string $subject
     *            The subject text
     *
     * @return \Core\Mail\Mail
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Returns set body content
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets mail body content
     *
     * @param string $body
     *            The body content
     *
     * @return \Core\Mail\Mail
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Returns set altbody content
     *
     * @return string
     */
    public function getAltbody()
    {
        return $this->altbody;
    }

    /**
     * Sets altbody content for non html capable mail clients when mail is html
     *
     * @param string $altbody
     *            The altbody content
     *
     * @return \Core\Mail\Mail
     */
    public function setAltbody($altbody)
    {
        $this->altbody = $altbody;

        return $this;
    }

    /**
     * Returns all added attachements
     *
     * @return array
     */
    public function getAttachements()
    {
        return $this->attachements;
    }

    /**
     * Adds an attachement
     *
     * @param string $attachement
     *            Full path to attachement
     * @param string $name
     *            Optional title (Default: '')
     * @param string $encoding
     *            Optional encoding (Default: 'base64')
     * @param strng $type
     *            Optional attachement type (Default: 'application/octet-stream')
     *
     * @return \Core\Mail\Mail
     */
    public function addAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
    {
        $this->attachements[] = [
            'path' => $path,
            'name' => $name,
            'encoding' => $encoding,
            'type' => $type
        ];

        return $this;
    }

    /**
     * Removes all attachments
     *
     * @return \Core\Mail\Mail
     */
    public function clearAttachements()
    {
        $this->attachements = [];

        return $this;
    }

    /**
     * Returns all embedded images
     *
     * @return array
     */
    public function getEmbeddedImages()
    {
        return $this->images;
    }

    /**
     * Adds an embedded image
     *
     * @param string $path
     *            Full path to image
     * @param string $cid
     *            Content id of image
     * @param string $name
     *            Optional title (Default: '')
     * @param string $encoding
     *            Optional encoding (Default: 'base64')
     * @param strng $type
     *            Optional attachement type (Default: 'application/octet-stream')
     *
     * @return \Core\Mail\Mail
     */
    public function addEmbeddedImage($path, $cid, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
    {
        $this->images[] = [
            'path' => $path,
            'cid' => $cid,
            'name' => $name,
            'encoding' => $encoding,
            'type' => $type
        ];

        return $this;
    }

    /**
     * Removes all embeded images
     *
     * @return \Core\Mail\Mail
     */
    public function clearEmbeddedImages()
    {
        $this->images = [];

        return $this;
    }

    /**
     * Sets or returns html flag
     *
     * @param boolean $flag
     *            Optional boolean flag
     *
     * @return \Core\Mail\Mail
     */
    public function isHtml($flag = null)
    {
        if (empty($flag)) {
            return $this->html;
        }

        $this->html = (bool) $flag;

        return $this;
    }

    /**
     *
     * @return the $charset
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     *
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Returns from mailaddress and name
     *
     * @return array
     */
    public function getFrom()
    {
        return [
            'from' => $this->from,
            'name' => $this->fromname
        ];
    }

    /**
     * Set from and optional fromname property
     *
     * @param string $from
     *            The 'from' mailaddress
     * @param string $fromname
     *            Optional name the mail is from
     *
     * @return \Core\Mail\Mail
     */
    public function setFrom($from, $name = '')
    {
        $this->from = $from;

        if ($name) {
            $this->fromname = $name;
        }

        return $this;
    }

    /**
     * Adds one replyto contact to the replytolist
     *
     * @param string $mailaddress
     *            Contact reply to mailaddress
     *
     * @param string $name
     *            Optional contact replyto name
     *
     * @throws MailerException
     *
     * @return \Core\Mailer\Mail
     */
    public function addReplyto($mailaddress, $name = '')
    {
        $this->replyto[$mailaddress] = $name;

        return $this;
    }

    /**
     * Adds a list of contacts to the replytolist
     *
     * @param array $reply_tos
     *            List of contacts to add. This array can be an indexed array with only
     *            the contacts mailaddress or an assoc array with mailaddress as key and
     *            the contactss name as value.
     *
     * @throws MailerException
     *
     * @return \Core\Mail\Mail
     */
    public function addReplytos(array $reply_tos)
    {
        if ($this->arrayIsAssoc($reply_tos)) {
            foreach ($reply_tos as $mailaddress => $name) {
                $this->addReplyto($mailaddress, $name);
            }
        }
        else {
            foreach ($reply_tos as $mailaddress) {
                $this->addReplyto($mailaddress);
            }
        }

        return $this;
    }

    /**
     * Returns replyto stack
     *
     * @return array
     */
    public function getReplyto()
    {
        return $this->replyto;
    }

    /**
     * Adds one recipient to the recipientlist
     *
     * @param string $recipient
     *            Recipients mailaddress
     *
     * @param string $name
     *            Optional recipients name
     *
     * @throws MailerException
     *
     * @return \Core\Mailer\Mail
     */
    public function addRecipient($type, $recipient, $name = '')
    {
        $types = [
            'to',
            'cc',
            'bcc'
        ];

        if (! in_array($type, $types)) {
            Throw new MailerException(sprintf('The recipienttype "%" is not allowed. Please select from "to", "cc" or "bcc"'), $type);
        }

        $this->recipients[$type][$recipient] = $name;

        return $this;
    }

    /**
     * Adds a list of recipients to the recipientslist
     *
     * @param string $type
     *            Name of the list to add the recipients. Can be "to", "cc" or "bcc".
     * @param array $recipients
     *            List of recipients to add. This array can be an indexed array with only
     *            the recipients mailaddress or an assoc array with mailaddress as key and
     *            the recipients name as value.
     *
     * @throws MailerException
     *
     * @return \Core\Mail\Mail
     */
    public function addRecipients($type, array $recipients)
    {
        $types = [
            'to',
            'cc',
            'bcc'
        ];

        if (! in_array($type, $types)) {
            Throw new MailerException(sprintf('The recipienttype "%" is not allowed. Please select from "to", "cc" or "bcc"'), $type);
        }

        if ($this->arrayIsAssoc($recipients)) {
            foreach ($recipients as $recipient => $name) {
                $this->addRecipient($type, $recipients, $name);
            }
        }
        else {
            foreach ($recipients as $recipient) {
                $this->addRecipient($type, $recipient);
            }
        }

        return $this;
    }

    /**
     * Returns recipientlist
     *
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Clears recipientlist
     *
     * @return \Core\Mail\Mail
     */
    public function clearRecipients()
    {
        $this->recipients = [];

        return $this;
    }

    /**
     * Adds a recipient with optional name to recipients "to" list
     *
     * @param string $to
     *            Mailaddress to add to TO recipientlist
     * @param string $name
     *            Optional name for recipient
     *
     * @return \Core\Mail\Mail
     */
    public function addTo($to, $name = '')
    {
        $this->addRecipient('to', $to, $name);

        return $this;
    }

    /**
     * Adds a arraylist of recipients to recipients "to" list.
     *
     * Works similiar to addRecipients() method.
     *
     * @param array $tos
     *            List of recipients to add. This array can be an indexed array with only
     *            the recipients mailaddress or an assoc array with mailaddress as key and
     *            the recipients name as value.
     *
     * @return \Core\Mail\Mail
     */
    public function addTos(array $tos)
    {
        $this->addRecipients('to', $tos);

        return $this;
    }

    /**
     * Clears "to" recipientlist
     */
    public function clearTo()
    {
        $this->recipients['to'] = [];

        return $this;
    }

    /**
     * Adds a recipient with optional name to recipients "cc" list
     *
     * @param string $cc
     *            Mailaddress to add
     * @param string $name
     *            Optional name for recipient
     *
     * @return \Core\Mail\Mail
     */
    public function addCc($cc, $name = '')
    {
        $this->addRecipient('cc', $cc, $name);

        return $this;
    }

    /**
     * Adds a arraylist of recipients to recipients "cc" list.
     *
     * Works similiar to addRecipients() method.
     *
     * @param array $ccs
     *            List of recipients to add. This array can be an indexed array with only
     *            the recipients mailaddress or an assoc array with mailaddress as key and
     *            the recipients name as value.
     *
     * @return \Core\Mail\Mail
     */
    public function addCcs(array $tos)
    {
        $this->addRecipients('cc', $tos);

        return $this;
    }

    /**
     * Clears "cc" recipientlist
     */
    public function clearCc()
    {
        $this->recipients['cc'] = [];

        return $this;
    }

    /**
     * Adds a recipient with optional name to recipients "bcc" list
     *
     * @param string $bcc
     *            Mailaddress to add
     * @param string $name
     *            Optional name for recipient
     *
     * @return \Core\Mail\Mail
     */
    public function addBcc($bcc, $name = '')
    {
        $this->addRecipient('bcc', $bcc, $name);

        return $this;
    }

    /**
     * Adds a arraylist of recipients to recipients "bcc" list.
     *
     * Works similiar to addRecipients() method.
     *
     * @param array $bccs
     *            List of recipients to add. This array can be an indexed array with only
     *            the recipients mailaddress or an assoc array with mailaddress as key and
     *            the recipients name as value.
     *
     * @return \Core\Mail\Mail
     */
    public function addBcs(array $tos)
    {
        $this->addRecipients('bcc', $tos);

        return $this;
    }

    /**
     * Clears "bcc" recipientlist
     */
    public function clearBcc()
    {
        $this->recipients['bcc'] = [];

        return $this;
    }

    /**
     * Set mail encoding
     *
     * @param string $encoding
     *            Encoding type
     *
     * @return \Core\Mailer\Mail
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Returns encoding type
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Sets mailaddress to which a reading confirm message should be sent
     *
     * @param string $mailadress
     *            Mailaddress to send confirmmail to
     *
     * @return \Core\Mail\Mail
     */
    public function setConfirmReadingTo($mailadress)
    {
        $this->confirm_reading_to = $mailadress;

        return $this;
    }

    /**
     * Returns set mailaddress to which a confirm message should be sent
     *
     * @return string
     */
    public function getConfirmReadingTo()
    {
        return $this->confirm_reading_to;
    }

    /**
     * Adds custom header with optional value
     *
     * @param string $header
     *            custom header string
     * @param string $value
     *            Optional header value
     *
     * @return \Core\Mail\Mail
     */
    public function addHeader($header, $value = '')
    {
        $this->headers[$header] = $value;

        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function cleanHeaders()
    {
        $this->headers = [];

        return $this;
    }

    /**
     * Returns mail priority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Sets mail priority
     *
     * @param int $priority
     *            The mail priority value. Has to be between 1 (high) to 5 (low)
     *
     * @throws MailerException
     *
     * @return \Core\Mail\Mail
     */
    public function setPriority($priority)
    {
        if ($priority < 1 || $priority > 5) {
            Throw new MailerException('Mail priority has to be betwenn 1 (high) to 5 (low)');
        }

        $this->priority = $priority;

        return $this;
    }

    /**
     * Returns the set id of the registered MTA
     *
     * @throws MailerException
     *
     * @return string
     */
    public function getMTA()
    {
        if (! $this->MTA) {
            Throw new MailerException('No MTA id set.');
        }

        return $this->MTA;
    }

    /**
     * Id of the MTA to use for sending mail
     *
     * @param integer $id
     *            The id of registered MTA to use for sending this mail
     *
     * @return \Core\Mail\Mail
     */
    public function setMTA($id)
    {
        $this->MTA = $id;

        return $this;
    }

    /**
     * Injects reference to Mailer service object
     *
     * @param Mailer $mailer
     *
     * @return \Core\Mail\Mail
     */
    public function injectMailer(Mailer $mailer)
    {
        $this->mailer = $mailer;

        return $this;
    }
}