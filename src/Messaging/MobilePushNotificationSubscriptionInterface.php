<?php

namespace Osimatic\Helpers\Messaging;

interface MobilePushNotificationSubscriptionInterface
{
	/**
	 * The device token
	 * @return string
	 */
	public function getDeviceToken(): string;
}