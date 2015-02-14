<?php

namespace Zoddo\irc\EventListener;

use Zoddo\irc\IrcConnection;

/**
 * Class PingResponder
 * @package Zoddo\irc
 */
class PingResponder
{
	/**
	 * @param array $data
	 * @param IrcConnection $connection
	 */
	public function onPing(array $data, IrcConnection $connection)
	{
		$connection->send(sprintf('PONG %s :%s', $data['source'], $data['params'][0]));
	}
}