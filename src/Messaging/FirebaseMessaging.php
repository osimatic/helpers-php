<?php

namespace Osimatic\Messaging;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class FirebaseMessaging implements MobilePushNotificationSenderInterface
{
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
	 * Send the message to the device
	 * @param PushNotificationInterface $mobilePushNotification
	 * @return PushNotificationSendingResponse
	 */
	public function send(PushNotificationInterface $mobilePushNotification): PushNotificationSendingResponse
	{
		if (empty($this->projectId)) {
			$this->logger->error('Project ID not set');
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::SETTINGS_INVALID);
		}

		if (empty($this->serviceKeyFile)) {
			$this->logger->error('Server key file not set');
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::SETTINGS_INVALID);
		}

		$url = 'https://fcm.googleapis.com/v1/projects/'.$this->projectId.'/messages:send';

		if (!is_a($subscription = $mobilePushNotification->getSubscription(), MobilePushNotificationSubscriptionInterface::class) || empty($deviceToken = $subscription->getDeviceToken())) {
			$this->logger->error('No device set');
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::TOKEN_INVALID);
		}

		$fields = [
			'message' => [
				'name' => 'projects/*/messages/'.date('YmdHis').'-'.uniqid('', true),
				'token' => $deviceToken,
				'notification' => [
					'title' => $mobilePushNotification->getTitle(),
					'body' => $mobilePushNotification->getMessage(),
				],
			],
		];

		if (null !== $mobilePushNotification->getTimeToLive()) {
			$fields['android']['ttl'] = $mobilePushNotification->getTimeToLive();
		}

		if (null !== $mobilePushNotification->getCollapseKey()) {
			$fields['android']['collapse_key'] = $mobilePushNotification->getCollapseKey();
		}

		if (null !== $mobilePushNotification->getData()) {
			foreach ($mobilePushNotification->getData() as $key => $value) {
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
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::HTTP);
		}

		try {
			$responseData = \GuzzleHttp\Utils::jsonDecode((string) $result->getBody(), true);
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::UNKNOWN);
		}

		$this->logger->info((string) $result->getBody());

		if (null === $responseData) {
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::UNKNOWN);
		}

		if (!empty($responseData['error'])) {
			$errorStatus = $responseData['error']['status'] ?? null;
			if ('INVALID_ARGUMENT' === $errorStatus) {
				return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::SETTINGS_INVALID, $responseData);
			}
			if ('UNREGISTERED' === $errorStatus) {
				return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::TOKEN_EXPIRED, $responseData);
			}
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::UNKNOWN, $responseData);
		}

		return new PushNotificationSendingResponse(true, null, $responseData);
	}
}