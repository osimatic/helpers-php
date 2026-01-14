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

		if (!file_exists($this->serviceKeyFile)) {
			$this->logger->error('Service key file does not exist: ' . $this->serviceKeyFile);
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::SETTINGS_INVALID);
		}

		$url = 'https://fcm.googleapis.com/v1/projects/'.$this->projectId.'/messages:send';

		if (!is_a($subscription = $mobilePushNotification->getSubscription(), MobilePushNotificationSubscriptionInterface::class) || empty($deviceToken = $subscription->getDeviceToken())) {
			$this->logger->error('No device set');
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::TOKEN_INVALID);
		}

		// Validate title and message
		$title = $mobilePushNotification->getTitle();
		$message = $mobilePushNotification->getMessage();

		if (empty($title) || empty($message)) {
			$this->logger->error('Title and message are required');
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::SETTINGS_INVALID);
		}

		$fields = [
			'message' => [
				'token' => $deviceToken,
				'notification' => [
					'title' => $title,
					'body' => $message,
				],
			],
		];

		// Android-specific configuration
		if (null !== $mobilePushNotification->getTimeToLive() || null !== $mobilePushNotification->getCollapseKey()) {
			$fields['message']['android'] = [];

			if (null !== $mobilePushNotification->getTimeToLive()) {
				$fields['message']['android']['ttl'] = $mobilePushNotification->getTimeToLive() . 's';
			}

			if (null !== $mobilePushNotification->getCollapseKey()) {
				$fields['message']['android']['collapse_key'] = $mobilePushNotification->getCollapseKey();
			}
		}

		// iOS-specific configuration (APNS)
		if (null !== $mobilePushNotification->getTimeToLive()) {
			$fields['message']['apns'] = [
				'headers' => [
					'apns-expiration' => (string) (time() + $mobilePushNotification->getTimeToLive()),
				],
			];
		}

		// Custom data
		if (null !== $mobilePushNotification->getData()) {
			$fields['message']['data'] = [];
			foreach ($mobilePushNotification->getData() as $key => $value) {
				$fields['message']['data'][$key] = (string) $value;
			}
		}

		try {
			$client = new \Google_Client();
			$client->setAuthConfig($this->serviceKeyFile);
			$client->addScope('https://www.googleapis.com/auth/firebase.messaging');
			$httpClient = $client->authorize();
		} catch (\Exception $e) {
			$this->logger->error('Failed to authorize Google Client: ' . $e->getMessage());
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::SETTINGS_INVALID);
		}

		try {
			$jsonBody = json_encode($fields, JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			$this->logger->error('Failed to encode JSON: ' . $e->getMessage());
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::SETTINGS_INVALID);
		}

		try {
			$result = $httpClient->request('POST', $url, [
				'http_errors' => false,
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body' => $jsonBody
			]);
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			$this->logger->error('HTTP request failed: ' . $e->getMessage());
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::HTTP);
		}

		$statusCode = $result->getStatusCode();
		$responseBody = (string) $result->getBody();

		try {
			$responseData = \GuzzleHttp\Utils::jsonDecode($responseBody, true);
		} catch (\Exception $e) {
			$this->logger->error('Failed to decode response: ' . $e->getMessage());
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::UNKNOWN);
		}

		$this->logger->info('Firebase response (status ' . $statusCode . '): ' . $responseBody);

		if (null === $responseData) {
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::UNKNOWN);
		}

		// Success response
		if ($statusCode >= 200 && $statusCode < 300 && empty($responseData['error'])) {
			return new PushNotificationSendingResponse(true, null, $responseData);
		}

		// Error handling
		if (!empty($responseData['error'])) {
			$errorStatus = $responseData['error']['status'] ?? null;
			$errorMessage = $responseData['error']['message'] ?? 'Unknown error';

			$this->logger->error('Firebase error: ' . $errorStatus . ' - ' . $errorMessage);

			// Map Firebase error status to PushNotificationSendingStatus
			$sendingStatus = match ($errorStatus) {
				'INVALID_ARGUMENT' => PushNotificationSendingStatus::SETTINGS_INVALID,
				'PERMISSION_DENIED', 'UNAUTHENTICATED' => PushNotificationSendingStatus::TOKEN_INVALID,
				'UNREGISTERED', 'NOT_FOUND' => PushNotificationSendingStatus::TOKEN_EXPIRED,
				'UNAVAILABLE', 'INTERNAL', 'DEADLINE_EXCEEDED' => PushNotificationSendingStatus::HTTP,
				'QUOTA_EXCEEDED' => PushNotificationSendingStatus::QUOTA_EXCEEDED,
				default => PushNotificationSendingStatus::UNKNOWN,
			};

			return new PushNotificationSendingResponse(false, $sendingStatus, $responseData);
		}

		return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::UNKNOWN, $responseData);
	}
}