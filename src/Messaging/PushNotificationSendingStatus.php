<?php

namespace Osimatic\Messaging;

/**
 * Represents the possible error statuses when sending push notifications.
 * This enum defines the various failure conditions that can occur when attempting to send push notifications to mobile or web clients.
 */
enum PushNotificationSendingStatus: string
{
	/**
	 * The push notification service settings are invalid or misconfigured.
	 */
	case SETTINGS_INVALID = 'SETTINGS_INVALID';

	/**
	 * The device token is invalid or malformed.
	 */
	case TOKEN_INVALID = 'TOKEN_INVALID';

	/**
	 * The device token has expired and needs to be refreshed.
	 */
	case TOKEN_EXPIRED = 'TOKEN_EXPIRED';

	/**
	 * An HTTP error occurred during the push notification request.
	 */
	case HTTP = 'HTTP';

	/**
	 * The push notification request timed out.
	 */
	case TIMEOUT = 'TIMEOUT';

	/**
	 * The push notification quota or rate limit has been exceeded.
	 */
	case QUOTA_EXCEEDED = 'QUOTA_EXCEEDED';

	/**
	 * An unknown error occurred during push notification sending.
	 */
	case UNKNOWN = 'UNKNOWN';
}