<?php

namespace Osimatic\Bank;

use Osimatic\Organization\OrganizationInterface;

/**
 * https://schema.org/Invoice
 */
interface InvoiceInterface
{
	/**
	 * @return OrganizationInterface|null
	 */
	public function getSeller(): ?OrganizationInterface;

	/**
	 * @param OrganizationInterface|null $seller
	 */
	public function setSeller(?OrganizationInterface $seller): void;

	/**
	 * @return OrganizationInterface|null
	 */
	public function getBuyer(): ?OrganizationInterface;

	/**
	 * @param OrganizationInterface|null $buyer
	 */
	public function setBuyer(?OrganizationInterface $buyer): void;

	/**
	 * @return string
	 */
	public function getOrderReference(): string;

	/**
	 * @param string $orderReference
	 */
	public function setOrderReference(string $orderReference): void;

	/**
	 * @return string
	 */
	public function getInvoiceNumber(): string;

	/**
	 * @param string $invoiceNumber
	 */
	public function setInvoiceNumber(string $invoiceNumber): void;

	/**
	 * @return \DateTime
	 */
	public function getDate(): \DateTime;

	/**
	 * @param \DateTime $date
	 */
	public function setDate(\DateTime $date): void;

	/**
	 * @return string
	 */
	public function getBillingCity(): string;

	/**
	 * @param string $billingCity
	 */
	public function setBillingCity(string $billingCity): void;

	/**
	 * @return InvoiceProductInterface[]
	 */
	public function getProductsList(): array;

	/**
	 * @param InvoiceProductInterface[] $productsList
	 */
	public function setProductsList(array $productsList): void;

	/**
	 * @return float
	 */
	public function getTotalExclTax(): float;

	/**
	 * @param float $totalExclTax
	 */
	public function setTotalExclTax(float $totalExclTax): void;

	/**
	 * @return float
	 */
	public function getTotalVat(): float;

	/**
	 * @param float $totalVat
	 */
	public function setTotalVat(float $totalVat): void;

	/**
	 * @return float
	 */
	public function getTotalInclTax(): float;

	/**
	 * @param float $totalInclTax
	 */
	public function setTotalInclTax(float $totalInclTax): void;

	/**
	 * @return string
	 */
	public function getCurrency(): string;

	/**
	 * @param string $currency
	 */
	public function setCurrency(string $currency): void;

	/**
	 * @return float
	 */
	public function getBillingTaxRate(): float;

	/**
	 * @param float $billingTaxRate
	 */
	public function setBillingTaxRate(float $billingTaxRate): void;

	/**
	 * @return \DateTime|null
	 */
	public function getValidationDate(): ?\DateTime;

	/**
	 * @param \DateTime|null $validationDate
	 */
	public function setValidationDate(?\DateTime $validationDate): void;

	/**
	 * @return string
	 */
	public function getPaymentStatus(): string;

	/**
	 * @param string $paymentStatus
	 */
	public function setPaymentStatus(string $paymentStatus): void;

	/**
	 * @return \DateTime|null
	 */
	public function getPaymentDate(): ?\DateTime;

	/**
	 * @param \DateTime|null $paymentDate
	 */
	public function setPaymentDate(?\DateTime $paymentDate): void;

	/**
	 * @return PaymentMethod|null
	 */
	public function getPaymentMethod(): ?PaymentMethod;

	/**
	 * @param PaymentMethod|null $paymentMethod
	 */
	public function setPaymentMethod(?PaymentMethod $paymentMethod): void;

	/**
	 * @return string|null
	 */
	public function getBankCardAuthorizationNumber(): ?string;

	/**
	 * @param string|null $bankCardAuthorizationNumber
	 */
	public function setBankCardAuthorizationNumber(?string $bankCardAuthorizationNumber): void;

	/**
	 * @return string|null
	 */
	public function getDeliveryType(): ?string;

	/**
	 * @param string|null $deliveryType
	 */
	public function setDeliveryType(?string $deliveryType): void;

}