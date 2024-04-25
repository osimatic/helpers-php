<?php

namespace Osimatic\Helpers\Messaging;

interface MobilePushNotificationSenderInterface
{
	public function send(MobilePushNotificationInterface $mobilePushNotification): PushNotificationSendingResponse;
}