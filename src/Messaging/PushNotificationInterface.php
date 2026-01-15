<?php

namespace Osimatic\Messaging;

/**
 * Interface for push notification messages.
 * This interface must be implemented by any class that represents a push notification to be sent to mobile or web clients.
 */
interface PushNotificationInterface
{
	/**
	 * Get the recipient subscription to send the notification to.
	 * @return MobilePushNotificationSubscriptionInterface|WebPushNotificationSubscriptionInterface The subscription containing device token or endpoint information
	 */
	public function getSubscription(): MobilePushNotificationSubscriptionInterface|WebPushNotificationSubscriptionInterface;

	/**
	 * Get the title of the notification message.
	 * @return string The notification title
	 */
	public function getTitle(): string;

	/**
	 * Get the body message of the notification.
	 * @return string The notification message content
	 */
	public function getMessage(): string;

	/**
	 * Get additional data to accompany the notification message.
	 * @return array|null Array of custom key-value pairs, or null if no additional data
	 */
	public function getData(): ?array;

	/**
	 * Get the collapse key for message grouping.
	 * @return string|null The collapse key used to group notifications, or null if not set
	 */
	public function getCollapseKey(): ?string;

	/**
	 * Get the time-to-live duration for the notification.
	 * @return int|null The duration in seconds for which the message should be kept if the device is offline, or null if not set
	 */
	public function getTimeToLive(): ?int;
}