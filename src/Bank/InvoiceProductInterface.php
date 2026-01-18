<?php

namespace Osimatic\Bank;

/**
 * Interface for invoice product/line item information
 * Represents a single product or service line in an invoice
 */
interface InvoiceProductInterface
{
	/**
	 * Get the unit price of the product
	 * @return float The price per unit
	 */
	public function getUnitPrice(): float;

	/**
	 * Get the quantity of the product
	 * @return float The number of units
	 */
	public function getQuantity(): float;
}