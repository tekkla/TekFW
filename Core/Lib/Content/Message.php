<?php
namespace Core\Lib\Content;

/**
 * Message class for flash messages.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class Message
{
	/**
	 * Predefined message types
	 * @var array
	 */
	private static $types = array(
		'primary',
		'success',
		'info',
		'warning',
		'danger',
		'default'
	);

	/**
	 * Message disply type
	 * @see self::$types
	 * @var string
	 */
	private $type;

	/**
	 * Message cpntent
	 * @var string
	 */
	private $message;

	/**
	 * Autmatic fadeout flag
	 * @var bool
	 */
	private $fadeout = true;

	/**
	 * Factory pattern for message creation.
	 * Creates a message object, sets the message,
	 * stores the object in the message container and returns a reference to this object.
	 * @param string $message
	 * @return Message
	 */
	public static function factory($message, $type = 'info', $fadeout = true)
	{
		if (!in_array($type, self::$types))
			Throw new \InvalidArgumentException('Wrong message type.', 1000, array(
				$type,
				self::$types
			));

		$obj = new Message();
		$obj->setMessage($message);
		$obj->setType($type);
		$obj->setFadeout($fadeout);
		return $obj->add();
	}

	/**
	 * Adds message object to session and returns a reference
	 * to this message object
	 * @throws Error
	 */
	public function &add()
	{
		// Errorhandling on no set message text
		if (!isset($this->message) || empty($this->message))
			Throw new \RuntimeException('No message set', 5002);

		// Assign this message to message session
		if (!isset($_SESSION['messages']))
			$_SESSION['messages'] = [];

		// Get current message counter
		$id = uniqid('core_message_');

		$_SESSION['messages'][$id] = $this;

		// Return reference to the message
		return $_SESSION['messages'][$id];
	}

	/**
	 * Creates "primary" message and returns reference to this messages.
	 * @param string $message Message content
	 * @param bool $fadeout Automatic fadeout. Set to false dto disable.
	 * @return Message
	 */
	public function primary($message, $fadeout = true)
	{
		$this->type = 'primary';
		$this->message = $message;
		$this->fadeout = $fadeout;
		return $this->add();
	}

	/**
	 * Creates "succcess" message and returns reference to this messages.
	 * @param string $message Message content
	 * @param bool $fadeout Automatic fadeout. Set to false dto disable.
	 * @return Message
	 */
	public function success($message, $fadeout = true)
	{
		$this->type = 'success';
		$this->message = $message;
		$this->fadeout = $fadeout;
		return $this->add();
	}

	/**
	 * Creates "info" message and returns reference to this messages.
	 * @param string $message Message content
	 * @param bool $fadeout Automatic fadeout. Set to false dto disable.
	 * @return Message
	 */
	public function info($message, $fadeout = true)
	{
		$this->type = 'info';
		$this->message = $message;
		$this->fadeout = $fadeout;
		return $this->add();
	}

	/**
	 * Creates "warning" message and returns reference to this messages.
	 * @param string $message Message content
	 * @param bool $fadeout Automatic fadeout. Set to false dto disable.
	 * @return Message
	 */
	public function warning($message, $fadeout = true)
	{
		$this->type = 'warning';
		$this->message = $message;
		$this->fadeout = $fadeout;
		return $this->add();
	}

	/**
	 * Creates "danger" message and returns reference to this messages.
	 * @param string $message Message content
	 * @param bool $fadeout Automatic fadeout. Set to false dto disable.
	 * @return Message
	 */
	public function danger($message, $fadeout = true)
	{
		$this->type = 'danger';
		$this->message = $message;
		$this->fadeout = $fadeout;
		return $this->add();
	}


	public function __call($name, $arguments)
	{
		switch ($name)
		{
			case 'error':
				$method = 'danger';
				break;
			default:
				$method = 'info';
		}


		$this->{$method}($arguments[0]);
	}

	/**
	 * Sets message content
	 * @param string $message
	 * @return \Core\Lib\Message
	 */
	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * Sets message type
	 * @param string $type
	 * @throws NoValidParameterError
	 * @return \Core\Lib\Message
	 */
	public function setType($type)
	{
		if (!in_array($type, self::$types))
			Throw new \InvalidArgumentException('Wrong type set for message.');

		$this->type = $type;
		return $this;
	}

	/**
	 * Switches fadeout on or off
	 * @param bool $fadeout
	 * @return \Core\Lib\Message
	 */
	public function setFadeout($fadeout)
	{
		$this->fadeout = is_bool($fadeout) ? $fadeout : false;
		return $this;
	}

	public function build()
	{
		return '
		<div class="alert alert-' . $this->type . ' alert-dismissable' . ( $this->fadeout ? ' fadeout' : '' ) . '
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true&times;</butto
			' . $this->message . '
		</di';
	}

	/**
	 * Check for set messages
	 */
	public static function checkMessages()
	{
		return isset($_SESSION['messages']) && !empty($_SESSION['messages']);
	}

	/**
	 * Returns set messages and resets the the messagestorage.
	 * If no message is set the method returns boolean false.
	 */
	public static function getMessages()
	{
		$return = isset($_SESSION['messages']) ? $_SESSION['messages'] : false;

		if ($return)
			self::resetMessages();

		return $return;
	}

	/**
	 * Resets messages in session
	 */
	public static function resetMessages()
	{
		unset($_SESSION['messages']);
	}
}

