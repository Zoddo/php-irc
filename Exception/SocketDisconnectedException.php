<?php

namespace Zoddo\irc\Exception;

/**
 * Class SocketDisconnectedException
 * @package Zoddo\irc
 */
class SocketDisconnectedException extends SocketErrorException
{
	/**
	 * @param string $message
	 * @param \Exception $previous
	 */
	public function __construct($message = null, \Exception $previous = null)
	{
		if (empty($message))
		{
			$message = 'Socket disconnected';
		}

		parent::__construct($message, 500, $previous);
	}
}