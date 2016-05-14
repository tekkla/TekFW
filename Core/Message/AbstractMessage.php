<?php
namespace Core\Message;

/**
 * AbstractMessage.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractMessage implements MessageInterface
{

    /**
     *
     * @var string
     */
    private $message = '';

    /**
     *
     * @var string
     */
    private $type = 'info';

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
     *
     * @var string
     */
    private $target = '#core-message';

    /**
     *
     * @var string
     */
    private $function = 'append';

    /**
     *
     * @var string
     */
    private $id = '';

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::setMessage()
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::getMessage()
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::setType()
     */
    public function setType($type)
    {
        $types = [
            'primary',
            'success',
            'info',
            'warning',
            'danger',
            'clear'
        ];

        if (! in_array($type, $types)) {
            Throw new MessageException(sprintf('Type "%s" is a not valid messagetype.', $type));
        }

        $this->type = $type;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::getType()
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::setFadeout()
     */
    public function setFadeout($fadeout)
    {
        $this->fadeout = (bool) $fadeout;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::getFadeout()
     */
    public function getFadeout()
    {
        return $this->fadeout;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::setDismissable()
     */
    public function setDismissable($dismissable)
    {
        $this->dismissable = (bool) $dismissable;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::getDismissable()
     */
    public function getDismissable()
    {
        return $this->dismissable;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::getTarget()
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::setTarget()
     */
    public function setTarget($target = '#core-message')
    {
        $this->target = $target;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::getDisplayFunction()
     */
    public function getDisplayFunction()
    {
        return $this->function;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::setDisplayFunction()
     */
    public function setDisplayFunction($function = 'append')
    {
        $this->function = $function;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::getId()
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\MessageInterface::setId()
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
