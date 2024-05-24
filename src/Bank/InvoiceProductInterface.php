<?php

namespace Osimatic\Bank;

interface InvoiceProductInterface
{
	/**
	 * @return float
	 */
	public function getUnitPrice(): float;

	/**
	 * @return float
	 */
	public function getQuantity(): float;
}