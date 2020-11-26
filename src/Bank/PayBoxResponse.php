<?php

namespace Osimatic\Helpers\Bank;

class PayBoxResponse
{
	private ?string $responseCode;
	private ?string $callNumber;
	private ?string $transactionNumber;
	private ?string $authorizationNumber;
	private ?string $cardHash;
	private ?string $cardLastDigits;
	private ?string $cardExpiryDate;


	/**
	 * @return string|null
	 */
	public function getResponseCode(): ?string
	{
		return $this->responseCode ?? null;
	}

	/**
	 * @param string|null $responseCode
	 */
	public function setResponseCode(?string $responseCode): void
	{
		$this->responseCode = $responseCode;
	}

	/**
	 * @return string|null
	 */
	public function getCallNumber(): ?string
	{
		return $this->callNumber ?? null;
	}

	/**
	 * @param string|null $callNumber
	 */
	public function setCallNumber(?string $callNumber): void
	{
		$this->callNumber = $callNumber;
	}

	/**
	 * @return string|null
	 */
	public function getTransactionNumber(): ?string
	{
		return $this->transactionNumber ?? null;
	}

	/**
	 * @param string|null $transactionNumber
	 */
	public function setTransactionNumber(?string $transactionNumber): void
	{
		$this->transactionNumber = $transactionNumber;
	}

	/**
	 * @return string|null
	 */
	public function getAuthorizationNumber(): ?string
	{
		return $this->authorizationNumber ?? null;
	}

	/**
	 * @param string|null $authorizationNumber
	 */
	public function setAuthorizationNumber(?string $authorizationNumber): void
	{
		$this->authorizationNumber = $authorizationNumber;
	}

	/**
	 * @return string|null
	 */
	public function getCardHash(): ?string
	{
		return $this->cardHash ?? null;
	}

	/**
	 * @param string|null $cardHash
	 */
	public function setCardHash(?string $cardHash): void
	{
		$this->cardHash = $cardHash;
	}

	/**
	 * @return string|null
	 */
	public function getCardLastDigits(): ?string
	{
		return $this->cardLastDigits ?? null;
	}

	/**
	 * @param string|null $cardLastDigits
	 */
	public function setCardLastDigits(?string $cardLastDigits): void
	{
		$this->cardLastDigits = $cardLastDigits;
	}

	/**
	 * @return string|null
	 */
	public function getCardExpiryDate(): ?string
	{
		return $this->cardExpiryDate ?? null;
	}

	/**
	 * @param string|null $cardExpiryDate
	 */
	public function setCardExpiryDate(?string $cardExpiryDate): void
	{
		$this->cardExpiryDate = $cardExpiryDate;
	}

}