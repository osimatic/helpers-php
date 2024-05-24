<?php

namespace Osimatic\Messaging;

interface MobilePushNotificationSenderInterface
{
	public function send(PushNotificationInterface $mobilePushNotification): PushNotificationSendingResponse;
}