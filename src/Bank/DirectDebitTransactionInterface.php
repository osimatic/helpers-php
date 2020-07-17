<?php

namespace Osimatic\Helpers\Bank;

interface DirectDebitTransactionInterface
{
	/**
	 * @return string
	 */
	public function getTransactionNumber(): string;

	/**
	 * @param string $value
	 */
	public function setTransactionNumber(string $value): void;

	/**
	 * @return string
	 */
	public function getInvoiceReference(): string;

	/**
	 * @param string $value
	 */
	public function setInvoiceReference(string $value): void;

	/**
	 * @return string
	 */
	public function getInvoiceNumber(): string;

	/**
	 * @param string $value
	 */
	public function setInvoiceNumber(string $value): void;

	/**
	 * @return float
	 */
	public function getAmount(): float;

	/**
	 * @param float $value
	 */
	public function setAmount(float $value): void;

	/**
	 * @return string
	 */
	public function getCurrency(): string;

	/**
	 * @param string $value
	 */
	public function setCurrency(string $value): void;

	/**
	 * @return string
	 */
	public function getSepaMandateRum(): string;

	/**
	 * @param string $value
	 */
	public function setSepaMandateRum(string $value): void;

	/**
	 * @return \DateTime
	 */
	public function getSepaMandateDateOfSignature(): \DateTime;

	/**
	 * @param \DateTime $value
	 */
	public function setSepaMandateDateOfSignature(\DateTime $value): void;

	/**
	 * @return BankAccountInterface
	 */
	public function getDebtorBankAccount(): BankAccountInterface;

	/**
	 * @param BankAccountInterface $value
	 */
	public function setDebtorBankAccount(BankAccountInterface $value): void;

}