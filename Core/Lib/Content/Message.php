<?php
namespace Core\Lib\Content;

use Core\Lib\Http\Session;

/**
 * Message class for flash messages.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Message
{

    /**
     *
     * @var Session
     */
    private $session;

    /**
     * Constructor
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Generice function to create a message object
     * Called by public mapper methods
     *
     * @param string $type
     * @param string $message
     * @param string $fadeout
     *
     * @return MessageObject
     */
    private function generic($type, $message, $fadeout = true)
    {

        $msg = new MessageObject();

        $msg->setType($type);
        $msg->setMessage($message);
        $msg->setFadeout($fadeout);

        $this->add($msg);

        return $msg;
    }

    /**
     * Adds a message object to the message storage in session
     *
     * @param MessageObject $msg
     */
    public function add(MessageObject &$msg)
    {
        $this->session->add('messages', $msg);
    }

    /**
     * Creates "succcess" message and returns reference to this messages.
     *
     * @param string $message Message content
     * @param bool $fadeout Automatic fadeout. Set to false dto disable.
     *
     * @return MessageObject
     */
    public function success($message, $fadeout = true)
    {
        return $this->generic('success', $message, $fadeout);
    }

    /**
     * Creates "info" message and returns reference to this messages.
     *
     * @param string $message Message content
     * @param bool $fadeout Automatic fadeout. Set to false dto disable.
     *
     * @return MessageObject
     */
    public function info($message, $fadeout = true)
    {
        return $this->generic('info', $message, $fadeout);
    }

    /**
     * Creates "warning" message and returns reference to this messages.
     *
     * @param string $message Message content
     * @param bool $fadeout Automatic fadeout. Set to false dto disable.
     *
     * @return MessageObject
     */
    public function warning($message, $fadeout = true)
    {
        return $this->generic('warning', $message, $fadeout);
    }

    /**
     * Creates "danger" message and returns reference to this messages.
     *
     * @param string $message Message content
     * @param bool $fadeout Automatic fadeout. Set to false dto disable.
     *
     * @return MessageObject
     */
    public function danger($message, $fadeout = true)
    {
        return $this->generic('danger', $message, $fadeout);
    }

    /**
     * Checks for existing messages
     */
    public static function checkMessages()
    {
        return $this->session->exists('messages');
    }

    /**
     * Returns set messages and resets the the message storage.
     * If no message is set the method returns boolean false.
     */
    public function getMessages()
    {
        if (! $this->session->exists('messages')) {
            return [];
        }

        $messages = $this->session->get('messages');

        if ($messages) {
            $this->resetMessages();
        }

        return $messages;
    }

    /**
     * Resets messages in session
     */
    public function resetMessages()
    {
        $this->session->remove('messages');
    }
}
