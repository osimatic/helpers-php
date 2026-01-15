<?php

namespace Osimatic\Messaging;

/**
 * Interface for web push notification sending implementations.
 * This interface must be implemented by any class that provides web push notification sending functionality through the Web Push Protocol.
 */
interface WebPushNotificationSenderInterface
{
	/**
	 * Send a web push notification.
	 * @param PushNotificationInterface $webPushNotification The push notification to send to a web browser
	 * @return PushNotificationSendingResponse The response containing the sending result and any error information
	 */
	public function send(PushNotificationInterface $webPushNotification): PushNotificationSendingResponse;
}