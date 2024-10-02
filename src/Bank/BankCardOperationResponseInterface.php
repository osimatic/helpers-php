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
	public function getAuthorisationNumber(): ?string;

	/**
	 * @return string|null
	 */
	public function getTransactionNumber(): ?string;

	/**
	 * given if autorisation and used for debit later
	 * @return string|null
	 */
	public function getCardReference(): ?string;

	/**
	 * @return string|null
	 */
	public function getCardLastDigits(): ?string;

	/**
	 * @return \DateTime|null
	 */
	public function getCardExpirationDateTime(): ?\DateTime;
}