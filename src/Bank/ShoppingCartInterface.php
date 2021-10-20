<?php 

namespace Osimatic\Helpers\Bank;

/**
 * Represent a shopping cart
 * Used during 3D-Secure authentication for example 
 */
interface ShoppingCartInterface
{
    /**
     * @return int
     */
    public function getTotalQuantity(): int;

	/**
	 * @param int $totalQuantity
	 * @return void
	 */
    public function setTotalQuantity(int $totalQuantity): void;
}