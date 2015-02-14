<?php

namespace Zoddo\irc;

use Zoddo\irc\Exception\InvalidArgumentException;
use Zoddo\irc\Exception\BadMethodCallException;

/**
 * Class Config
 * @package Zoddo\irc
 */
class Config
{
	/**
	 * @var string
	 */
	protected $host;

	/**
	 * @var int
	 */
	protected $port = 6667;

	/**
	 * @var bool
	 */
	protected $ssl = false;

	/**
	 * @var string
	 */
	protected $pass = null;

	/**
	 * @var string
	 */
	protected $nick;

	/**
	 * @var string
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $real;

	/**
	 * @param string $host
	 * @param string $nick
	 */
	public function __construct($host, $nick)
	{
		$this->setHost($host)
			->setNick($nick)
			->setUser()
			->setReal();
	}

	/**
	 * @param string $host
	 * @return $this
	 */
	public function setHost($host)
	{
		if (empty($host))
		{
			throw new InvalidArgumentException("The host can't be empty or null");
		}
		elseif (!is_string($host))
		{
			throw new InvalidArgumentException(sprintf("The host must be a string, %s given", gettype($host)));
		}

		$this->host = $host;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * @param int $port
	 * @return $this
	 */
	public function setPort($port = 6667)
	{
		if (empty($port))
		{
			$port = 6667;
		}
		elseif (!is_int($port))
		{
			throw new InvalidArgumentException(sprintf("The port must be an integer, %s given", gettype($port)));
		}

		$this->port = $port;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * @param bool $ssl
	 * @return $this
	 */
	public function setSsl($ssl = true)
	{
		if (!is_bool($ssl))
		{
			throw new InvalidArgumentException(sprintf("The SSL option must be a boolean, %s given", gettype($ssl)));
		}

		$this->ssl = $ssl;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getSsl()
	{
		return $this->ssl;
	}

	/**
	 * @param string $pass
	 * @return $this
	 */
	public function setPass($pass = null)
	{
		if (empty($pass))
		{
			$pass = null;
		}
		elseif (!is_string($pass))
		{
			throw new InvalidArgumentException(sprintf("The password must be a string, %s given", gettype($pass)));
		}

		$this->pass = $pass;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPass()
	{
		return $this->pass;
	}

	/**
	 * @param string $nick
	 * @return $this
	 */
	public function setNick($nick)
	{
		if (empty($nick))
		{
			throw new InvalidArgumentException("The nickname can't be empty or null");
		}
		elseif (!is_string($nick))
		{
			throw new InvalidArgumentException(sprintf("The nickname must be a string, %s given", gettype($nick)));
		}

		$this->nick = $nick;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNick()
	{
		return $this->nick;
	}

	/**
	 * @param string $user
	 * @return $this
	 */
	public function setUser($user = null)
	{
		if (empty($user))
		{
			$user = 'Zoddo-IRCBot';
		}
		elseif (!is_string($user))
		{
			throw new InvalidArgumentException(sprintf("The username must be a string, %s given", gettype($user)));
		}

		$this->user = $user;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param string $real
	 * @return $this
	 */
	public function setReal($real = null)
	{
		if (empty($real))
		{
			$real = 'PHP IRC Bot By Zoddo';
		}
		elseif (!is_string($real))
		{
			throw new InvalidArgumentException(sprintf("The realname must be a string, %s given", gettype($real)));
		}

		$this->real = $real;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getReal()
	{
		return $this->real;
	}

	public function __call($name, array $args)
	{
		switch(strtolower($name))
		{
			case 'sethostname':
			case 'setip':
				return call_user_func_array(array(&$this, 'setHost'), $args);

			case 'setpassword':
				return call_user_func_array(array(&$this, 'setPass'), $args);

			case 'setnickname':
				return call_user_func_array(array(&$this, 'setNick'), $args);

			case 'setusername':
				return call_user_func_array(array(&$this, 'setUser'), $args);

			case 'setrealname':
				return call_user_func_array(array(&$this, 'setReal'), $args);


			case 'gethostname':
			case 'getip':
				return call_user_func_array(array(&$this, 'getHost'), $args);

			case 'getpassword':
				return call_user_func_array(array(&$this, 'getPass'), $args);

			case 'getnickname':
				return call_user_func_array(array(&$this, 'getNick'), $args);

			case 'getusername':
				return call_user_func_array(array(&$this, 'getUser'), $args);

			case 'getrealname':
				return call_user_func_array(array(&$this, 'getReal'), $args);

			default:
				throw new BadMethodCallException(sprintf("Call to undefined method %s::%s", __CLASS__, $name));
		}
	}
}