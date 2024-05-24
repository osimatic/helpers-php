<?php

namespace Osimatic\Messaging;

interface WebPushNotificationSenderInterface
{
	public function send(PushNotificationInterface $webPushNotification): PushNotificationSendingResponse;
}