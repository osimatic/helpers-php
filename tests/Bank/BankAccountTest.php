<?php

declare(strict_types=1);

namespace Tests\Bank;

use Osimatic\Bank\BankAccount;
use PHPUnit\Framework\TestCase;

final class BankAccountTest extends TestCase
{
	/* ===================== checkIban() ===================== */

	public function testCheckIbanWithValidFrenchIban(): void
	{
		$this->assertTrue(BankAccount::checkIban('FR1420041010050500013M02606'));
		$this->assertTrue(BankAccount::checkIban('FR7630006000011234567890189'));
	}

	public function testCheckIbanWithValidGermanIban(): void
	{
		$this->assertTrue(BankAccount::checkIban('DE89370400440532013000'));
	}

	public function testCheckIbanWithValidItalianIban(): void
	{
		$this->assertTrue(BankAccount::checkIban('IT60X0542811101000000123456'));
	}

	public function testCheckIbanWithValidSpanishIban(): void
	{
		$this->assertTrue(BankAccount::checkIban('ES9121000418450200051332'));
	}

	public function testCheckIbanWithValidBelgianIban(): void
	{
		$this->assertTrue(BankAccount::checkIban('BE68539007547034'));
	}

	public function testCheckIbanWithValidSwissIban(): void
	{
		$this->assertTrue(BankAccount::checkIban('CH9300762011623852957'));
	}

	public function testCheckIbanWithValidUkIban(): void
	{
		$this->assertTrue(BankAccount::checkIban('GB29NWBK60161331926819'));
	}

	public function testCheckIbanWithSpaces(): void
	{
		$this->assertTrue(BankAccount::checkIban('FR14 2004 1010 0505 0001 3M02 606'));
		$this->assertTrue(BankAccount::checkIban('DE89 3704 0044 0532 0130 00'));
	}

	public function testCheckIbanWithInvalidIban(): void
	{
		$this->assertFalse(BankAccount::checkIban('FR1420041010050500013M02607')); // Mauvaise clé
		$this->assertFalse(BankAccount::checkIban('DE89370400440532013001')); // Mauvaise clé
		$this->assertFalse(BankAccount::checkIban('INVALID'));
		$this->assertFalse(BankAccount::checkIban(''));
	}

	public function testCheckIbanWithInvalidLength(): void
	{
		$this->assertFalse(BankAccount::checkIban('FR142004101005'));
		$this->assertFalse(BankAccount::checkIban('FR142004101005050001'));
	}

	public function testCheckIbanWithInvalidCountryCode(): void
	{
		$this->assertFalse(BankAccount::checkIban('ZZ1420041010050500013M02606'));
		$this->assertFalse(BankAccount::checkIban('XX89370400440532013000'));
	}

	/* ===================== checkBic() ===================== */

	public function testCheckBicWithValidBic8(): void
	{
		$this->assertTrue(BankAccount::checkBic('BNPAFRPP'));
		$this->assertTrue(BankAccount::checkBic('DEUTDEFF'));
		$this->assertTrue(BankAccount::checkBic('CRLYFRPP'));
	}

	public function testCheckBicWithValidBic11(): void
	{
		$this->assertTrue(BankAccount::checkBic('BNPAFRPPXXX'));
		$this->assertTrue(BankAccount::checkBic('DEUTDEFFXXX'));
		$this->assertTrue(BankAccount::checkBic('CRLYFRPP123'));
	}

	public function testCheckBicWithInvalidBic(): void
	{
		$this->assertFalse(BankAccount::checkBic('INVALID'));
		$this->assertFalse(BankAccount::checkBic('BNP'));
		$this->assertFalse(BankAccount::checkBic('BNPAFR'));
		$this->assertFalse(BankAccount::checkBic(''));
	}

	public function testCheckBicWithInvalidLength(): void
	{
		$this->assertFalse(BankAccount::checkBic('BNPAFRP')); // 7 caractères
		$this->assertFalse(BankAccount::checkBic('BNPAFRPPP')); // 9 caractères
		$this->assertFalse(BankAccount::checkBic('BNPAFRPPXX')); // 10 caractères
		$this->assertFalse(BankAccount::checkBic('BNPAFRPPXXXX')); // 12 caractères
	}

	public function testCheckBicWithLowerCase(): void
	{
		// BIC doit être en majuscules selon la norme
		$this->assertFalse(BankAccount::checkBic('bnpafrpp'));
		$this->assertFalse(BankAccount::checkBic('deutdeff'));
	}

	/* ===================== formatIban() ===================== */

	public function testFormatIbanWithFrenchIban(): void
	{
		$result = BankAccount::formatIban('FR1420041010050500013M02606');

		$this->assertIsString($result);
		$this->assertStringContainsString(' ', $result);
		// Vérifie que les espaces sont bien placés tous les 4 caractères
		// La fonction formate 27 caractères avec espaces
		$this->assertSame('FR14 2004 1010 0505 0001 3M02 606', $result);
	}

	public function testFormatIbanLength(): void
	{
		$iban = 'FR1420041010050500013M02606';
		$result = BankAccount::formatIban($iban);

		// IBAN français = 27 caractères + 6 espaces = 33 caractères
		$this->assertSame(33, strlen($result));
	}

	public function testFormatIbanWithShortIban(): void
	{
		// Test avec un IBAN belge (16 caractères)
		$result = BankAccount::formatIban('BE68539007547034');

		$this->assertIsString($result);
		// IBAN belge = 16 caractères + 3 espaces = 19 caractères
		$this->assertSame('BE68 5390 0754 7034', $result);
		$this->assertSame(19, strlen($result));
	}

	public function testFormatIbanPreservesCharacters(): void
	{
		$iban = 'FR1420041010050500013M02606';
		$result = BankAccount::formatIban($iban);

		// Retire les espaces et vérifie que les caractères sont préservés
		$resultWithoutSpaces = str_replace(' ', '', $result);
		$this->assertStringStartsWith('FR1420041010050500013M0', $resultWithoutSpaces);
	}

	/* ===================== Integration tests ===================== */

	public function testValidIbanCanBeFormatted(): void
	{
		$iban = 'FR1420041010050500013M02606';

		// L'IBAN est valide
		$this->assertTrue(BankAccount::checkIban($iban));

		// Il peut être formaté
		$formatted = BankAccount::formatIban($iban);
		$this->assertIsString($formatted);
		$this->assertStringContainsString('FR14', $formatted);
	}

	public function testMultipleCountryIbans(): void
	{
		$ibans = [
			'FR1420041010050500013M02606', // France
			'DE89370400440532013000', // Allemagne
			'ES9121000418450200051332', // Espagne
			'IT60X0542811101000000123456', // Italie
			'BE68539007547034', // Belgique
		];

		foreach ($ibans as $iban) {
			$this->assertTrue(BankAccount::checkIban($iban), "IBAN $iban should be valid");
		}
	}

	public function testMultipleBics(): void
	{
		$bics = [
			'BNPAFRPP', // BNP Paribas France
			'DEUTDEFF', // Deutsche Bank Germany
			'CRLYFRPP', // Crédit Lyonnais France
			'RABONL2U', // Rabobank Netherlands
		];

		foreach ($bics as $bic) {
			$this->assertTrue(BankAccount::checkBic($bic), "BIC $bic should be valid");
		}
	}
}