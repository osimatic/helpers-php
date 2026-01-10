<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\PostalAddress;
use PHPUnit\Framework\TestCase;

final class PostalAddressTest extends TestCase
{
	/* ===================== checkStreet() ===================== */

	public function testCheckStreetValid(): void
	{
		$this->assertTrue(PostalAddress::checkStreet('123 Main Street'));
		$this->assertTrue(PostalAddress::checkStreet('Rue de la Paix'));
		$this->assertTrue(PostalAddress::checkStreet('1'));
		$this->assertTrue(PostalAddress::checkStreet('Avenue des Champs-Élysées'));
	}

	public function testCheckStreetMaxLength(): void
	{
		$longStreet = str_repeat('a', 200);
		$this->assertTrue(PostalAddress::checkStreet($longStreet));

		$tooLongStreet = str_repeat('a', 201);
		$this->assertFalse(PostalAddress::checkStreet($tooLongStreet));
	}

	public function testCheckStreetEmpty(): void
	{
		$this->assertFalse(PostalAddress::checkStreet(''));
	}

	public function testCheckStreetNull(): void
	{
		$this->assertFalse(PostalAddress::checkStreet(null));
	}

	/* ===================== checkPostalCode() ===================== */

	public function testCheckPostalCodeValid(): void
	{
		$this->assertTrue(PostalAddress::checkPostalCode('75001'));
		$this->assertTrue(PostalAddress::checkPostalCode('12345'));
		$this->assertTrue(PostalAddress::checkPostalCode('AB1 2CD'));
		$this->assertTrue(PostalAddress::checkPostalCode('123-456'));
	}

	public function testCheckPostalCodeFranceValid(): void
	{
		$this->assertTrue(PostalAddress::checkPostalCode('75001', 'FR'));
		$this->assertTrue(PostalAddress::checkPostalCode('69001', 'FR'));
		$this->assertTrue(PostalAddress::checkPostalCode('97400', 'FR')); // Réunion
	}

	public function testCheckPostalCodeFranceInvalid(): void
	{
		$this->assertFalse(PostalAddress::checkPostalCode('ABC', 'FR'));
		$this->assertFalse(PostalAddress::checkPostalCode('1234', 'FR')); // trop court
		$this->assertFalse(PostalAddress::checkPostalCode('123456', 'FR')); // trop long
	}

	public function testCheckPostalCodeUSValid(): void
	{
		$this->assertTrue(PostalAddress::checkPostalCode('12345', 'US'));
		$this->assertTrue(PostalAddress::checkPostalCode('12345-6789', 'US'));
	}

	public function testCheckPostalCodeMinMaxLength(): void
	{
		$this->assertTrue(PostalAddress::checkPostalCode('123'));
		$this->assertTrue(PostalAddress::checkPostalCode(str_repeat('1', 15)));

		$this->assertFalse(PostalAddress::checkPostalCode('12')); // trop court
		$this->assertFalse(PostalAddress::checkPostalCode(str_repeat('1', 16))); // trop long
	}

	public function testCheckPostalCodeNull(): void
	{
		$this->assertFalse(PostalAddress::checkPostalCode(null));
	}

	/* ===================== checkZipCode() ===================== */

	public function testCheckZipCodeValid(): void
	{
		$this->assertTrue(PostalAddress::checkZipCode('75001'));
		$this->assertTrue(PostalAddress::checkZipCode('12345'));
	}

	public function testCheckZipCodeInvalid(): void
	{
		$this->assertFalse(PostalAddress::checkZipCode('12'));
		$this->assertFalse(PostalAddress::checkZipCode(null));
	}

	/* ===================== checkCity() ===================== */

	public function testCheckCityValid(): void
	{
		$this->assertTrue(PostalAddress::checkCity('Paris'));
		$this->assertTrue(PostalAddress::checkCity('New York'));
		$this->assertTrue(PostalAddress::checkCity('Saint-Étienne'));
		$this->assertTrue(PostalAddress::checkCity("Aix-en-Provence"));
		$this->assertTrue(PostalAddress::checkCity('München'));
	}

	public function testCheckCityMaxLength(): void
	{
		$longCity = str_repeat('a', 100);
		$this->assertTrue(PostalAddress::checkCity($longCity));

		$tooLongCity = str_repeat('a', 101);
		$this->assertFalse(PostalAddress::checkCity($tooLongCity));
	}

	public function testCheckCityEmpty(): void
	{
		$this->assertFalse(PostalAddress::checkCity(''));
	}

	public function testCheckCityNull(): void
	{
		$this->assertFalse(PostalAddress::checkCity(null));
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