<?php

namespace Osimatic\Helpers\API;

/**
 * Class Mantis
 * @package Osimatic\Helpers\API
 */
class Mantis
{
	private $url;
	private $userId;
	private $userName;
	private $userPassword;

	/**
	 * Mantis constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * @param string $url
	 * @return self
	 */
	public function setUrl(string $url): self
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * @param string $userId
	 * @return self
	 */
	public function setUserId(string $userId): self
	{
		$this->userId = $userId;

		return $this;
	}

	/**
	 * @param string $userName
	 * @return self
	 */
	public function setUserName(string $userName): self
	{
		$this->userName = $userName;

		return $this;
	}

	/**
	 * @param string $userPassword
	 * @return self
	 */
	public function setUserPassword(string $userPassword): self
	{
		$this->userPassword = $userPassword;

		return $this;
	}


	public function addIssue(int $projectId, string $title, string $desc, int $serverity, string $projectName=null)
	{
		$soapIssueAdd = new \SoapClient($this->url.'api/soap/mantisconnect.php?wsdl');
		$soapIssueAdd->mc_issue_add($this->userName, $this->userPassword, [
			'summary' 		=> iconv('windows-1252', 'UTF-8', $title),
			'description' 	=> iconv('windows-1252', 'UTF-8', $desc),
			'handler' 		=> ['id'=> $this->userId, 'name' => $this->userName],
			'project' 		=> ['id' => $projectId, 'name' => $projectName ?? ''],
			'severity' 		=> ['id' => $serverity],
			'category' 		=> 'General'
		]);
	}

	public static function getImpactList()
	{
		return [
			50 => 'Mineur',
			60 => 'Majeur',
			70 => 'Critique',
			80 => 'Bloquant',
		];
	}

}