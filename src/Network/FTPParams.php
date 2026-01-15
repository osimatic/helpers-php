<?php

namespace Osimatic\Network;

/**
 * Class FTPParams
 * Holds FTP connection parameters
 */
class FTPParams
{
	/**
	 * @param string $host FTP server host
	 * @param string $userName FTP username
	 * @param string $password FTP password
	 * @param int $port FTP port (default: 21)
	 */
	public function __construct(
		private string $host,
		private string $userName,
		private string $password,
		private int $port=21,
	) {
	}

	/**
	 * Gets the FTP server host
	 * @return string the FTP server host
	 */
	public function getHost(): string
	{
		return $this->host;
	}

	/**
	 * Sets the FTP server host
	 * @param string $host the FTP server host
	 * @return void
	 */
	public function setHost(string $host): void
	{
		$this->host = $host;
	}

	/**
	 * Gets the FTP username
	 * @return string the FTP username
	 */
	public function getUserName(): string
	{
		return $this->userName;
	}

	/**
	 * Sets the FTP username
	 * @param string $userName the FTP username
	 * @return void
	 */
	public function setUserName(string $userName): void
	{
		$this->userName = $userName;
	}

	/**
	 * Gets the FTP password
	 * @return string the FTP password
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * Sets the FTP password
	 * @param string $password the FTP password
	 * @return void
	 */
	public function setPassword(string $password): void
	{
		$this->password = $password;
	}

	/**
	 * Gets the FTP port
	 * @return int the FTP port
	 */
	public function getPort(): int
	{
		return $this->port;
	}

	/**
	 * Sets the FTP port
	 * @param int $port the FTP port
	 * @return void
	 */
	public function setPort(int $port): void
	{
		$this->port = $port;
	}
}