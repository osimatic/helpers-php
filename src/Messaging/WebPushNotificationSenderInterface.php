<?php

namespace Osimatic\Helpers\Messaging;

interface WebPushNotificationSenderInterface
{
	public function send(WebPushNotificationInterface $webPushNotification): PushNotificationSendingResponse;
}