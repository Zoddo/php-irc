<?php

namespace Zoddo\irc;

use Zoddo\irc\Exception\InvalidIrcResponseException;
use Zoddo\irc\Exception\SocketDisconnectedException;
use Zoddo\irc\Exception\SocketErrorException;

/**
 * Class IrcConnection
 * @package Zoddo\irc
 */
class IrcConnection
{
	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var Event
	 */
	protected $event;

	/**
	 * @var resource
	 */
	protected $socket = null;

	/**
	 * @param Config $config
	 * @param Event $event
	 */
	public function __construct(Config $config, Event $event = null)
	{
		if (!$event)
		{
			$event = new Event;
		}

		$this->config = $config;
		$this->event = $event;
	}

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * @return Event
	 */
	public function getEvent()
	{
		return $this->event;
	}

	/**
	 * @return $this
	 */
	public function connect()
	{
		$proto = ($this->config->getSsl()) ? 'ssl://' : 'tcp://';
		if (!$this->socket = fsockopen($proto . $this->config->getHost(), $this->config->getPort(), $errno, $errstr))
		{
			throw new SocketErrorException($errstr, $errno);
		}
		stream_set_blocking($this->socket, 0); // Set socket in non-blocking mode

		if ($this->config->getPass())
		{
			$this->send(sprintf('PASS %s', $this->config->getPass()));
		}
		$this->send(sprintf('NICK %s', $this->config->getNick()));
		$this->send(sprintf('USER %s 0 %s :%s', $this->config->getUser(), $this->config->getHost(), $this->config->getReal()));

		return $this;
	}

	/**
	 * @param int $wait
	 * @return array|false
	 */
	public function irc_read($wait = null)
	{
		if ($wait)
		{
			$data['raw'] = $this->read_wait($wait);
		}
		else
		{
			$data['raw'] = $this->read();
		}

		// Pas de données
		if ($data['raw'] === false)
		{
			return false;
		}

		// :hirin.ekinetirc.com 001 Zoddo :Welcome to the EkiNetIrc IRC Network Zoddo!Zoddo@rbz50-1-83-128-22-86.fbx.proxad.net
		if (!preg_match('#^(?::([a-z0-9~*!@./-]+) )?([A-Z]+|[0-9]{3})((?: [a-z0-9~*!@./-]+)?)((?: [^:](?:(?! :).)+)?)(?: :(.*))?$#i', $data['raw'], $part))
		{
			throw new InvalidIrcResponseException($data['raw']);
		}
		$data['source'] = trim($part[1]);
		$data['command'] = trim($part[2]);
		$data['dest'] = trim($part[3]);
		$data['params'] = array();
		if ($part[4] != '')
		{
			$data['params'] = explode(' ', trim($part[4]));
		}
		if (array_key_exists(5, $part))
		{
			$data['params'][] = trim($part[5]);
		}

		return $data;
	}

	/**
	 * @param array $data
	 * @return $this
	 */
	public function callListeners(array $data)
	{
		$this->event->callListeners($data['command'], $data, $this);

		return $this;
	}

	/**
	 * @param string $channel
	 * @param string $password
	 * @return $this
	 */
	public function join($channel, $password = '')
	{
		$this->send(sprintf('JOIN %s %s', $channel, $password));

		return $this;
	}

	/**
	 * @param string $dest
	 * @param string $message
	 * @return $this
	 */
	public function privmsg($dest, $message)
	{
		$this->send(sprintf('PRIVMSG %s :%s', $dest, $message));

		return $this;
	}

	/**
	 * @param string $dest
	 * @param string $message
	 * @return $this
	 */
	public function notice($dest, $message)
	{
		$this->send(sprintf('NOTICE %s :%s', $dest, $message));

		return $this;
	}

	/**
	 * @param string $channel
	 * @param string $message
	 * @return $this
	 */
	public function part($channel, $message = '')
	{
		$this->send(sprintf('PART %s :%s', $channel, $message));

		return $this;
	}

	/**
	 * @param string $message
	 * @return $this
	 */
	public function quit($message = '')
	{
		$this->send(sprintf('QUIT :%s',$message));

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disconnect()
	{
		if (!$this->feof())
		{
			fclose($this->socket);
		}
		$this->socket = null;

		return $this;
	}

	/**
	 * @return bool|string
	 */
	protected function read()
	{
		if ($this->feof())
		{
			throw new SocketDisconnectedException;
		}

		return fgets($this->socket);
	}

	/**
	 * @param float|int $timeout (in seconds)
	 * @return bool|string
	 */
	protected function read_wait($timeout = 0)
	{
		$start = microtime(true);
		do
		{
			if ($this->feof())
			{
				throw new SocketDisconnectedException;
			}

			$r = array($this->socket);
			$w = $e = null;
			if ($modified = stream_select($r, $w, $e, 1))
			{
				break;
			}
		} while($timeout && (microtime(true) - $start) <= $timeout);

		return ($modified) ? $this->read() : false;
	}

	/**
	 * @param string $data
	 * @return int|bool
	 */
	public function send($data)
	{
		if (substr($data, -1) != "\n")
		{
			$data .= "\n";
		}
		$this->event->callWriteListeners($data, $this);

		// http://php.net/manual/fr/function.fwrite.php#refsect1-function.fwrite-notes
		for ($written = 0; $written < strlen($data); $written += $fwrite)
		{
			if ($this->feof())
			{
				throw new SocketDisconnectedException;
			}

			$w = array($this->socket);
			$r = $e = null;
			stream_select($r, $w, $e, 0);

			$fwrite = fwrite($this->socket, substr($data, $written));
			if ($fwrite === false)
			{
				return $fwrite;
			}
		}
		return $written;
	}

	/**
	 * Check if the socket is disconnected
	 *
	 * @return bool
	 */
	public function feof()
	{
		if (!$this->socket)
		{
			return true;
		}

		$data = stream_get_meta_data($this->socket);

		return $data['eof'];
	}

	public function __destruct()
	{
		$this->disconnect();
	}
}