<?php

namespace Osimatic\Messaging;

interface MobilePushNotificationSubscriptionInterface
{
	/**
	 * The device token
	 * @return string
	 */
	public function getDeviceToken(): string;
}