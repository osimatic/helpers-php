<?php

namespace Osimatic\Helpers\Messaging;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class WebPushNotificationSender implements WebPushNotificationSenderInterface
{
	public function __construct(
		private ?string $vapidPublicKey = null,
		private ?string $vapidPrivateKey = null,
		private ?string $subject = null,
		private LoggerInterface $logger = new NullLogger(),
	) {}

	/**
	 * @param string|null $vapidPublicKey
	 * @return self
	 */
	public function setVapidPublicKey(?string $vapidPublicKey): self
	{
		$this->vapidPublicKey = $vapidPublicKey;

		return $this;
	}

	/**
	 * @param string|null $vapidPrivateKey
	 * @return self
	 */
	public function setVapidPrivateKey(?string $vapidPrivateKey): self
	{
		$this->vapidPrivateKey = $vapidPrivateKey;

		return $this;
	}

	/**
	 * @param string|null $subject
	 * @return self
	 */
	public function setSubject(?string $subject): self
	{
		$this->subject = $subject;

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
	 * @param WebPushNotificationInterface $webPushNotification
	 * @return PushNotificationSendingResponse
	 */
	public function send(WebPushNotificationInterface $webPushNotification): PushNotificationSendingResponse
	{
		if (empty($endPoint = $webPushNotification->getSubscription()->getEndpoint())) {
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
				'keys' => $webPushNotification->getSubscription()->getSubscriptionKeys()
			]);

			$webPush = new \Minishlink\WebPush\WebPush($auth);
			$report = $webPush->sendOneNotification(
				$sub,
				$webPushNotification->getMessage(),
			);

			if (!$report->isSuccess()) {
				$this->logger->info('Error during sending message to endpoint "'.$report->getEndpoint().'" : '.$report->getReason());
				return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::UNKNOWN);
			}

			return new PushNotificationSendingResponse(true);
		}
		catch (\ErrorException $e) {
			$this->logger->info('Error envoi web push notification: '.$e->getMessage());
		}

		return new PushNotificationSendingResponse(false, PushNotificationSendingStatus::UNKNOWN);
	}
}