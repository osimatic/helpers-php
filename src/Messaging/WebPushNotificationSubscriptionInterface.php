<?php

namespace Osimatic\Messaging;

/**
 * Interface for web push notification subscriptions.
 * This interface represents a subscription for web push notifications, containing the endpoint and encryption keys required to send notifications to a web browser.
 */
interface WebPushNotificationSubscriptionInterface
{
	/**
	 * Get the subscription endpoint URL.
	 * @return string The push service endpoint URL where notifications should be sent
	 */
	public function getEndpoint() : string;

	/**
	 * Get the subscription encryption keys.
	 * @return array Array containing the public key and authentication token
	 */
	public function getSubscriptionKeys() : array;

	/**
	 * Get the public key for message encryption.
	 * @return string The public key used to encrypt notification payloads
	 */
	public function getPublicKey() : string;

	/**
	 * Get the authentication token.
	 * @return string The authentication token used to verify the subscription
	 */
	public function getAuthToken() : string;

	/**
	 * Get the subscription expiration timestamp.
	 * @return int|null The Unix timestamp when the subscription expires, or null if it doesn't expire
	 */
	public function getExpirationTimestamp() : ?int;

	/**
	 * Get the content encoding method.
	 * @return string|null The content encoding algorithm (e.g., 'aes128gcm', 'aesgcm'), or null if not specified
	 */
	public function getContentEncoding() : ?string;

}