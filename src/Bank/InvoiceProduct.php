<?php

namespace Osimatic\Bank;

/**
 * @deprecated use InvoiceProductInterface instead
 */
class InvoiceProduct implements InvoiceProductInterface
{
	protected float $unitPrice;
	protected float $quantity;

	/**
	 * @return float
	 */
	public function getUnitPrice(): float
	{
		return $this->unitPrice;
	}

	/**
	 * @param float $unitPrice
	 */
	public function setUnitPrice(float $unitPrice): void
	{
		$this->unitPrice = $unitPrice;
	}

	/**
	 * @return float
	 */
	public function getQuantity(): float
	{
		return $this->quantity;
	}

	/**
	 * @param float $quantity
	 */
	public function setQuantity(float $quantity): void
	{
		$this->quantity = $quantity;
	}
}