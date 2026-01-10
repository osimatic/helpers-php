<?php

declare(strict_types=1);

namespace Tests\Organization;

use Osimatic\Organization\Company;
use PHPUnit\Framework\TestCase;

final class CompanyTest extends TestCase
{
	/* ===================== checkCompanyName() ===================== */

	public function testCheckCompanyNameValid(): void
	{
		$this->assertTrue(Company::checkCompanyName('ACME Corporation'));
		$this->assertTrue(Company::checkCompanyName('Société Martin & Fils'));
		$this->assertTrue(Company::checkCompanyName('ABC-123'));
		$this->assertTrue(Company::checkCompanyName("L'Entreprise"));
		$this->assertTrue(Company::checkCompanyName('Café Müller'));
		$this->assertTrue(Company::checkCompanyName('Company (France)'));
		$this->assertTrue(Company::checkCompanyName('A.B.C. S.A.'));
	}

	public function testCheckCompanyNameMinLength(): void
	{
		$this->assertTrue(Company::checkCompanyName('ABC'));
		$this->assertFalse(Company::checkCompanyName('AB')); // trop court
	}

	public function testCheckCompanyNameMaxLength(): void
	{
		$longName = str_repeat('a', 100);
		$this->assertTrue(Company::checkCompanyName($longName));

		$tooLongName = str_repeat('a', 101);
		$this->assertFalse(Company::checkCompanyName($tooLongName));
	}

	public function testCheckCompanyNameInvalid(): void
	{
		$this->assertFalse(Company::checkCompanyName(''));
		$this->assertFalse(Company::checkCompanyName('A'));
		$this->assertFalse(Company::checkCompanyName('Company@mail'));
	}

	/* ===================== checkCompanyNumber() ===================== */

	public function testCheckCompanyNumberFrance(): void
	{
		// SIREN valide : 732 829 320 (Wikipédia)
		$this->assertTrue(Company::checkCompanyNumber('FR', '732829320'));

		// SIREN invalide
		$this->assertFalse(Company::checkCompanyNumber('FR', '123456789'));
	}

	public function testCheckCompanyNumberOtherCountry(): void
	{
		// Pour les autres pays, la méthode retourne toujours true
		$this->assertTrue(Company::checkCompanyNumber('US', '123456789'));
		$this->assertTrue(Company::checkCompanyNumber('DE', 'DE123456789'));
	}

	/* ===================== checkFranceSiren() ===================== */

	public function testCheckFranceSirenValid(): void
	{
		// SIREN valide : 732 829 320 (Wikipédia)
		$this->assertTrue(Company::checkFranceSiren('732829320'));

		// SIREN valide : 552 100 554 (Google France)
		$this->assertTrue(Company::checkFranceSiren('552100554'));
	}

	public function testCheckFranceSirenInvalidFormat(): void
	{
		$this->assertFalse(Company::checkFranceSiren('12345678')); // trop court
		$this->assertFalse(Company::checkFranceSiren('1234567890')); // trop long
		$this->assertFalse(Company::checkFranceSiren('ABC123456')); // lettres
		$this->assertFalse(Company::checkFranceSiren(''));
	}

	public function testCheckFranceSirenInvalidLuhn(): void
	{
		// SIREN avec mauvaise clé de contrôle
		$this->assertFalse(Company::checkFranceSiren('123456789'));
		$this->assertFalse(Company::checkFranceSiren('111111111'));
	}

	/* ===================== checkFranceSiret() ===================== */

	public function testCheckFranceSiretValid(): void
	{
		// SIRET valide : 732 829 320 00074 (exemple Wikipédia)
		$this->assertTrue(Company::checkFranceSiret('73282932000074'));

		// SIRET valide : 552 100 554 00012 (Google France)
		$this->assertTrue(Company::checkFranceSiret('55210055400012'));
	}

	public function testCheckFranceSiretInvalidFormat(): void
	{
		$this->assertFalse(Company::checkFranceSiret('1234567890123')); // trop court
		$this->assertFalse(Company::checkFranceSiret('123456789012345')); // trop long
		$this->assertFalse(Company::checkFranceSiret('ABC1234567890')); // lettres
		$this->assertFalse(Company::checkFranceSiret(''));
	}

	public function testCheckFranceSiretInvalidSiren(): void
	{
		// SIRET avec SIREN invalide
		$this->assertFalse(Company::checkFranceSiret('12345678900001'));
	}

	public function testCheckFranceSiretInvalidLuhn(): void
	{
		// SIRET avec SIREN valide mais mauvaise clé de contrôle SIRET
		$this->assertFalse(Company::checkFranceSiret('73282932000075'));
	}

	/* ===================== checkFranceCodeNaf() ===================== */

	public function testCheckFranceCodeNafValid(): void
	{
		$this->assertTrue(Company::checkFranceCodeNaf('01.11Z'));
		$this->assertTrue(Company::checkFranceCodeNaf('62.01Z'));
		$this->assertTrue(Company::checkFranceCodeNaf('47.11F'));
	}

	public function testCheckFranceCodeNafInvalidFormat(): void
	{
		$this->assertFalse(Company::checkFranceCodeNaf(''));
		$this->assertFalse(Company::checkFranceCodeNaf('1234')); // trop court
		$this->assertFalse(Company::checkFranceCodeNaf('123456')); // trop long
		$this->assertFalse(Company::checkFranceCodeNaf('AB.CD'));
	}

	public function testCheckFranceCodeNafInvalidCode(): void
	{
		$this->assertFalse(Company::checkFranceCodeNaf('99.99Z')); // code inexistant
		$this->assertFalse(Company::checkFranceCodeNaf('00.00A'));
	}

	/* ===================== checkFranceCodeApe() ===================== */

	public function testCheckFranceCodeApe(): void
	{
		$this->assertTrue(Company::checkFranceCodeApe('01.11Z'));
		$this->assertFalse(Company::checkFranceCodeApe('99.99Z'));
	}

	/* ===================== getFranceApeCodeList() ===================== */

	public function testGetFranceApeCodeList(): void
	{
		$list = Company::getFranceApeCodeList();
		$this->assertIsArray($list);
		$this->assertNotEmpty($list);
		$this->assertArrayHasKey('01.11Z', $list);
	}

	/* ===================== getFranceApeLabel() ===================== */

	public function testGetFranceApeLabelValid(): void
	{
		$label = Company::getFranceApeLabel('01.11Z');
		$this->assertNotEmpty($label);
		$this->assertIsString($label);
	}

	public function testGetFranceApeLabelInvalid(): void
	{
		$label = Company::getFranceApeLabel('99.99Z');
		$this->assertSame('', $label);
	}

	/* ===================== formatFranceRcs() ===================== */

	public function testFormatFranceRcs(): void
	{
		$rcs = Company::formatFranceRcs('73282932000074');
		$this->assertIsString($rcs);
		$this->assertStringContainsString('B ', $rcs);
		$this->assertStringContainsString('732 829 320', $rcs);
	}

	public function testFormatFranceRcsFormatting(): void
	{
		$rcs = Company::formatFranceRcs('55210055400012');
		$this->assertIsString($rcs);
		$this->assertStringContainsString('B ', $rcs);
		// Vérifie que les espaces sont présents tous les 3 chiffres
		$this->assertMatchesRegularExpression('/B \d{3} \d{3} \d{3}/', $rcs);
	}

	/* ===================== checkMonacoNis() ===================== */

	public function testCheckMonacoNisValid(): void
	{
		$this->assertTrue(Company::checkMonacoNis('12345'));
		$this->assertTrue(Company::checkMonacoNis('ABCDE'));
		$this->assertTrue(Company::checkMonacoNis('A1B2C'));
		$this->assertTrue(Company::checkMonacoNis('1234567890'));
	}

	public function testCheckMonacoNisInvalidLength(): void
	{
		$this->assertFalse(Company::checkMonacoNis('1234')); // trop court
		$this->assertFalse(Company::checkMonacoNis('12345678901')); // trop long
	}

	public function testCheckMonacoNisInvalidCharacters(): void
	{
		$this->assertFalse(Company::checkMonacoNis('ABC-12'));
		$this->assertFalse(Company::checkMonacoNis('ABC@12'));
		$this->assertFalse(Company::checkMonacoNis(''));
	}

	public function testCheckMonacoNisLowerCase(): void
	{
		// La regex n'accepte que les majuscules
		$this->assertFalse(Company::checkMonacoNis('abcde'));
	}
}
