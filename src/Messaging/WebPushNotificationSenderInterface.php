<?php

namespace Osimatic\Helpers\Messaging;

interface WebPushNotificationSenderInterface
{
	public function send(PushNotificationInterface $webPushNotification): PushNotificationSendingResponse;
}