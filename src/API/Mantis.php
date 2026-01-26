<?php

namespace Osimatic\API;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Osimatic\Network\HTTPRequestExecutor;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Client for interacting with the MantisBT (Mantis Bug Tracker) REST API.
 * MantisBT is an open-source issue tracking system that helps manage software defects and feature requests.
 * This class provides methods to create and manage issues/bugs in a Mantis installation using the REST API.
 * API Documentation: https://documenter.getpostman.com/view/29959/mantis-bug-tracker-rest-api/7Lt6zkP
 * @see https://www.testingdocs.com/mantisbt-rest-api-guide/
 * @see https://mantisbt.org/docs/master/en-US/Developers_Guide/html/restapi.html
 */
class Mantis
{
	/** HTTP request executor for making API calls */
	private HTTPRequestExecutor $requestExecutor;

	/**
	 * Initializes a new MantisBT REST API client instance.
	 * @param string|null $url The base URL of the Mantis installation (e.g., 'https://mantis.example.com/')
	 * @param string|null $apiToken The Mantis API token for authentication (generated in My Account – API tokens)
	 * @param string|null $userId The Mantis user ID for issue assignment (handler)
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 * @param ClientInterface $httpClient The PSR-18 HTTP client instance used for making API requests (default: HTTPClient)
	 */
	public function __construct(
		private ?string $url = null,
		private ?string $apiToken = null,
		private ?string $userId = null,
		private readonly LoggerInterface $logger = new NullLogger(),
		ClientInterface $httpClient = new HTTPClient(),
	)
	{
		$this->requestExecutor = new HTTPRequestExecutor($httpClient, $logger);
	}

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
	 * Sets the API token for authentication.
	 * API tokens can be generated via My Account – API tokens in MantisBT interface.
	 * @param string $apiToken The API token
	 * @return self Returns this instance for method chaining
	 */
	public function setApiToken(string $apiToken): self
	{
		$this->apiToken = $apiToken;

		return $this;
	}

	/**
	 * Sets the Mantis user ID for issue assignment (handler).
	 * @param string $userId The user ID in the Mantis system
	 * @return self Returns this instance for method chaining
	 */
	public function setUserId(string $userId): self
	{
		$this->userId = $userId;

		return $this;
	}

	/**
	 * Creates a new issue (bug/feature request) in the Mantis bug tracker via the REST API.
	 * The issue will be assigned to the user specified during construction or via setUserId().
	 *
	 * @param int $projectId The Mantis project ID where the issue should be created
	 * @param string $title The issue summary/title (brief description)
	 * @param string $desc The detailed issue description
	 * @param int $severity The severity level ID (e.g., 10=feature, 20=trivial, 30=text, 40=tweak, 50=minor, 60=major, 70=crash, 80=block)
	 * @param string|null $projectName Optional project name (alternative to projectId)
	 * @param string $category The issue category (default: 'General')
	 * @param int|null $priority Optional priority level ID
	 * @param string|null $reproducibility Optional reproducibility description
	 * @param array|null $customFields Optional custom fields as key-value pairs
	 * @return int|false The created issue ID if successful, false on failure (invalid configuration or API error)
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
		if (empty($this->url)) {
			$this->logger->error('Mantis: URL is required for REST API calls');
			return false;
		}

		if (empty($this->apiToken)) {
			$this->logger->error('Mantis: API token is required for authentication');
			return false;
		}

		try {
			// Build the request payload according to Mantis REST API specification
			$issueData = [
				'summary' => $title,
				'description' => $desc,
				'category' => ['name' => $category],
				'severity' => ['id' => $severity],
			];

			// Use project name if provided, otherwise use ID
			if ($projectName !== null) {
				$issueData['project'] = ['name' => $projectName];
			} else {
				$issueData['project'] = ['id' => $projectId];
			}

			// Add handler (assigned user) if specified
			if ($this->userId !== null) {
				$issueData['handler'] = ['id' => $this->userId];
			}

			// Add optional fields
			if ($priority !== null) {
				$issueData['priority'] = ['id' => $priority];
			}

			if ($reproducibility !== null) {
				$issueData['reproducibility'] = ['name' => $reproducibility];
			}

			if (!empty($customFields)) {
				$issueData['custom_fields'] = $customFields;
			}

			$this->logger->debug('Mantis: Creating issue via REST API', [
				'project_id' => $projectId,
				'summary' => $title,
			]);

			// Make POST request to create issue
			$url = rtrim($this->url, '/') . '/api/rest/issues/';
			$response = $this->requestExecutor->execute(
				HTTPMethod::POST,
				$url,
				$issueData,
				headers: [
					'Authorization' => $this->apiToken,
					'Content-Type' => 'application/json',
				],
				jsonBody: true,
				decodeJson: true
			);

			if ($response === null) {
				$this->logger->error('Mantis: Failed to create issue - null response', [
					'project_id' => $projectId,
					'title' => $title,
				]);
				return false;
			}

			// Extract issue ID from response
			$issueId = $response['issue']['id'] ?? null;

			if ($issueId === null) {
				$this->logger->error('Mantis: Issue ID not found in response', [
					'response' => $response,
				]);
				return false;
			}

			$this->logger->info('Mantis: Issue created successfully via REST API', [
				'issue_id' => $issueId,
				'project_id' => $projectId,
				'title' => $title,
			]);

			return (int) $issueId;
		} catch (\Throwable $e) {
			$this->logger->error('Mantis: Error while creating issue via REST API', [
				'exception' => get_class($e),
				'message' => $e->getMessage(),
				'project_id' => $projectId,
				'title' => $title,
			]);
			return false;
		}
	}

}