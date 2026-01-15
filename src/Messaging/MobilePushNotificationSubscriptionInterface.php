<?php

namespace Osimatic\Messaging;

/**
 * Interface for mobile push notification subscriptions.
 * This interface must be implemented by any class that represents a mobile device's push notification subscription information.
 */
interface MobilePushNotificationSubscriptionInterface
{
	/**
	 * Get the device token for push notifications.
	 * @return string The unique device token provided by the push notification service (e.g., FCM, APNs)
	 */
	public function getDeviceToken(): string;
}