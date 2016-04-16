<?php
namespace Core\Page\Body\Message;

use Core\Http\Session;

/**
 * Message.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Message
{

    /**
     * Constructor
     */
    public function __construct()
    {

        // Init messages stack
        $_SESSION['Core']['messages'] = [];
    }

    /**
     * Generic function to create a message object
     *
     * This method is called by public message methods
     *
     * @param string $type
     *            Message type, which should be "info", "success", "warning" or "danger"
     * @param string $message
     *            The message to show
     * @param string $fadeout
     *            Optional flag to signal that this message should fadeout after some time
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
     * Clears message area
     *
     * @return MessageObject
     */
    public function clear()
    {
        $msg = new MessageObject();
        $msg->setType('clear');

        $this->add($msg);

        return $msg;
    }

    /**
     * Adds a message object to the message storage in session
     *
     * @param MessageObject $msg
     *            MessageObject to add
     */
    public function add(MessageObject &$msg)
    {
        $_SESSION['Core']['messages'][] = $msg;
    }

    /**
     * Creates "succcess" message and returns reference to this messages
     *
     * @param string $message
     *            The message to show
     * @param bool $fadeout
     *            Optional flag to switch Automatic fadeout
     *
     * @return MessageObject
     */
    public function success($message, $fadeout = true)
    {
        return $this->generic('success', $message, $fadeout);
    }

    /**
     * Creates "info" message and returns reference to this messages
     *
     * @param string $message
     *            The message to show
     * @param bool $fadeout
     *            Optional flag to switch Automatic fadeout
     *
     * @return MessageObject
     */
    public function info($message, $fadeout = true)
    {
        return $this->generic('info', $message, $fadeout);
    }

    /**
     * Creates "warning" message and returns reference to this messages
     *
     * @param string $message
     *            The message to show
     * @param bool $fadeout
     *            Optional flag to switch Automatic fadeout
     *
     * @return MessageObject
     */
    public function warning($message, $fadeout = true)
    {
        return $this->generic('warning', $message, $fadeout);
    }

    /**
     * Creates "danger" message and returns reference to this messages
     *
     * @param string $message
     *            The message to show
     * @param bool $fadeout
     *            Optional flag to switch Automatic fadeout
     *
     * @return MessageObject
     */
    public function danger($message, $fadeout = true)
    {
        return $this->generic('danger', $message, $fadeout);
    }

    /**
     * Returns messages stack, which may be empty, and resets it
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = $_SESSION['Core']['messages'];
        $this->resetMessages();

        return $messages;
    }

    /**
     * Resets messages stack
     */
    public function resetMessages()
    {
        $_SESSION['Core']['messages'] = [];
    }
}
