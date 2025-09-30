<?php

namespace Osimatic\Bank;

class AccountingTransaction
{
	private \DateTime $dateTime;
	private float $amountExclTax;
	private float $amountVat;
	private float $amountInclTax;
	private string $currency = 'EUR';
	private string $invoiceNumber;
	private string $customerIdentity;
	private ?string $customerPostCode = null;
	private string $customerCountry = 'FR';
	private ?string $customerVatNumber = null;
	private ?string $debitAccount = null;

	/**
	 * @param string $sqlDate
	 */
	public function setSqlDate(string $sqlDate): void
	{
		try {
			$this->dateTime = new \DateTime($sqlDate.' 00:00:00');
		}
		catch (\Exception) {}
	}



	/**
	 * @return \DateTime
	 */
	public function getDateTime(): \DateTime
	{
		return $this->dateTime ?? new \DateTime();
	}

	/**
	 * @param \DateTime $dateTime
	 */
	public function setDateTime(\DateTime $dateTime): void
	{
		$this->dateTime = $dateTime;
	}

	/**
	 * @return float
	 */
	public function getAmountExclTax(): float
	{
		return round($this->amountExclTax ?? 0., 2);
	}

	/**
	 * @param float $amountExclTax
	 */
	public function setAmountExclTax(float $amountExclTax): void
	{
		$this->amountExclTax = $amountExclTax;
	}

	/**
	 * @return float
	 */
	public function getAmountVat(): float
	{
		return round($this->amountVat ?? 0., 2);
	}

	/**
	 * @param float $amountVat
	 */
	public function setAmountVat(float $amountVat): void
	{
		$this->amountVat = $amountVat;
	}

	/**
	 * @return float
	 */
	public function getAmountInclTax(): float
	{
		return round($this->amountInclTax ?? 0., 2);
	}

	/**
	 * @param float $amountInclTax
	 */
	public function setAmountInclTax(float $amountInclTax): void
	{
		$this->amountInclTax = $amountInclTax;
	}

	/**
	 * @return string
	 */
	public function getCurrency(): string
	{
		return $this->currency;
	}

	/**
	 * @param string $currency
	 */
	public function setCurrency(string $currency): void
	{
		$this->currency = $currency;
	}

	/**
	 * @return string
	 */
	public function getInvoiceNumber(): string
	{
		return $this->invoiceNumber ?? '';
	}

	/**
	 * @param string $invoiceNumber
	 */
	public function setInvoiceNumber(string $invoiceNumber): void
	{
		$this->invoiceNumber = $invoiceNumber;
	}

	/**
	 * @return string
	 */
	public function getCustomerIdentity(): string
	{
		return $this->customerIdentity ?? '';
	}

	/**
	 * @param string $customerIdentity
	 */
	public function setCustomerIdentity(string $customerIdentity): void
	{
		$this->customerIdentity = $customerIdentity;
	}

	/**
	 * @return string|null
	 */
	public function getCustomerPostCode(): ?string
	{
		return $this->customerPostCode;
	}

	/**
	 * @param string|null $customerPostCode
	 */
	public function setCustomerPostCode(?string $customerPostCode): void
	{
		$this->customerPostCode = $customerPostCode;
	}

	/**
	 * @return string
	 */
	public function getCustomerCountry(): string
	{
		return $this->customerCountry;
	}

	/**
	 * @param string $customerCountry
	 */
	public function setCustomerCountry(string $customerCountry): void
	{
		$this->customerCountry = $customerCountry;
	}

	/**
	 * @return string|null
	 */
	public function getCustomerVatNumber(): ?string
	{
		return $this->customerVatNumber;
	}

	/**
	 * @param string|null $customerVatNumber
	 */
	public function setCustomerVatNumber(?string $customerVatNumber): void
	{
		$this->customerVatNumber = $customerVatNumber;
	}

	/**
	 * @return string|null
	 */
	public function getDebitAccount(): ?string
	{
		return $this->debitAccount;
	}

	/**
	 * @param string|null $debitAccount
	 */
	public function setDebitAccount(?string $debitAccount): void
	{
		$this->debitAccount = $debitAccount;
	}


}