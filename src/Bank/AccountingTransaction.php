<?php

namespace Osimatic\Bank;

/**
 * Class representing an accounting transaction
 * Contains transaction details including amounts, customer information, and invoice data
 */
class AccountingTransaction
{
	/**
	 * Transaction date and time
	 * Represents when the accounting transaction occurred
	 * @var \DateTime
	 */
	private \DateTime $dateTime;

	/**
	 * Transaction amount excluding tax (net amount)
	 * The base amount before any VAT or taxes are applied
	 * @var float
	 */
	private float $amountExclTax;

	/**
	 * VAT/tax amount
	 * The total tax amount applied to this transaction
	 * @var float
	 */
	private float $amountVat;

	/**
	 * Transaction amount including tax (gross amount)
	 * The final total amount including all taxes
	 * @var float
	 */
	private float $amountInclTax;

	/**
	 * Currency code for the transaction
	 * Three-letter ISO 4217 currency code (e.g., EUR, USD, GBP)
	 * @var string
	 */
	private string $currency = 'EUR';

	/**
	 * Invoice number associated with this transaction
	 * Unique identifier linking the transaction to an invoice
	 * @var string
	 */
	private string $invoiceNumber;

	/**
	 * Customer identity/name
	 * The name of the customer (individual or company) associated with this transaction
	 * @var string
	 */
	private string $customerIdentity;

	/**
	 * Customer postal/ZIP code
	 * The postal code from the customer's address
	 * @var string|null
	 */
	private ?string $customerPostCode = null;

	/**
	 * Customer country code
	 * Two-letter ISO 3166-1 alpha-2 country code (e.g., FR, US, GB)
	 * @var string
	 */
	private string $customerCountry = 'FR';

	/**
	 * Customer VAT number
	 * EU intra-community VAT identification number for B2B transactions
	 * @var string|null
	 */
	private ?string $customerVatNumber = null;

	/**
	 * Debit account number
	 * The accounting ledger account number to debit for this transaction
	 * @var string|null
	 */
	private ?string $debitAccount = null;

	/**
	 * Set the transaction date from SQL date format (YYYY-MM-DD)
	 * @param string $sqlDate The date in SQL format
	 */
	public function setSqlDate(string $sqlDate): void
	{
		try {
			$this->dateTime = new \DateTime($sqlDate.' 00:00:00');
		}
		catch (\Exception) {}
	}



	/**
	 * Get the transaction date and time
	 * @return \DateTime The transaction date and time
	 */
	public function getDateTime(): \DateTime
	{
		return $this->dateTime ?? new \DateTime();
	}

	/**
	 * Set the transaction date and time
	 * @param \DateTime $dateTime The transaction date and time to set
	 */
	public function setDateTime(\DateTime $dateTime): void
	{
		$this->dateTime = $dateTime;
	}

	/**
	 * Get the amount excluding tax, rounded to 2 decimal places
	 * @return float The amount before tax
	 */
	public function getAmountExclTax(): float
	{
		return round($this->amountExclTax ?? 0., 2);
	}

	/**
	 * Set the amount excluding tax
	 * @param float $amountExclTax The amount before tax to set
	 */
	public function setAmountExclTax(float $amountExclTax): void
	{
		$this->amountExclTax = $amountExclTax;
	}

	/**
	 * Get the VAT/tax amount, rounded to 2 decimal places
	 * @return float The VAT amount
	 */
	public function getAmountVat(): float
	{
		return round($this->amountVat ?? 0., 2);
	}

	/**
	 * Set the VAT/tax amount
	 * @param float $amountVat The VAT amount to set
	 */
	public function setAmountVat(float $amountVat): void
	{
		$this->amountVat = $amountVat;
	}

	/**
	 * Get the amount including tax, rounded to 2 decimal places
	 * @return float The amount after tax
	 */
	public function getAmountInclTax(): float
	{
		return round($this->amountInclTax ?? 0., 2);
	}

	/**
	 * Set the amount including tax
	 * @param float $amountInclTax The amount after tax to set
	 */
	public function setAmountInclTax(float $amountInclTax): void
	{
		$this->amountInclTax = $amountInclTax;
	}

	/**
	 * Get the currency code
	 * @return string The three-letter ISO 4217 currency code (default: "EUR")
	 */
	public function getCurrency(): string
	{
		return $this->currency;
	}

	/**
	 * Set the currency code
	 * @param string $currency The three-letter ISO 4217 currency code to set
	 */
	public function setCurrency(string $currency): void
	{
		$this->currency = $currency;
	}

	/**
	 * Get the invoice number
	 * @return string The invoice number
	 */
	public function getInvoiceNumber(): string
	{
		return $this->invoiceNumber ?? '';
	}

	/**
	 * Set the invoice number
	 * @param string $invoiceNumber The invoice number to set
	 */
	public function setInvoiceNumber(string $invoiceNumber): void
	{
		$this->invoiceNumber = $invoiceNumber;
	}

	/**
	 * Get the customer's identity/name
	 * @return string The customer name
	 */
	public function getCustomerIdentity(): string
	{
		return $this->customerIdentity ?? '';
	}

	/**
	 * Set the customer's identity/name
	 * @param string $customerIdentity The customer name to set
	 */
	public function setCustomerIdentity(string $customerIdentity): void
	{
		$this->customerIdentity = $customerIdentity;
	}

	/**
	 * Get the customer's postal/ZIP code
	 * @return string|null The customer postal code
	 */
	public function getCustomerPostCode(): ?string
	{
		return $this->customerPostCode;
	}

	/**
	 * Set the customer's postal/ZIP code
	 * @param string|null $customerPostCode The customer postal code to set
	 */
	public function setCustomerPostCode(?string $customerPostCode): void
	{
		$this->customerPostCode = $customerPostCode;
	}

	/**
	 * Get the customer's country code
	 * @return string The two-letter ISO 3166-1 alpha-2 country code (default: "FR")
	 */
	public function getCustomerCountry(): string
	{
		return $this->customerCountry;
	}

	/**
	 * Set the customer's country code
	 * @param string $customerCountry The two-letter ISO 3166-1 alpha-2 country code to set
	 */
	public function setCustomerCountry(string $customerCountry): void
	{
		$this->customerCountry = $customerCountry;
	}

	/**
	 * Get the customer's VAT number (EU intra-community VAT number)
	 * @return string|null The VAT number
	 */
	public function getCustomerVatNumber(): ?string
	{
		return $this->customerVatNumber;
	}

	/**
	 * Set the customer's VAT number (EU intra-community VAT number)
	 * @param string|null $customerVatNumber The VAT number to set
	 */
	public function setCustomerVatNumber(?string $customerVatNumber): void
	{
		$this->customerVatNumber = $customerVatNumber;
	}

	/**
	 * Get the debit account number
	 * @return string|null The accounting debit account
	 */
	public function getDebitAccount(): ?string
	{
		return $this->debitAccount;
	}

	/**
	 * Set the debit account number
	 * @param string|null $debitAccount The accounting debit account to set
	 */
	public function setDebitAccount(?string $debitAccount): void
	{
		$this->debitAccount = $debitAccount;
	}


}