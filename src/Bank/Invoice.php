<?php

namespace Osimatic\Helpers\Bank;

use Osimatic\Helpers\Organization\Organization;

class Invoice
{
	/**
	 * @var Organization|null
	 */
	protected ?Organization $seller;

	/**
	 * @var Organization|null
	 */
	protected ?Organization $buyer;

	/**
	 * @var string|null
	 */
	protected ?string $orderReference;

	/**
	 * @var string
	 */
	protected string $invoiceNumber;

	/**
	 * @var \DateTime
	 */
	protected \DateTime $date;

	/**
	 * @var string
	 */
	protected string $billingCity;

	/**
	 * @var InvoiceProduct[]
	 */
	protected array $productsList;

	/**
	 * @var float
	 */
	protected float $totalExclTax;

	/**
	 * @var float
	 */
	protected float $totalVat;

	/**
	 * @var float
	 */
	protected float $totalInclTax;

	/**
	 * @var string
	 */
	protected string $currency;

	/**
	 * @var float
	 */
	protected float $billingTaxRate;

	/**
	 * @var \DateTime|null
	 */
	protected ?\DateTime $validationDate;

	/**
	 * @var string
	 */
	protected string $paymentStatus;

	/**
	 * @var \DateTime|null
	 */
	protected ?\DateTime $paymentDate;

	/**
	 * @var string
	 */
	protected string $paymentMethod;

	/**
	 * @var string
	 */
	protected string $bankCardAuthorizationNumber;

	/**
	 * @var string|null
	 */
	protected ?string $deliveryType;





	/**
	 * @return Organization|null
	 */
	public function getSeller(): ?Organization
	{
		return $this->seller;
	}

	/**
	 * @param Organization|null $seller
	 */
	public function setSeller(?Organization $seller): void
	{
		$this->seller = $seller;
	}

	/**
	 * @return Organization|null
	 */
	public function getBuyer(): ?Organization
	{
		return $this->buyer;
	}

	/**
	 * @param Organization|null $buyer
	 */
	public function setBuyer(?Organization $buyer): void
	{
		$this->buyer = $buyer;
	}

	/**
	 * @return string
	 */
	public function getOrderReference(): string
	{
		return $this->orderReference;
	}

	/**
	 * @param string $orderReference
	 */
	public function setOrderReference(string $orderReference): void
	{
		$this->orderReference = $orderReference;
	}

	/**
	 * @return string
	 */
	public function getInvoiceNumber(): string
	{
		return $this->invoiceNumber;
	}

	/**
	 * @param string $invoiceNumber
	 */
	public function setInvoiceNumber(string $invoiceNumber): void
	{
		$this->invoiceNumber = $invoiceNumber;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate(): \DateTime
	{
		return $this->date;
	}

	/**
	 * @param \DateTime $date
	 */
	public function setDate(\DateTime $date): void
	{
		$this->date = $date;
	}

	/**
	 * @return string
	 */
	public function getBillingCity(): string
	{
		return $this->billingCity;
	}

	/**
	 * @param string $billingCity
	 */
	public function setBillingCity(string $billingCity): void
	{
		$this->billingCity = $billingCity;
	}

	/**
	 * @return InvoiceProduct[]
	 */
	public function getProductsList(): array
	{
		return $this->productsList;
	}

	/**
	 * @param InvoiceProduct[] $productsList
	 */
	public function setProductsList(array $productsList): void
	{
		$this->productsList = $productsList;
	}

	/**
	 * @return float
	 */
	public function getTotalExclTax(): float
	{
		return $this->totalExclTax;
	}

	/**
	 * @param float $totalExclTax
	 */
	public function setTotalExclTax(float $totalExclTax): void
	{
		$this->totalExclTax = $totalExclTax;
	}

	/**
	 * @return float
	 */
	public function getTotalVat(): float
	{
		return $this->totalVat;
	}

	/**
	 * @param float $totalVat
	 */
	public function setTotalVat(float $totalVat): void
	{
		$this->totalVat = $totalVat;
	}

	/**
	 * @return float
	 */
	public function getTotalInclTax(): float
	{
		return $this->totalInclTax;
	}

	/**
	 * @param float $totalInclTax
	 */
	public function setTotalInclTax(float $totalInclTax): void
	{
		$this->totalInclTax = $totalInclTax;
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
	 * @return float
	 */
	public function getBillingTaxRate(): float
	{
		return $this->billingTaxRate;
	}

	/**
	 * @param float $billingTaxRate
	 */
	public function setBillingTaxRate(float $billingTaxRate): void
	{
		$this->billingTaxRate = $billingTaxRate;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getValidationDate(): ?\DateTime
	{
		return $this->validationDate;
	}

	/**
	 * @param \DateTime|null $validationDate
	 */
	public function setValidationDate(?\DateTime $validationDate): void
	{
		$this->validationDate = $validationDate;
	}

	/**
	 * @return string
	 */
	public function getPaymentStatus(): string
	{
		return $this->paymentStatus;
	}

	/**
	 * @param string $paymentStatus
	 */
	public function setPaymentStatus(string $paymentStatus): void
	{
		$this->paymentStatus = $paymentStatus;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getPaymentDate(): ?\DateTime
	{
		return $this->paymentDate;
	}

	/**
	 * @param \DateTime|null $paymentDate
	 */
	public function setPaymentDate(?\DateTime $paymentDate): void
	{
		$this->paymentDate = $paymentDate;
	}

	/**
	 * @return string
	 */
	public function getPaymentMethod(): string
	{
		return $this->paymentMethod;
	}

	/**
	 * @param string $paymentMethod
	 */
	public function setPaymentMethod(string $paymentMethod): void
	{
		$this->paymentMethod = $paymentMethod;
	}

	/**
	 * @return string
	 */
	public function getBankCardAuthorizationNumber(): string
	{
		return $this->bankCardAuthorizationNumber;
	}

	/**
	 * @param string $bankCardAuthorizationNumber
	 */
	public function setBankCardAuthorizationNumber(string $bankCardAuthorizationNumber): void
	{
		$this->bankCardAuthorizationNumber = $bankCardAuthorizationNumber;
	}

	/**
	 * @return string|null
	 */
	public function getDeliveryType(): ?string
	{
		return $this->deliveryType;
	}

	/**
	 * @param string|null $deliveryType
	 */
	public function setDeliveryType(?string $deliveryType): void
	{
		$this->deliveryType = $deliveryType;
	}

}