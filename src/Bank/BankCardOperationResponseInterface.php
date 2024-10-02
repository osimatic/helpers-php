<?php

namespace Osimatic\Bank;

interface BankCardOperationResponseInterface
{
	/**
	 * @return bool
	 */
	public function isSuccess(): bool;

	/**
	 * @return string|null
	 */
	public function getOrderReference(): ?string;

	/**
	 * @return string|null
	 */
	public function getAuthorizationNumber(): ?string;

	/**
	 * @return string|null
	 */
	public function getTransactionNumber(): ?string;

	/**
	 * @return string|null
	 */
	public function getCardLastDigits(): ?string;

	/**
	 * @return \DateTime|null
	 */
	public function getCardExpirationDateTime(): ?\DateTime;
}