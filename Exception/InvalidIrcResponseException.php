<?php

namespace Zoddo\irc\Exception;

/**
 * Class InvalidIrcResponseException
 * @package Zoddo\irc
 */
class InvalidIrcResponseException extends \RuntimeException
{
	/**
	 * @var string
	 */
	protected $raw;

	/**
	 * @param string $raw
	 * @param string $message
	 * @param \Exception $previous
	 */
	public function __construct($raw, $message = null, \Exception $previous = null)
	{
		if (!$message)
		{
			$message = "The IRC response is invalid";
		}

		$this->raw = $raw;
		parent::__construct($message, 500, $previous);
	}

	/**
	 * @return string
	 */
	public function getRaw()
	{
		return $this->raw;
	}
}