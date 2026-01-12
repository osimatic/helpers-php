<?php

declare(strict_types=1);

namespace Tests\Bank;

use Osimatic\Bank\BankCard;
use Osimatic\Bank\BankCardType;
use PHPUnit\Framework\TestCase;

final class BankCardTest extends TestCase
{
	/* ===================== checkCardNumber() ===================== */

	public function testCheckCardNumberWithValidVisa(): void
	{
		// Numéros de test Visa valides (algorithme Luhn)
		$this->assertTrue(BankCard::checkCardNumber('4111111111111111'));
		$this->assertTrue(BankCard::checkCardNumber('4012888888881881'));
		$this->assertTrue(BankCard::checkCardNumber('4222222222222'));
	}

	public function testCheckCardNumberWithValidMastercard(): void
	{
		// Numéros de test Mastercard valides
		$this->assertTrue(BankCard::checkCardNumber('5555555555554444'));
		$this->assertTrue(BankCard::checkCardNumber('5105105105105100'));
	}

	public function testCheckCardNumberWithValidAmex(): void
	{
		// Numéros de test American Express valides
		$this->assertTrue(BankCard::checkCardNumber('378282246310005'));
		$this->assertTrue(BankCard::checkCardNumber('371449635398431'));
	}

	public function testCheckCardNumberWithInvalidNumber(): void
	{
		$this->assertFalse(BankCard::checkCardNumber('1234567890123456'));
		$this->assertFalse(BankCard::checkCardNumber('0000000000000000'));
		$this->assertFalse(BankCard::checkCardNumber('invalid'));
		$this->assertFalse(BankCard::checkCardNumber(''));
	}

	public function testCheckCardNumberWithInvalidLength(): void
	{
		$this->assertFalse(BankCard::checkCardNumber('4111'));
		$this->assertFalse(BankCard::checkCardNumber('411111111111111111111'));
	}

	/* ===================== checkCardCSC() ===================== */

	public function testCheckCardCSCWithValidThreeDigits(): void
	{
		$this->assertTrue(BankCard::checkCardCSC('123'));
		$this->assertTrue(BankCard::checkCardCSC('456'));
		$this->assertTrue(BankCard::checkCardCSC('789'));
	}

	public function testCheckCardCSCWithValidFourDigits(): void
	{
		$this->assertTrue(BankCard::checkCardCSC('1234'));
		$this->assertTrue(BankCard::checkCardCSC('5678'));
	}

	public function testCheckCardCSCWithInvalidLength(): void
	{
		$this->assertFalse(BankCard::checkCardCSC('12'));
		$this->assertFalse(BankCard::checkCardCSC('1'));
		$this->assertFalse(BankCard::checkCardCSC('12345'));
		$this->assertFalse(BankCard::checkCardCSC(''));
	}

	/* ===================== formatCardNumber() ===================== */

	public function testFormatCardNumberWithSixteenDigits(): void
	{
		$this->assertSame('4111-1111-1111-1111', BankCard::formatCardNumber('4111111111111111'));
		$this->assertSame('5555-5555-5555-4444', BankCard::formatCardNumber('5555555555554444'));
	}

	public function testFormatCardNumberWithAsterisks(): void
	{
		$this->assertSame('4111-XXXX-XXXX-1111', BankCard::formatCardNumber('4111********1111'));
	}

	public function testFormatCardNumberWithOtherLength(): void
	{
		// Ne formate pas si longueur différente de 16
		$this->assertSame('411111111111', BankCard::formatCardNumber('411111111111'));
		$this->assertSame('378282246310005', BankCard::formatCardNumber('378282246310005'));
	}

	/* ===================== getExpirationDateFromYearAndMonth() ===================== */

	public function testGetExpirationDateFromYearAndMonth(): void
	{
		$date = BankCard::getExpirationDateFromYearAndMonth(2025, 12);

		$this->assertInstanceOf(\DateTime::class, $date);
		$this->assertSame('2025', $date->format('Y'));
		$this->assertSame('12', $date->format('m'));
		$this->assertSame('31', $date->format('d')); // Dernier jour du mois
	}

	public function testGetExpirationDateFromYearAndMonthWithFebruary(): void
	{
		$date = BankCard::getExpirationDateFromYearAndMonth(2024, 2);

		$this->assertInstanceOf(\DateTime::class, $date);
		$this->assertSame('2024', $date->format('Y'));
		$this->assertSame('02', $date->format('m'));
		$this->assertSame('29', $date->format('d')); // 2024 est bissextile
	}

	public function testGetExpirationDateFromYearAndMonthWithFebruaryNonLeapYear(): void
	{
		$date = BankCard::getExpirationDateFromYearAndMonth(2025, 2);

		$this->assertInstanceOf(\DateTime::class, $date);
		$this->assertSame('2025', $date->format('Y'));
		$this->assertSame('02', $date->format('m'));
		$this->assertSame('28', $date->format('d'));
	}

	public function testGetExpirationDateFromYearAndMonthWithThirtyDays(): void
	{
		$date = BankCard::getExpirationDateFromYearAndMonth(2025, 4);

		$this->assertInstanceOf(\DateTime::class, $date);
		$this->assertSame('30', $date->format('d'));
	}

	public function testGetExpirationDateFromYearAndMonthWithInvalidMonth(): void
	{
		$date = BankCard::getExpirationDateFromYearAndMonth(2025, 13);
		$this->assertNull($date);
	}

	public function testGetExpirationDateFromYearAndMonthWithZeroMonth(): void
	{
		$date = BankCard::getExpirationDateFromYearAndMonth(2025, 0);
		$this->assertNull($date);
	}

	/* ===================== getExpirationDateFromString() ===================== */

	public function testGetExpirationDateFromStringWithSlashFormat(): void
	{
		$date = BankCard::getExpirationDateFromString('12/2025');

		$this->assertInstanceOf(\DateTime::class, $date);
		$this->assertSame('2025', $date->format('Y'));
		$this->assertSame('12', $date->format('m'));
		$this->assertSame('31', $date->format('d'));
	}

	public function testGetExpirationDateFromStringWithSlashFormatShortYear(): void
	{
		$date = BankCard::getExpirationDateFromString('12/25');

		$this->assertInstanceOf(\DateTime::class, $date);
		$this->assertSame('25', $date->format('y'));
		$this->assertSame('12', $date->format('m'));
	}

	public function testGetExpirationDateFromStringWithStandardDate(): void
	{
		$date = BankCard::getExpirationDateFromString('2025-12-31');

		$this->assertInstanceOf(\DateTime::class, $date);
		$this->assertSame('2025', $date->format('Y'));
		$this->assertSame('12', $date->format('m'));
		$this->assertSame('31', $date->format('d'));
	}

	public function testGetExpirationDateFromStringWithInvalidFormat(): void
	{
		$date = BankCard::getExpirationDateFromString('invalid');
		$this->assertNull($date);
	}

	public function testGetExpirationDateFromStringWithEmptyString(): void
	{
		$date = BankCard::getExpirationDateFromString('');
		$this->assertNull($date);
	}

	/* ===================== formatCardExpirationDate() ===================== */

	public function testFormatCardExpirationDateWithLongFormat(): void
	{
		$date = new \DateTime('2025-12-31');
		$result = BankCard::formatCardExpirationDate($date, \IntlDateFormatter::LONG);

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
		// Le résultat dépend de la locale, mais devrait contenir 2025
		$this->assertStringContainsString('2025', $result);
	}

	public function testFormatCardExpirationDateWithShortFormat(): void
	{
		$date = new \DateTime('2025-12-31');
		$result = BankCard::formatCardExpirationDate($date, \IntlDateFormatter::SHORT);

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
		$this->assertStringContainsString('12', $result);
		$this->assertStringContainsString('2025', $result);
	}

	public function testFormatCardExpirationDateWithNull(): void
	{
		$result = BankCard::formatCardExpirationDate(null);
		$this->assertNull($result);
	}

	/* ===================== getType() ===================== */

	public function testGetTypeWithVisa(): void
	{
		$this->assertSame(BankCardType::VISA, BankCard::getType('4111111111111111'));
		$this->assertSame(BankCardType::VISA, BankCard::getType('4012888888881881'));
	}

	public function testGetTypeWithMastercard(): void
	{
		$this->assertSame(BankCardType::MASTER_CARD, BankCard::getType('5555555555554444'));
		$this->assertSame(BankCardType::MASTER_CARD, BankCard::getType('5105105105105100'));
	}

	public function testGetTypeWithAmex(): void
	{
		$this->assertSame(BankCardType::AMERICAN_EXPRESS, BankCard::getType('378282246310005'));
		$this->assertSame(BankCardType::AMERICAN_EXPRESS, BankCard::getType('371449635398431'));
	}

	public function testGetTypeWithDiscoverNetwork(): void
	{
		$this->assertSame(BankCardType::DISCOVER_NETWORK, BankCard::getType('6011111111111117'));
	}

	public function testGetTypeWithDinnerClub(): void
	{
		$this->assertSame(BankCardType::DINNER_CLUB, BankCard::getType('38000000000006'));
	}

	public function testGetTypeWithUnknownCard(): void
	{
		$this->assertNull(BankCard::getType('1234567890123456'));
		$this->assertNull(BankCard::getType('9999999999999999'));
	}

	/* ===================== Integration tests ===================== */

	public function testCompleteCardValidation(): void
	{
		$cardNumber = '4111111111111111';
		$csc = '123';
		$expirationDate = '12/2025';

		// Validation complète
		$this->assertTrue(BankCard::checkCardNumber($cardNumber));
		$this->assertTrue(BankCard::checkCardCSC($csc));
		$this->assertInstanceOf(\DateTime::class, BankCard::getExpirationDateFromString($expirationDate));
		$this->assertSame(BankCardType::VISA, BankCard::getType($cardNumber));
		$this->assertSame('4111-1111-1111-1111', BankCard::formatCardNumber($cardNumber));
	}

	public function testFormatAndTypeConsistency(): void
	{
		$cardNumber = '5555555555554444';
		$formatted = BankCard::formatCardNumber($cardNumber);
		$type = BankCard::getType($cardNumber);

		$this->assertSame('5555-5555-5555-4444', $formatted);
		$this->assertSame(BankCardType::MASTER_CARD, $type);
	}
}