<?php

namespace Osimatic\Helpers\Bank;

class BankCard
{

	/**
	 * @param string $cardNumber
	 * @return bool
	 * @link https://en.wikipedia.org/wiki/Payment_card_number
	 */
	public static function checkCardNumber(string $cardNumber): bool
	{
		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		//$constraint = new \Symfony\Component\Validator\Constraints\CardScheme();
		//$constraint->schemes = ['VISA', 'MASTERCARD', 'AMEX'];
		$constraint = new \Symfony\Component\Validator\Constraints\CardScheme(['schemes' => ['VISA', 'MASTERCARD', 'AMEX']]);
		return $validator->validate($cardNumber, $constraint)->count() === 0;
	}

	/**
	 * @param string $csc
	 * @return bool
	 */
	public static function checkCardCSC(string $csc): bool
	{
		return strlen($csc) >= 3 && strlen($csc) <= 4;
	}

	/**
	 * @param string $cardNumber
	 * @return string
	 */
	public static function formatCardNumber(string $cardNumber): string
	{
		if (strlen($cardNumber) === 16) {
			$cardNumber = substr($cardNumber, 0, 4).'-'.substr($cardNumber, 4, 4).'-'.substr($cardNumber, 8, 4).'-'.substr($cardNumber, 12, 4);
			$cardNumber = str_replace('*', 'X', $cardNumber);
		}
		return $cardNumber;
	}

}