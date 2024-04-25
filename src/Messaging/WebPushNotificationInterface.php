<?php

namespace Osimatic\Helpers\Messaging;

interface WebPushNotificationInterface
{
	/**
	 * The recipient subscription to send to
	 * @return WebPushNotificationSubscriptionInterface
	 */
	public function getSubscription(): WebPushNotificationSubscriptionInterface;

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
}