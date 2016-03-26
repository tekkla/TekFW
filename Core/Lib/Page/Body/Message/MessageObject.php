<?php
namespace Core\Lib\Page\Body\Message;

use Core\Lib\Page\PageException;

/**
 * MessageObject.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class MessageObject
{

    /**
     *
     * @var string
     */
    private $message;

    /**
     *
     * @var string
     */
    private $type = 'default';

    /**
     *
     * @var boolean
     */
    private $fadeout = true;

    /**
     *
     * @var boolean
     */
    private $dismissable = true;

    /**
     * Sets message content
     *
     * @param string $message
     *
     * @return \Core\Lib\Message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Returns message text
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets message type
     *
     * @param string $type
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Message
     */
    public function setType($type)
    {
        $types = [
            'primary',
            'success',
            'info',
            'warning',
            'danger',
            'default',
            'clear'
        ];

        if (! in_array($type, $types)) {
            Throw new PageException('Wrong type set for message.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Returns set message type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Switches fadeout on or off
     *
     * @param bool $fadeout
     *
     * @return \Core\Lib\Message
     */
    public function setFadeout($fadeout)
    {
        $this->fadeout = (bool) $fadeout;

        return $this;
    }

    /**
     * Returns set fadeout time
     *
     * @return boolean
     */
    public function getFadeout()
    {
        return $this->fadeout;
    }

    /**
     * Switches dismissable button on/off
     *
     * @param bool $dismissable
     *
     * @return \Core\Lib\Message
     */
    public function setDismissable($dismissable)
    {
        $this->dismissable = (bool) $dismissable;

        return $this;
    }

    /**
     * Returns set dismissable flag.
     *
     * @return boolean
     */
    public function getDismissable()
    {
        return $this->dismissable;
    }
}
