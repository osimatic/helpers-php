<?php

namespace Osimatic\Messaging;

/**
 * Represents the response from a push notification sending operation.
 * This class encapsulates the result status and any additional data returned from the push notification service.
 */
class PushNotificationSendingResponse
{
	/**
	 * Construct a new PushNotificationSendingResponse instance.
	 * @param bool $isSuccess Whether the push notification was sent successfully
	 * @param PushNotificationSendingStatus|null $status The error status if sending failed, or null if successful
	 * @param array|null $responseData Additional response data from the push notification service
	 */
	public function __construct(
		private bool $isSuccess,
		private ?PushNotificationSendingStatus $status = null,
		private ?array $responseData = null,
	) {}

	/**
	 * Check if the push notification was sent successfully.
	 * @return bool True if successful, false otherwise
	 */
	public function isSuccess(): bool
	{
		return $this->isSuccess;
	}

	/**
	 * Set whether the push notification was sent successfully.
	 * @param bool $isSuccess True if successful, false otherwise
	 */
	public function setIsSuccess(bool $isSuccess): void
	{
		$this->isSuccess = $isSuccess;
	}

	/**
	 * Get the error status if sending failed.
	 * @return PushNotificationSendingStatus|null The error status, or null if successful
	 */
	public function getStatus(): ?PushNotificationSendingStatus
	{
		return $this->status;
	}

	/**
	 * Set the error status.
	 * @param PushNotificationSendingStatus|null $status The error status to set
	 */
	public function setStatus(?PushNotificationSendingStatus $status): void
	{
		$this->status = $status;
	}

	/**
	 * Get additional response data from the push notification service.
	 * @return array|null The response data array, or null if not set
	 */
	public function getResponseData(): ?array
	{
		return $this->responseData;
	}

	/**
	 * Set additional response data.
	 * @param array|null $responseData The response data array to set
	 */
	public function setResponseData(?array $responseData): void
	{
		$this->responseData = $responseData;
	}
}