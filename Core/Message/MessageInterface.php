<?php
namespace Core\Message;

/**
 * MessageInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface MessageInterface
{

    const PRIMARY = 'primary';

    const SUCCESS = 'success';

    const INFO = 'info';

    const WARNING = 'warning';

    const DANGER = 'danger';

    const CLEAR = 'clear';

    /**
     * Returns message text
     *
     * @return string
     */
    public function getMessage();

    /**
     * Sets message type
     *
     * @param string $type
     */
    public function setType($type);

    /**
     * Returns set message type
     *
     * @return string
     */
    public function getType();

    /**
     * Switches fadeout on or off
     *
     * @param bool $fadeout
     */
    public function setFadeout($fadeout);

    /**
     * Returns set fadeout time
     *
     * @return boolean
     */
    public function getFadeout();

    /**
     * Switches dismissable button on/off
     *
     * @param bool $dismissable
     */
    public function setDismissable($dismissable);

    /**
     * Returns set dismissable flag
     *
     * @return boolean
     */
    public function getDismissable();

    /**
     * Set the DOM target where the message should be showed.
     *
     * This only applies to ajax requests.
     * On full requests a message will always be shown in the default messagearea.
     *
     * @param string $target
     */
    public function setTarget($target = '#core-message');

    /**
     * Returns the DOM target of the message
     *
     * @return string
     */
    public function getTarget();

    /**
     * Sets the function to use when the message should be displayed
     *
     * You can use all common functions like append, prepend,html etc.
     * This only applies to ajax requests.
     *
     * You can use the common function
     *
     * @param string $function
     */
    public function setDisplayFunction($function='append');

    /**
     * Returns the set display function
     *
     * This only applies to ajax requests.
     *
     * @return string
     */
    public function getDisplayFunction();
}
