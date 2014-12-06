<?php
namespace Core\Lib\Content;

/**
 * Message class for flash messages.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class MessageObject
{
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
	 * @throws NoValidParameterError
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
			'default'
		];

		if (! in_array($type, $types)) {
			Throw new \InvalidArgumentException('Wrong type set for message.');
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
		$this->fadeout = is_bool($fadeout) ? $fadeout : false;

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
}
