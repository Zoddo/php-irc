<?php

namespace Zoddo\irc;

use Zoddo\irc\Exception\InvalidArgumentException;

/**
 * Class Event
 * @package Zoddo\irc
 */
class Event
{
	/**
	 * @var array
	 */
	protected $listeners = array();

	/**
	 * @var array
	 */
	protected $globalListeners = array();

	/**
	 * @var array
	 */
	protected $writeListeners = array();

	/**
	 * @param array|string $command
	 * @param callable $callback
	 * @return $this
	 */
	public function addListener($command, $callback)
	{
		$command = (array) $command;

		if (!is_callable($callback))
		{
			throw new InvalidArgumentException("The callback is invalid");
		}

		foreach ($command as $command_)
		{
			$this->listeners[strtolower($command_)][] = $callback;
		}

		return $this;
	}

	/**
	 * @param callable $callback
	 * @return $this
	 */
	public function addGlobalListener($callback)
	{
		if (!is_callable($callback))
		{
			throw new InvalidArgumentException("The callback is invalid");
		}

		$this->globalListeners[] = $callback;

		return $this;
	}

	/**
	 * @param callable $callback
	 * @return $this
	 */
	public function addWriteListener($callback)
	{
		if (!is_callable($callback))
		{
			throw new InvalidArgumentException("The callback is invalid");
		}

		$this->writeListeners[] = $callback;

		return $this;
	}

	/**
	 * @param string $command
	 * @param array $data
	 * @param IrcConnection $connection
	 * @return $this
	 */
	public function callListeners($command, array $data, IrcConnection $connection)
	{
		foreach ($this->globalListeners as $callback)
		{
			call_user_func($callback, $data, $connection);
		}

		if (array_key_exists(strtolower($command), $this->listeners))
		{
			foreach ($this->listeners[strtolower($command)] as $callback)
			{
				call_user_func($callback, $data, $connection);
			}
		}

		return $this;
	}

	/**
	 * @param string $data
	 * @param IrcConnection $connection
	 * @return $this
	 */
	public function callWriteListeners(&$data, IrcConnection $connection)
	{
		foreach ($this->writeListeners as $callback)
		{
			call_user_func_array($callback, array(&$data, $connection));
		}

		return $this;
	}
}