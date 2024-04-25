<?php

namespace Osimatic\Helpers\Messaging;

interface MobilePushNotificationInterface
{
	/**
	 * The recipient subscription to send to
	 * @return MobilePushNotificationSubscriptionInterface
	 */
	public function getSubscription(): MobilePushNotificationSubscriptionInterface;

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