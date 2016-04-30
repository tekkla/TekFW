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
     * Returns set dismissable flag.
     *
     * @return boolean
     */
    public function getDismissable();
}
