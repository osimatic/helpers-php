<?php

namespace Osimatic\API;

/**
 * Client for interacting with the MantisBT (Mantis Bug Tracker) API via SOAP.
 * MantisBT is an open-source issue tracking system that helps manage software defects and feature requests.
 * This class provides methods to create and manage issues/bugs in a Mantis installation.
 */
class Mantis
{
	/** Cached SOAP client instance for reuse across multiple API calls */
	private ?\SoapClient $soapClient = null;

	/**
	 * Initializes a new MantisBT API client instance.
	 * @param string|null $url The base URL of the Mantis installation (e.g., 'https://mantis.example.com/')
	 * @param string|null $userId The Mantis user ID for issue assignment
	 * @param string|null $userName The Mantis username for authentication
	 * @param string|null $userPassword The Mantis user password for authentication
	 */
	public function __construct(
		private ?string $url = null,
		private ?string $userId = null,
		private ?string $userName = null,
		private ?string $userPassword = null,
	) {}

	/**
	 * Sets the base URL of the Mantis installation.
	 * @param string $url The base URL (e.g., 'https://mantis.example.com/')
	 * @return self Returns this instance for method chaining
	 */
	public function setUrl(string $url): self
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * Sets the Mantis user ID for issue assignment.
	 * @param string $userId The user ID in the Mantis system
	 * @return self Returns this instance for method chaining
	 */
	public function setUserId(string $userId): self
	{
		$this->userId = $userId;

		return $this;
	}

	/**
	 * Sets the Mantis username for authentication.
	 * @param string $userName The username in the Mantis system
	 * @return self Returns this instance for method chaining
	 */
	public function setUserName(string $userName): self
	{
		$this->userName = $userName;

		return $this;
	}

	/**
	 * Sets the Mantis user password for authentication.
	 * @param string $userPassword The user password for SOAP API authentication
	 * @return self Returns this instance for method chaining
	 */
	public function setUserPassword(string $userPassword): self
	{
		$this->userPassword = $userPassword;

		return $this;
	}

	/**
	 * Creates a new issue (bug/feature request) in the Mantis bug tracker via the SOAP API.
	 * The issue will be assigned to the user specified during construction or via setUserId() and setUserName().
	 * @param int $projectId The Mantis project ID where the issue should be created
	 * @param string $title The issue summary/title (brief description)
	 * @param string $desc The detailed issue description
	 * @param int $severity The severity level ID (e.g., 10=feature, 20=trivial, 30=text, 40=tweak, 50=minor, 60=major, 70=crash, 80=block)
	 * @param string|null $projectName Optional project name for display purposes
	 * @param string $category The issue category (default: 'General')
	 * @param int|null $priority Optional priority level ID
	 * @param string|null $reproducibility Optional reproducibility description
	 * @param array|null $customFields Optional custom fields as key-value pairs
	 * @return int|false The created issue ID if successful, false on failure (invalid configuration or SOAP error)
	 */
	public function addIssue(
		int $projectId,
		string $title,
		string $desc,
		int $severity,
		?string $projectName = null,
		string $category = 'General',
		?int $priority = null,
		?string $reproducibility = null,
		?array $customFields = null
	): int|false
	{
		if (empty($this->url) || empty($this->userName) || empty($this->userPassword) || empty($this->userId)) {
			return false;
		}

		try {
			$issueData = [
				'summary' => $title,
				'description' => $desc,
				'handler' => ['id' => $this->userId, 'name' => $this->userName],
				'project' => ['id' => $projectId, 'name' => $projectName ?? ''],
				'severity' => ['id' => $severity],
				'category' => $category,
			];

			if ($priority !== null) {
				$issueData['priority'] = ['id' => $priority];
			}

			if ($reproducibility !== null) {
				$issueData['reproducibility'] = ['name' => $reproducibility];
			}

			if ($customFields !== null && !empty($customFields)) {
				$issueData['custom_fields'] = $customFields;
			}

			$issueId = $this->getSoapClient()->mc_issue_add($this->userName, $this->userPassword, $issueData);

			return (int) $issueId;
		} catch (\SoapFault) {
			return false;
		}
	}

	/**
	 * Gets or creates a SOAP client instance for communicating with the Mantis API.
	 * The client is cached after first creation for performance optimization.
	 * @return \SoapClient The SOAP client instance
	 * @throws \RuntimeException If the URL is not configured or SOAP connection fails
	 */
	private function getSoapClient(): \SoapClient
	{
		if ($this->soapClient === null) {
			if (empty($this->url)) {
				throw new \RuntimeException('Mantis URL not configured. Use setUrl() or pass URL to constructor.');
			}

			try {
				$this->soapClient = new \SoapClient(
					$this->url . 'api/soap/mantisconnect.php?wsdl',
					['cache_wsdl' => \WSDL_CACHE_MEMORY]
				);
			} catch (\SoapFault $e) {
				throw new \RuntimeException('Failed to connect to Mantis SOAP API: ' . $e->getMessage(), 0, $e);
			}
		}

		return $this->soapClient;
	}

}