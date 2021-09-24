<?php 

namespace Osimatc\Helpers\Bank;

/**
 * Represent a shopping cart
 * Used during 3D-Secure authentication for example 
 */
interface ShoppingCartInterface
{
    /**
     * @return string
     */
    public function getTotalQuantity(): string;
}