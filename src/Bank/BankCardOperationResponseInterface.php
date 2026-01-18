<?php

namespace Osimatic\Bank;

/**
 * Interface for bank card operation response information
 * Represents the response from a bank card payment processor after an operation (authorization, capture, refund, etc.)
 */
interface BankCardOperationResponseInterface
{
	/**
	 * Check if the operation was successful
	 * @return bool True if the operation succeeded, false otherwise
	 */
	public function isSuccess(): bool;

	/**
	 * Get the order reference
	 * @return string|null The merchant's order reference
	 */
	public function getOrderReference(): ?string;

	/**
	 * Get the call number
	 * @return string|null The unique identifier for this API call
	 */
	public function getCallNumber(): ?string;

	/**
	 * Get the authorization number
	 * @return string|null The authorization code from the bank
	 */
	public function getAuthorisationNumber(): ?string;

	/**
	 * Get the transaction number
	 * @return string|null The unique transaction identifier
	 */
	public function getTransactionNumber(): ?string;

	/**
	 * Get the card reference token
	 * This token is provided during authorization and can be used for future debits (e.g., recurring payments)
	 * @return string|null The card reference token
	 */
	public function getCardReference(): ?string;

	/**
	 * Get the last digits of the card number
	 * @return string|null The last 4 digits of the card (e.g., "1234")
	 */
	public function getCardLastDigits(): ?string;

	/**
	 * Get the card expiration date
	 * @return \DateTime|null The expiration date of the card
	 */
	public function getCardExpirationDateTime(): ?\DateTime;
}