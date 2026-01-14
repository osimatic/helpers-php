<?php

namespace Osimatic\Messaging;

enum PushNotificationSendingStatus: string
{
	case SETTINGS_INVALID = 'SETTINGS_INVALID';
	case TOKEN_INVALID = 'TOKEN_INVALID';
	case TOKEN_EXPIRED = 'TOKEN_EXPIRED';
	case HTTP = 'HTTP';
	case TIMEOUT = 'TIMEOUT';
	case QUOTA_EXCEEDED = 'QUOTA_EXCEEDED';
	case UNKNOWN = 'UNKNOWN';
}