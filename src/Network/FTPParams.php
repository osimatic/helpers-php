<?php

namespace Osimatic\Network;

class FTPParams
{
	public function __construct(
		private string $host,
		private string $userName,
		private string $password,
		private int $port=21,
	) {
	}

	public function getHost(): string
	{
		return $this->host;
	}

	public function setHost(string $host): void
	{
		$this->host = $host;
	}

	public function getUserName(): string
	{
		return $this->userName;
	}

	public function setUserName(string $userName): void
	{
		$this->userName = $userName;
	}

	public function getPassword(): string
	{
		return $this->password;
	}

	public function setPassword(string $password): void
	{
		$this->password = $password;
	}

	public function getPort(): int
	{
		return $this->port;
	}

	public function setPort(int $port): void
	{
		$this->port = $port;
	}
}