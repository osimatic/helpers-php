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

	/**
	 * @param string $cardNumber
	 * @return BankCardType|null
	 */
	public static function getType(string $cardNumber): ?BankCardType
	{
		if (((int)substr($cardNumber, 0, 1)) === 4) {
			return BankCardType::VISA;
		}
		if (((int)substr($cardNumber, 0, 1)) === 5) {
			return BankCardType::MASTER_CARD;
		}
		if (((int)substr($cardNumber, 0, 1)) === 6) {
			return BankCardType::DISCOVER_NETWORK;
		}
		if (((int)substr($cardNumber, 0, 2)) === 37) {
			return BankCardType::AMERICAN_EXPRESS;
		}
		if (((int)substr($cardNumber, 0, 2)) === 38) {
			return BankCardType::DINNER_CLUB;
		}
		return null;
	}


	// ---------- deprecated ----------

	/** @deprecated */
	public const CARD_TYPE_AMERICAN_EXPRESS = 'AMERICAN_EXPRESS';
	/** @deprecated */
	public const CARD_TYPE_VISA = 'VISA';
	/** @deprecated */
	public const CARD_TYPE_DINNER_CLUB = 'DINNER_CLUB';
	/** @deprecated */
	public const CARD_TYPE_DISCOVER_NETWORK = 'DISCOVER_NETWORK';
	/** @deprecated */
	public const CARD_TYPE_MASTER_CARD = 'MASTER_CARD';
	/** @deprecated */
	public const CARD_TYPE_GOOGLE_WALLET = 'GOOGLE_WALLET';
	/** @deprecated */
	public const CARD_TYPE_SKRILL = 'SKRILL';

	/**
	 * @deprecated
	 * @param string $cardNumber
	 * @return string|null
	 */
	public static function getCardType(string $cardNumber): ?string
	{
		if (((int)substr($cardNumber, 0, 1)) === 4) {
			return self::CARD_TYPE_VISA;
		}
		if (((int)substr($cardNumber, 0, 1)) === 5) {
			return self::CARD_TYPE_MASTER_CARD;
		}
		if (((int)substr($cardNumber, 0, 1)) === 6) {
			return self::CARD_TYPE_DISCOVER_NETWORK;
		}
		if (((int)substr($cardNumber, 0, 2)) === 37) {
			return self::CARD_TYPE_AMERICAN_EXPRESS;
		}
		if (((int)substr($cardNumber, 0, 2)) === 38) {
			return self::CARD_TYPE_DINNER_CLUB;
		}
		return null;
	}

}