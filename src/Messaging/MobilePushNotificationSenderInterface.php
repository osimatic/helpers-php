<?php

namespace Osimatic\Helpers\Messaging;

interface MobilePushNotificationSenderInterface
{
	public function send(PushNotificationInterface $mobilePushNotification): PushNotificationSendingResponse;
}