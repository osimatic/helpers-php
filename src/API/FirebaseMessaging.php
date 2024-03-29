<?php

namespace Osimatic\Helpers\API;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class FirebaseMessaging
{
	/**
	 * @var array|null
	 */
	private ?array $result;

	public function __construct(
		private ?string $projectId = null,
		private ?string $serviceKeyFile = null,
		private LoggerInterface $logger = new NullLogger(),
	) {}

	/**
	 * @param string $projectId
	 * @return self
	 */
	public function setProjectId(string $projectId): self
	{
		$this->projectId = $projectId;

		return $this;
	}

	/**
	 * @param string $serviceKeyFile
	 * @return self
	 */
	public function setServiceKeyFile(string $serviceKeyFile): self
	{
		$this->serviceKeyFile = $serviceKeyFile;

		return $this;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * @return array|null
	 */
	public function getResult(): ?array
	{
		return $this->result;
	}



	/**
	 * Send the message to the device
	 * @param string $deviceId device tokens to send to
	 * @param string $title The title of message
	 * @param string $message The message to send
	 * @param array|null $data Array of data to accompany the message
	 * @param string|null $collapseKey
	 * @param int|null $timeToLive
	 * @return bool
	 */
	public function send(string $deviceId, string $title, string $message, ?array $data=null, ?string $collapseKey=null, ?int $timeToLive=null): bool
	{
		if (empty($this->projectId)) {
			$this->logger->error('Project ID not set');
			return false;
		}

		$url = 'https://fcm.googleapis.com/v1/projects/'.$this->projectId.'/messages:send';

		if (empty($deviceId)) {
			$this->logger->error('No device set');
			return false;
		}

		if (empty($this->serviceKeyFile)) {
			$this->logger->error('Server key file not set');
			return false;
		}

		$fields = [
			'message' => [
				'name' => 'projects/*/messages/'.date('YmdHis').'-'.uniqid(),
				'token' => $deviceId,
				'notification' => [
					'title' => $title,
					'body' => $message,
				],
			],
		];

		if ($timeToLive !== null) {
			$fields['android']['ttl'] = $timeToLive;
		}

		if ($collapseKey !== null) {
			$fields['android']['collapse_key'] = $collapseKey;
		}

		if (null !== $data) {
			foreach ($data as $key => $value) {
				$fields['data'][$key] = $value;
			}
		}

		putenv('GOOGLE_APPLICATION_CREDENTIALS='.$this->serviceKeyFile);
		$client = new \Google_Client();
		$client->useApplicationDefaultCredentials();
		$client->addScope('https://www.googleapis.com/auth/firebase.messaging');
		$httpClient = $client->authorize();

		try {
			$result = $httpClient->request('POST', $url, [
				'http_errors' => false,
				// 'headers' => [],
				'body' => json_encode($fields)
			]);
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			$this->logger->error($e->getMessage());
			return false;
		}

		try {
			$jsonResult = \GuzzleHttp\Utils::jsonDecode((string) $result->getBody(), true);
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
			return false;
		}

		$this->logger->info((string) $result->getBody());

		$this->result = $jsonResult;

		//return $jsonResult;
		return true;
	}

	public function isTokenInvalid(): bool
	{
		if (null === $this->result) {
			return true;
		}

		if (!empty($this->result['error']) && !empty($errorCode = $this->result['error']['code'] ?? null) && in_array($errorCode, [400, 404], true)) {
			return true;
		}

		return false;
	}

}