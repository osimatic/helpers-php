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
	 *
	 * This error occurs when:
	 * - FCM/APNS API credentials are missing or incorrect
	 * - VAPID keys are invalid or expired (for Web Push)
	 * - Server keys or authentication tokens are not properly configured
	 *
	 * Action: Verify and update the push notification service configuration.
	 * Error type: Permanent (requires configuration fix)
	 */
	case SETTINGS_INVALID = 'SETTINGS_INVALID';

	/**
	 * The device token is invalid or malformed.
	 *
	 * This error occurs when:
	 * - The provided token format is incorrect
	 * - The token doesn't match the expected platform format (FCM, APNS, Web Push)
	 * - The token has been unregistered by the client application
	 *
	 * Action: Remove this token from your database and request a new token from the device.
	 * Error type: Permanent (token should be deleted)
	 */
	case TOKEN_INVALID = 'TOKEN_INVALID';

	/**
	 * The device token has expired and needs to be refreshed.
	 *
	 * This error occurs when:
	 * - The user has uninstalled and reinstalled the application
	 * - APNS tokens have been rotated by Apple
	 * - The token is no longer valid on the push notification service
	 *
	 * Action: Remove this token from your database and request a new token from the device.
	 * Error type: Permanent (token should be deleted)
	 */
	case TOKEN_EXPIRED = 'TOKEN_EXPIRED';

	/**
	 * An HTTP error occurred during the push notification request.
	 *
	 * This error occurs when:
	 * - The push notification service returns a 4xx or 5xx HTTP status code
	 * - Network connectivity issues prevent the request from completing
	 * - The push notification service is temporarily unavailable
	 *
	 * Action: Check the error details, verify network connectivity, and retry after a delay.
	 * Error type: Potentially temporary (may succeed on retry)
	 */
	case HTTP = 'HTTP';

	/**
	 * The push notification request timed out.
	 *
	 * This error occurs when:
	 * - The push notification service takes too long to respond
	 * - Network latency is too high
	 * - The push notification service is overloaded
	 *
	 * Action: Retry the request after a short delay with exponential backoff.
	 * Error type: Temporary (should retry)
	 */
	case TIMEOUT = 'TIMEOUT';

	/**
	 * The push notification quota or rate limit has been exceeded.
	 *
	 * This error occurs when:
	 * - FCM message rate limit exceeded (default: 1,000 messages per minute per app)
	 * - APNS throughput limit exceeded
	 * - Daily quota limit reached on the push notification service
	 * - Too many requests sent to the same device in a short period
	 *
	 * Action: Implement rate limiting and exponential backoff. Check your service quota limits.
	 * Error type: Temporary (retry after waiting, or upgrade quota)
	 */
	case QUOTA_EXCEEDED = 'QUOTA_EXCEEDED';

	/**
	 * An unknown error occurred during push notification sending.
	 *
	 * This error occurs when:
	 * - An unexpected exception was thrown
	 * - The error doesn't match any known error category
	 * - The push notification service returns an unrecognized error code
	 *
	 * Action: Check logs for detailed error information and contact support if the issue persists.
	 * Error type: Unknown (check logs and retry cautiously)
	 */
	case UNKNOWN = 'UNKNOWN';
}