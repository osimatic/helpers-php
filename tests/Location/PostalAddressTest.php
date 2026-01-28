<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\PostalAddress;
use PHPUnit\Framework\TestCase;

final class PostalAddressTest extends TestCase
{
	/* ===================== isValidStreet() ===================== */

	public function testCheckStreetValid(): void
	{
		$this->assertTrue(PostalAddress::isValidStreet('123 Main Street'));
		$this->assertTrue(PostalAddress::isValidStreet('Rue de la Paix'));
		$this->assertTrue(PostalAddress::isValidStreet('1'));
		$this->assertTrue(PostalAddress::isValidStreet('Avenue des Champs-Élysées'));
	}

	public function testCheckStreetMaxLength(): void
	{
		$longStreet = str_repeat('a', 200);
		$this->assertTrue(PostalAddress::isValidStreet($longStreet));

		$tooLongStreet = str_repeat('a', 201);
		$this->assertFalse(PostalAddress::isValidStreet($tooLongStreet));
	}

	public function testCheckStreetEmpty(): void
	{
		$this->assertFalse(PostalAddress::isValidStreet(''));
	}

	public function testCheckStreetNull(): void
	{
		$this->assertFalse(PostalAddress::isValidStreet(null));
	}

	/* ===================== isValidPostalCode() ===================== */

	public function testCheckPostalCodeValid(): void
	{
		$this->assertTrue(PostalAddress::isValidPostalCode('75001'));
		$this->assertTrue(PostalAddress::isValidPostalCode('12345'));
		$this->assertTrue(PostalAddress::isValidPostalCode('AB1 2CD'));
		$this->assertTrue(PostalAddress::isValidPostalCode('123-456'));
	}

	public function testCheckPostalCodeFranceValid(): void
	{
		$this->assertTrue(PostalAddress::isValidPostalCode('75001', 'FR'));
		$this->assertTrue(PostalAddress::isValidPostalCode('69001', 'FR'));
		$this->assertTrue(PostalAddress::isValidPostalCode('97400', 'FR')); // Réunion
	}

	public function testCheckPostalCodeFranceInvalid(): void
	{
		$this->assertFalse(PostalAddress::isValidPostalCode('ABC', 'FR'));
		$this->assertFalse(PostalAddress::isValidPostalCode('1234', 'FR')); // trop court
		$this->assertFalse(PostalAddress::isValidPostalCode('123456', 'FR')); // trop long
	}

	public function testCheckPostalCodeUSValid(): void
	{
		$this->assertTrue(PostalAddress::isValidPostalCode('12345', 'US'));
		$this->assertTrue(PostalAddress::isValidPostalCode('12345-6789', 'US'));
	}

	public function testCheckPostalCodeMinMaxLength(): void
	{
		$this->assertTrue(PostalAddress::isValidPostalCode('123'));
		$this->assertTrue(PostalAddress::isValidPostalCode(str_repeat('1', 15)));

		$this->assertFalse(PostalAddress::isValidPostalCode('12')); // trop court
		$this->assertFalse(PostalAddress::isValidPostalCode(str_repeat('1', 16))); // trop long
	}

	public function testCheckPostalCodeNull(): void
	{
		$this->assertFalse(PostalAddress::isValidPostalCode(null));
	}

	/* ===================== isValidZipCode() ===================== */

	public function testCheckZipCodeValid(): void
	{
		$this->assertTrue(PostalAddress::isValidZipCode('75001'));
		$this->assertTrue(PostalAddress::isValidZipCode('12345'));
	}

	public function testCheckZipCodeInvalid(): void
	{
		$this->assertFalse(PostalAddress::isValidZipCode('12'));
		$this->assertFalse(PostalAddress::isValidZipCode(null));
	}

	/* ===================== isValidCity() ===================== */

	public function testCheckCityValid(): void
	{
		$this->assertTrue(PostalAddress::isValidCity('Paris'));
		$this->assertTrue(PostalAddress::isValidCity('New York'));
		$this->assertTrue(PostalAddress::isValidCity('Saint-Étienne'));
		$this->assertTrue(PostalAddress::isValidCity("Aix-en-Provence"));
		$this->assertTrue(PostalAddress::isValidCity('München'));
	}

	public function testCheckCityMaxLength(): void
	{
		$longCity = str_repeat('a', 100);
		$this->assertTrue(PostalAddress::isValidCity($longCity));

		$tooLongCity = str_repeat('a', 101);
		$this->assertFalse(PostalAddress::isValidCity($tooLongCity));
	}

	public function testCheckCityEmpty(): void
	{
		$this->assertFalse(PostalAddress::isValidCity(''));
	}

	public function testCheckCityNull(): void
	{
		$this->assertFalse(PostalAddress::isValidCity(null));
	}

	/* ===================== replaceSpecialChar() ===================== */

	public function testReplaceSpecialChar(): void
	{
		// Caractère arabe pour la virgule
		$result = PostalAddress::replaceSpecialChar('123،Paris');
		$this->assertStringContainsString(',', $result);

		// Apostrophe spéciale
		$result = PostalAddress::replaceSpecialChar('Ĺexemple');
		$this->assertStringContainsString("'", $result);

		// Numéro spécial
		$result = PostalAddress::replaceSpecialChar('№123');
		$this->assertStringContainsString('N°', $result);
	}

	public function testReplaceSpecialCharNull(): void
	{
		$this->assertNull(PostalAddress::replaceSpecialChar(null));
	}

	public function testReplaceSpecialCharNormalString(): void
	{
		$input = 'Normal Street 123';
		$result = PostalAddress::replaceSpecialChar($input);
		$this->assertNotNull($result);
		$this->assertIsString($result);
	}
}