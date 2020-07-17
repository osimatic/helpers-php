<?php

namespace Osimatic\Helpers\Bank;

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