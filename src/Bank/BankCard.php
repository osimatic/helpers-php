<?php

namespace Osimatic\Bank;

use Osimatic\Calendar\Date;
use Osimatic\Calendar\DateTime;

class BankCard
{
	/**
	 * @param string $date
	 * @return \DateTime|null
	 */
	public static function getExpirationDateFromString(string $date): ?\DateTime
	{
		if (str_contains($date, '/') && count($dateArr = explode('/', $date)) === 2 && !empty($year = $dateArr[1] ?? null) && !empty($month = $dateArr[0] ?? null)) {
			return self::getExpirationDateFromYearAndMonth($year, $month);
		}
		return Date::parse($date);
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @return \DateTime|null
	 */
	public static function getExpirationDateFromYearAndMonth(int $year, int $month): ?\DateTime
	{
		try {
			return new \DateTime($year . '-' . $month . '-' .Date::getNumberOfDaysInMonth($year, $month).' 00:00:00');
		} catch (\Exception) {}
		return null;
	}

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
}