<?php

namespace Osimatic\Bank;

/**
 * Interface for SEPA direct debit transaction information
 * Represents a transaction for automated bank account debits within the Single Euro Payments Area (SEPA)
 */
interface DirectDebitTransactionInterface
{
	/**
	 * Get the unique transaction number
	 * @return string The transaction identifier
	 */
	public function getTransactionNumber(): string;

	/**
	 * Set the unique transaction number
	 * @param string $value The transaction identifier to set
	 */
	public function setTransactionNumber(string $value): void;

	/**
	 * Get the invoice reference
	 * @return string The internal invoice reference
	 */
	public function getInvoiceReference(): string;

	/**
	 * Set the invoice reference
	 * @param string $value The internal invoice reference to set
	 */
	public function setInvoiceReference(string $value): void;

	/**
	 * Get the invoice number
	 * @return string The human-readable invoice number
	 */
	public function getInvoiceNumber(): string;

	/**
	 * Set the invoice number
	 * @param string $value The human-readable invoice number to set
	 */
	public function setInvoiceNumber(string $value): void;

	/**
	 * Get the transaction amount
	 * @return float The amount to be debited
	 */
	public function getAmount(): float;

	/**
	 * Set the transaction amount
	 * @param float $value The amount to be debited
	 */
	public function setAmount(float $value): void;

	/**
	 * Get the currency code
	 * @return string The three-letter ISO 4217 currency code (e.g., "EUR")
	 */
	public function getCurrency(): string;

	/**
	 * Set the currency code
	 * @param string $value The three-letter ISO 4217 currency code to set (e.g., "EUR")
	 */
	public function setCurrency(string $value): void;

	/**
	 * Get the SEPA mandate RUM (Référence Unique de Mandat / Unique Mandate Reference)
	 * @return string The unique identifier for the SEPA direct debit mandate
	 */
	public function getSepaMandateRum(): string;

	/**
	 * Set the SEPA mandate RUM (Référence Unique de Mandat / Unique Mandate Reference)
	 * @param string $value The unique identifier for the SEPA direct debit mandate to set
	 */
	public function setSepaMandateRum(string $value): void;

	/**
	 * Get the date when the SEPA mandate was signed
	 * @return \DateTime The signature date of the mandate
	 */
	public function getSepaMandateDateOfSignature(): \DateTime;

	/**
	 * Set the date when the SEPA mandate was signed
	 * @param \DateTime $value The signature date of the mandate to set
	 */
	public function setSepaMandateDateOfSignature(\DateTime $value): void;

	/**
	 * Get the debtor's bank account information
	 * @return BankAccountInterface The bank account from which funds will be debited
	 */
	public function getDebtorBankAccount(): BankAccountInterface;

	/**
	 * Set the debtor's bank account information
	 * @param BankAccountInterface $value The bank account from which funds will be debited
	 */
	public function setDebtorBankAccount(BankAccountInterface $value): void;

}