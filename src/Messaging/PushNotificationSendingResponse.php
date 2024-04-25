<?php

namespace Osimatic\Helpers\Messaging;

class PushNotificationSendingResponse
{
	public function __construct(
		private bool $isSuccess,
		private ?PushNotificationSendingStatus $status = null,
		private ?array $responseData = null,
	) {}

	public function isSuccess(): bool
	{
		return $this->isSuccess;
	}

	public function setIsSuccess(bool $isSuccess): void
	{
		$this->isSuccess = $isSuccess;
	}

	public function getStatus(): ?PushNotificationSendingStatus
	{
		return $this->status;
	}

	public function setStatus(?PushNotificationSendingStatus $status): void
	{
		$this->status = $status;
	}

	public function getResponseData(): ?array
	{
		return $this->responseData;
	}

	public function setResponseData(?array $responseData): void
	{
		$this->responseData = $responseData;
	}
}