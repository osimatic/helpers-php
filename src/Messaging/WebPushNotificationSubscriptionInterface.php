<?php

namespace Osimatic\Helpers\Messaging;

/**
 * Interface WebPushNotificationSubscriptionInterface
 * Represent subscription of a web push notification
 * @package Osimatic\Helpers\Messaging
 */
interface WebPushNotificationSubscriptionInterface
{
	/**
	 * @return string
	 */
	public function getEndpoint() : string;

	/**
	 * @return string
	 */
	public function getPublicKey() : string;

	/**
	 * @return string
	 */
	public function getAuthToken() : string;

	/**
	 * @return int
	 */
	public function getExpirationTimestamp() : int;

	/**
	 * @return string|null
	 */
	public function getContentEncoding() : ?string;

}