<?php

namespace Osimatic\Bank;

/**
 * Interface for bank account information
 * Represents essential bank account details including IBAN, BIC, and account holder information
 */
interface BankAccountInterface
{
	/**
	 * Get the account holder's identity/name
	 * @return string The name of the account holder
	 */
	public function getIdentity(): string;

	/**
	 * Set the account holder's identity/name
	 * @param string $value The name of the account holder to set
	 */
	public function setIdentity(string $value): void;

	/**
	 * Get the IBAN (International Bank Account Number)
	 * @return string The IBAN identifying the bank account
	 */
	public function getIban(): string;

	/**
	 * Set the IBAN (International Bank Account Number)
	 * @param string $value The IBAN identifying the bank account to set
	 */
	public function setIban(string $value): void;

	/**
	 * Get the BIC (Bank Identifier Code), also known as SWIFT code
	 * @return string The BIC/SWIFT code of the bank
	 */
	public function getBic(): string;

	/**
	 * Set the BIC (Bank Identifier Code), also known as SWIFT code
	 * @param string $value The BIC/SWIFT code of the bank to set
	 */
	public function setBic(string $value): void;

	/**
	 * Get the banking domiciliation (bank branch/address information)
	 * @return string The domiciliation information of the bank
	 */
	public function getBankingDomiciliation(): string;

	/**
	 * Set the banking domiciliation (bank branch/address information)
	 * @param string $value The domiciliation information of the bank to set
	 */
	public function setBankingDomiciliation(string $value): void;

}