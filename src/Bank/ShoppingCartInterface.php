<?php 

namespace Osimatic\Helpers\Bank;

/**
 * Represent a shopping cart
 * Used during 3D-Secure authentication for example 
 */
interface ShoppingCartInterface
{
    /**
     * @return string
     */
    public function getProductName(): string;

	/**
	 * @param string $productName
	 * @return void
	 */
    public function setProductName(string $productName): void;

    /**
     * @return int
     */
    public function getTotalQuantity(): int;

	/**
	 * @param int $totalQuantity
	 * @return void
	 */
    public function setTotalQuantity(int $totalQuantity): void;

    /**
     * @return int unit price, in cents
     */
    public function getUnitPrice(): int;

	/**
	 * @param int $unitPrice unit price, in cents
	 * @return void
	 */
    public function setUnitPrice(int $unitPrice): void;

}