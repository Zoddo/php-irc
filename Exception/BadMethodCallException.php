<?php

namespace Zoddo\irc\Exception;

/**
 * Class BadMethodCallException
 * @package Zoddo\irc
 */
class BadMethodCallException extends \BadMethodCallException
{
	/**
	 * @param string $message
	 * @param \Exception $previous
	 */
	public function __construct($message, \Exception $previous = null)
	{
		parent::__construct($message, 500, $previous);
	}
}