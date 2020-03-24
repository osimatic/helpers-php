<?php

namespace Osimatic\Helpers;

class BankCard
{

	/**
	 * @param string $cardNumber
	 * @return string
	 */
	public static function formatCardNumber(string $cardNumber): string
	{
		if (strlen($cardNumber) == 16) {
			$cardNumber = substr($cardNumber, 0, 4).'-'.substr($cardNumber, 4, 4).'-'.substr($cardNumber, 8, 4).'-'.substr($cardNumber, 12, 4);
			$cardNumber = str_replace('*', 'X', $cardNumber);
		}
		return $cardNumber;
	}

}