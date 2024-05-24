<?php

namespace Osimatic\Messaging;

interface PushNotificationInterface
{
	/**
	 * The recipient subscription to send to
	 * @return MobilePushNotificationSubscriptionInterface|WebPushNotificationSubscriptionInterface
	 */
	public function getSubscription(): MobilePushNotificationSubscriptionInterface|WebPushNotificationSubscriptionInterface;

	/**
	 * The title of message
	 * @return string
	 */
	public function getTitle(): string;

	/**
	 * The message to send
	 * @return string
	 */
	public function getMessage(): string;

	/**
	 * Array of data to accompany the message
	 * @return array|null
	 */
	public function getData(): ?array;

	/**
	 * @return string|null
	 */
	public function getCollapseKey(): ?string;

	/**
	 * @return int|null
	 */
	public function getTimeToLive(): ?int;
}