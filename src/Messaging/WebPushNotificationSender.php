<?php

namespace Osimatic\Messaging;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Web Push notification sender using VAPID authentication.
 * This class implements the WebPushNotificationSenderInterface and provides methods to send push notifications to web browsers using the Web Push Protocol with VAPID keys.
 */
class WebPushNotificationSender implements WebPushNotificationSenderInterface
{
	/**
	 * Construct a new WebPushNotificationSender instance.
	 * @param string|null $vapidPublicKey The VAPID public key for authentication
	 * @param string|null $vapidPrivateKey The VAPID private key for signing requests
	 * @param string|null $subject The subject (typically a mailto: or https: URL) for VAPID authentication
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 */
	public function __construct(
		private ?string $vapidPublicKey = null,
		private ?string $vapidPrivateKey = null,
		private ?string $subject = null,
		private LoggerInterface $logger = new NullLogger(),
	) {}

	/**
	 * Set the VAPID public key.
	 * @param string|null $vapidPublicKey The VAPID public key (base64 URL-safe encoded)
	 * @return self Returns this instance for method chaining
	 */
	public function setVapidPublicKey(?string $vapidPublicKey): self
	{
		$this->vapidPublicKey = $vapidPublicKey;

		return $this;
	}

	/**
	 * Set the VAPID private key.
	 * @param string|null $vapidPrivateKey The VAPID private key (base64 URL-safe encoded)
	 * @return self Returns this instance for method chaining
	 */
	public function setVapidPrivateKey(?string $vapidPrivateKey): self
	{
		$this->vapidPrivateKey = $vapidPrivateKey;

		return $this;
	}

	/**
	 * Set the VAPID subject.
	 * @param string|null $subject The subject URL (e.g., "mailto:sender@example.com" or "https://example.com")
	 * @return self Returns this instance for method chaining
	 */
	public function setSubject(?string $subject): self
	{
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Sets the logger for error and debugging information.
	 * @param LoggerInterface $logger The PSR-3 logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * @param PushNotificationInterface $webPushNotification
	 * @return PushNotificationSendingResponse
	 */
	public function send(PushNotificationInterface $webPushNotification): PushNotificationSendingResponse
	{
		if (!is_a($subscription = $webPushNotification->getSubscription(), WebPushNotificationSubscriptionInterface::class) || empty($endPoint = $subscription->getEndpoint())) {
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::TOKEN_INVALID);
		}

		if (empty($this->vapidPublicKey) || empty($this->vapidPrivateKey)) {
			$this->logger->error('vapid keys not set');
			return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::SETTINGS_INVALID);
		}

		$auth = [
			'VAPID' => [
				'subject' => $this->subject, // can be a mailto: or your website address
				'publicKey' => $this->vapidPublicKey, // (recommended) uncompressed public key P-256 encoded in Base64-URL
				'privateKey' => $this->vapidPrivateKey, // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
				//'pemFile' => 'path/to/pem', // if you have a PEM file and can link to it on your filesystem
				//'pem' => 'pemFileContent', // if you have a PEM file and want to hardcode its content
			],
		];

		try {
			$sub = \Minishlink\WebPush\Subscription::create([
				'endpoint' => $endPoint,
				'keys' => $subscription->getSubscriptionKeys()
			]);

			$webPush = new \Minishlink\WebPush\WebPush($auth);
			$report = $webPush->sendOneNotification(
				$sub,
				$webPushNotification->getMessage(),
			);

			$responseData = $report->jsonSerialize();

			if (!$report->isSuccess()) {
				$this->logger->info('Error during sending message to endpoint "'.$report->getEndpoint().'" : '.$report->getReason());

				if ($report->isSubscriptionExpired()) {
					return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::TOKEN_EXPIRED, $responseData);
				}

				return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::UNKNOWN, $responseData);
			}

			return new PushNotificationSendingResponse(true, null, $responseData);
		}
		catch (\ErrorException $e) {
			$this->logger->error('Error sending web push notification: '.$e->getMessage());
		}

		return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::UNKNOWN);
	}
}