<?php 

namespace Osimatic\Bank;

/**
 * Represent a shopping cart
 * Used during 3D-Secure authentication for example 
 */
interface ShoppingCartInterface
{
	/**
	 * @return InvoiceProductInterface[]
	 */
	//public function getProductList(): array;

	/**
	 * @param InvoiceProductInterface[] $productList
	 * @return void
	 */
	//public function setProductName(array $productList): void;

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
	 * @return int total price, in cents
	 */
	public function getTotalPrice(): int;

	/**
	 * @param int $totalPrice total price, in cents
	 * @return void
	 */
	public function setTotalPrice(int $totalPrice): void;

}