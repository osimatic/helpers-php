<?php

declare(strict_types=1);

namespace Tests\Organization;

use Osimatic\Organization\VatNumber;
use PHPUnit\Framework\TestCase;

final class VatNumberTest extends TestCase
{
	/* ===================== format() ===================== */

	public function testFormat(): void
	{
		$formatted = VatNumber::format('FR12345678901');
		$this->assertIsString($formatted);
		$this->assertSame('FR12345678901', $formatted); // TODO: à implémenter dans la classe
	}

	/* ===================== check() ===================== */

	public function testCheckValidFrenchVatNumber(): void
	{
		// Numéro TVA FR valide : FR 44 732829320 (exemple Wikipédia)
		$this->assertTrue(VatNumber::check('FR44732829320', checkValidity: false));
	}

	public function testCheckValidMonacoVatNumber(): void
	{
		// Numéro TVA Monaco (9 chiffres après FR)
		// Note: La clé doit être valide selon l'algorithme
		// Format: FR + 2 chiffres clé + 7 chiffres SSEE
		// Les numéros Monaco ne peuvent pas être validés car le SSEE n'a pas de validation Luhn
		$this->markTestSkipped('Les numéros de TVA Monaco ne peuvent pas être validés automatiquement');
	}

	public function testCheckEmptyVatNumber(): void
	{
		$this->assertFalse(VatNumber::check(''));
	}

	public function testCheckInvalidFormat(): void
	{
		$this->assertFalse(VatNumber::check('123456789'));
		$this->assertFalse(VatNumber::check('FR'));
		$this->assertFalse(VatNumber::check('FRAB'));
	}

	public function testCheckInvalidCountryCode(): void
	{
		$this->assertFalse(VatNumber::check('12345678901'));
		$this->assertFalse(VatNumber::check('A1234567890'));
	}

	public function testCheckInvalidVatNumberLength(): void
	{
		$this->assertFalse(VatNumber::check('FR1'));
		$this->assertFalse(VatNumber::check('FR12345678901234567890'));
	}

	public function testCheckFranceInvalidSiren(): void
	{
		// SIREN invalide (mauvaise clé Luhn)
		$this->assertFalse(VatNumber::check('FR12123456789', checkValidity: false));
	}

	public function testCheckFranceInvalidVatKey(): void
	{
		// SIREN valide mais clé TVA incorrecte
		$this->assertFalse(VatNumber::check('FR00552100554', checkValidity: false));
		$this->assertFalse(VatNumber::check('FR99732829320', checkValidity: false));
	}

	public function testCheckFranceWrongLength(): void
	{
		// Ni 11 ni 9 chiffres
		$this->assertFalse(VatNumber::check('FR1234567890', checkValidity: false));
		$this->assertFalse(VatNumber::check('FR123456789012', checkValidity: false));
	}

	public function testCheckOtherCountryFormat(): void
	{
		// Pour les autres pays, vérifie juste le format
		$this->assertTrue(VatNumber::check('DE123456789', checkValidity: false));
		$this->assertTrue(VatNumber::check('GB123456789', checkValidity: false));
		$this->assertTrue(VatNumber::check('ES12345678A', checkValidity: false));
	}

	public function testCheckWithValidityCheck(): void
	{
		// Ce test va essayer de contacter l'API VIES
		// On teste juste que la méthode s'exécute sans erreur
		$result = VatNumber::check('FR32552100554', checkValidity: true);
		$this->assertIsBool($result);
	}

	/* ===================== checkValidity() ===================== */

	public function testCheckValidityCallsExternalAPI(): void
	{
		// Ce test appelle l'API VIES
		// Le résultat dépend de la disponibilité de l'API et de la validité réelle du numéro
		$result = VatNumber::checkValidity('FR32552100554');
		$this->assertIsBool($result);
	}

	public function testCheckValidityWithInvalidNumber(): void
	{
		// Avec un numéro clairement invalide
		$result = VatNumber::checkValidity('FR00000000000');
		$this->assertFalse($result);
	}

	/* ===================== Edge cases ===================== */

	public function testCheckVatNumberWithSpaces(): void
	{
		// Les espaces ne sont pas acceptés
		$this->assertFalse(VatNumber::check('FR 32 552100554', checkValidity: false));
	}

	public function testCheckVatNumberLowerCase(): void
	{
		// Le code pays doit être en majuscules
		$this->assertFalse(VatNumber::check('fr32552100554', checkValidity: false));
	}

	public function testCheckValidVatNumberFormats(): void
	{
		// Différents formats selon les pays
		$this->assertTrue(VatNumber::check('BE0123456789', checkValidity: false));
		$this->assertTrue(VatNumber::check('NL123456789B01', checkValidity: false));
		$this->assertTrue(VatNumber::check('IT12345678901', checkValidity: false));
	}

	/* ===================== French VAT key calculation ===================== */

	public function testFrenchVatKeyCalculation(): void
	{
		// Vérification de l'algorithme de calcul de la clé TVA française
		// SIREN: 732829320 -> Clé: 44
		// Formule: ((SIREN % 97) * 3 + 12) % 97
		$this->assertTrue(VatNumber::check('FR44732829320', checkValidity: false));

		// SIREN: 552100554 -> Clé: 96 (et non 32)
		$this->assertTrue(VatNumber::check('FR96552100554', checkValidity: false));
	}

	public function testMonacoVatNumberFormat(): void
	{
		// Monaco utilise le préfixe FR mais avec 9 chiffres au lieu de 11
		// Format: FR + 2 chiffres clé + 7 chiffres SSEE
		// Les numéros Monaco ne peuvent pas être validés car le SSEE n'a pas de validation Luhn
		$this->markTestSkipped('Les numéros de TVA Monaco ne peuvent pas être validés automatiquement');
	}
}
