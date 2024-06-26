<?php

namespace Osimatic\Bank;

interface BankAccountInterface
{
	/**
	 * @return string
	 */
	public function getIdentity(): string;

	/**
	 * @param string $value
	 */
	public function setIdentity(string $value): void;

	/**
	 * @return string
	 */
	public function getIban(): string;

	/**
	 * @param string $value
	 */
	public function setIban(string $value): void;

	/**
	 * @return string
	 */
	public function getBic(): string;

	/**
	 * @param string $value
	 */
	public function setBic(string $value): void;

	/**
	 * @return string
	 */
	public function getBankingDomiciliation(): string;

	/**
	 * @param string $value
	 */
	public function setBankingDomiciliation(string $value): void;

}