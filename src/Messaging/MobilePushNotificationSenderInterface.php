<?php

namespace Osimatic\Messaging;

/**
 * Interface for mobile push notification sending implementations.
 * This interface must be implemented by any class that provides mobile push notification sending functionality through services like Firebase Cloud Messaging.
 */
interface MobilePushNotificationSenderInterface
{
	/**
	 * Send a mobile push notification.
	 * @param PushNotificationInterface $mobilePushNotification The push notification to send to a mobile device
	 * @return PushNotificationSendingResponse The response containing the sending result and any error information
	 */
	public function send(PushNotificationInterface $mobilePushNotification): PushNotificationSendingResponse;
}