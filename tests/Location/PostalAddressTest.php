<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\PostalAddress;
use PHPUnit\Framework\TestCase;

final class PostalAddressTest extends TestCase
{
	// ========== Validation ==========

	public function testIsValidStreet(): void
	{
		// Valid cases
		$this->assertTrue(PostalAddress::isValidStreet('123 Main Street'));
		$this->assertTrue(PostalAddress::isValidStreet('Rue de la Paix'));
		$this->assertTrue(PostalAddress::isValidStreet('1'));
		$this->assertTrue(PostalAddress::isValidStreet('Avenue des Champs-Élysées'));

		// Max length (200 characters)
		$longStreet = str_repeat('a', 200);
		$this->assertTrue(PostalAddress::isValidStreet($longStreet));

		// Too long (201 characters)
		$tooLongStreet = str_repeat('a', 201);
		$this->assertFalse(PostalAddress::isValidStreet($tooLongStreet));

		// Empty string
		$this->assertFalse(PostalAddress::isValidStreet(''));

		// Null value
		$this->assertFalse(PostalAddress::isValidStreet(null));
	}

	public function testIsValidPostalCode(): void
	{
		// Valid cases without country
		$this->assertTrue(PostalAddress::isValidPostalCode('75001'));
		$this->assertTrue(PostalAddress::isValidPostalCode('12345'));
		$this->assertTrue(PostalAddress::isValidPostalCode('AB1 2CD'));
		$this->assertTrue(PostalAddress::isValidPostalCode('123-456'));

		// France - valid cases
		$this->assertTrue(PostalAddress::isValidPostalCode('75001', 'FR'));
		$this->assertTrue(PostalAddress::isValidPostalCode('69001', 'FR'));
		$this->assertTrue(PostalAddress::isValidPostalCode('97400', 'FR')); // Réunion

		// France - invalid cases
		$this->assertFalse(PostalAddress::isValidPostalCode('ABC', 'FR'));
		$this->assertFalse(PostalAddress::isValidPostalCode('1234', 'FR')); // trop court
		$this->assertFalse(PostalAddress::isValidPostalCode('123456', 'FR')); // trop long

		// United States - valid cases
		$this->assertTrue(PostalAddress::isValidPostalCode('12345', 'US'));
		$this->assertTrue(PostalAddress::isValidPostalCode('12345-6789', 'US'));

		// Min/max length without country (3-15 characters)
		$this->assertTrue(PostalAddress::isValidPostalCode('123'));
		$this->assertTrue(PostalAddress::isValidPostalCode(str_repeat('1', 15)));
		$this->assertFalse(PostalAddress::isValidPostalCode('12')); // trop court
		$this->assertFalse(PostalAddress::isValidPostalCode(str_repeat('1', 16))); // trop long

		// Null value
		$this->assertFalse(PostalAddress::isValidPostalCode(null));
	}

	public function testIsValidZipCode(): void
	{
		// Valid cases
		$this->assertTrue(PostalAddress::isValidZipCode('75001'));
		$this->assertTrue(PostalAddress::isValidZipCode('12345'));

		// Invalid cases
		$this->assertFalse(PostalAddress::isValidZipCode('12'));
		$this->assertFalse(PostalAddress::isValidZipCode(null));
	}

	public function testIsValidCity(): void
	{
		// Valid cases
		$this->assertTrue(PostalAddress::isValidCity('Paris'));
		$this->assertTrue(PostalAddress::isValidCity('New York'));
		$this->assertTrue(PostalAddress::isValidCity('Saint-Étienne'));
		$this->assertTrue(PostalAddress::isValidCity("Aix-en-Provence"));
		$this->assertTrue(PostalAddress::isValidCity('München'));

		// Max length (100 characters)
		$longCity = str_repeat('a', 100);
		$this->assertTrue(PostalAddress::isValidCity($longCity));

		// Too long (101 characters)
		$tooLongCity = str_repeat('a', 101);
		$this->assertFalse(PostalAddress::isValidCity($tooLongCity));

		// Empty string
		$this->assertFalse(PostalAddress::isValidCity(''));

		// Null value
		$this->assertFalse(PostalAddress::isValidCity(null));
	}

	// ========== Display ==========

	public function testFormat(): void
	{
		// Test with complete address - France
		$address = $this->createMock(\Osimatic\Location\PostalAddressInterface::class);
		$address->method('getCountryCode')->willReturn('FR');
		$address->method('getCity')->willReturn('Paris');
		$address->method('getPostcode')->willReturn('75001');
		$address->method('getRoad')->willReturn('1 Rue de Rivoli');
		$address->method('getState')->willReturn(null);
		$address->method('getAttention')->willReturn('John Doe');

		$result = PostalAddress::format($address);
		$expected = 'John Doe<br/>1 Rue de Rivoli<br/>75001 Paris<br/>France';
		$this->assertSame($expected, $result);

		// Test with United States address
		$address = $this->createMock(\Osimatic\Location\PostalAddressInterface::class);
		$address->method('getCountryCode')->willReturn('US');
		$address->method('getCity')->willReturn('New York');
		$address->method('getPostcode')->willReturn('10001');
		$address->method('getRoad')->willReturn('350 Fifth Avenue');
		$address->method('getState')->willReturn('NY');
		$address->method('getAttention')->willReturn('Jane Smith');

		$result = PostalAddress::format($address);
		$expected = 'Jane Smith<br/>350 Fifth Avenue<br/>New York, NY 10001<br/>États-Unis';
		$this->assertSame($expected, $result);

		// Test without attention
		$address = $this->createMock(\Osimatic\Location\PostalAddressInterface::class);
		$address->method('getCountryCode')->willReturn('FR');
		$address->method('getCity')->willReturn('Lyon');
		$address->method('getPostcode')->willReturn('69001');
		$address->method('getRoad')->willReturn('1 Place Bellecour');
		$address->method('getState')->willReturn(null);
		$address->method('getAttention')->willReturn(null);

		$result = PostalAddress::format($address, withAttention: false);
		$expected = '1 Place Bellecour<br/>69001 Lyon<br/>France';
		$this->assertSame($expected, $result);

		// Test with custom separator
		$address = $this->createMock(\Osimatic\Location\PostalAddressInterface::class);
		$address->method('getCountryCode')->willReturn('FR');
		$address->method('getCity')->willReturn('Marseille');
		$address->method('getPostcode')->willReturn('13001');
		$address->method('getRoad')->willReturn('Vieux Port');
		$address->method('getState')->willReturn(null);
		$address->method('getAttention')->willReturn(null);

		$result = PostalAddress::format($address, separator: ' | ');
		$expected = 'Vieux Port | 13001 Marseille | France';
		$this->assertSame($expected, $result);

		// Test with null country code
		$address = $this->createMock(\Osimatic\Location\PostalAddressInterface::class);
		$address->method('getCountryCode')->willReturn(null);
		$address->method('getCity')->willReturn('Paris');
		$address->method('getPostcode')->willReturn('75001');
		$address->method('getRoad')->willReturn('1 Rue de Rivoli');
		$address->method('getState')->willReturn(null);
		$address->method('getAttention')->willReturn(null);

		$result = PostalAddress::format($address);
		$this->assertNull($result); // Should return null without country code

		// Test with custom locale
		$address = $this->createMock(\Osimatic\Location\PostalAddressInterface::class);
		$address->method('getCountryCode')->willReturn('FR');
		$address->method('getCity')->willReturn('Nice');
		$address->method('getPostcode')->willReturn('06000');
		$address->method('getRoad')->willReturn('Promenade des Anglais');
		$address->method('getState')->willReturn(null);
		$address->method('getAttention')->willReturn(null);

		$result = PostalAddress::format($address, locale: 'fr_FR');
		$expected = 'Promenade des Anglais<br/>06000 Nice<br/>France';
		$this->assertSame($expected, $result);
	}

	public function testFormatInline(): void
	{
		// Test with complete address - default comma separator
		$address = $this->createMock(\Osimatic\Location\PostalAddressInterface::class);
		$address->method('getCountryCode')->willReturn('FR');
		$address->method('getCity')->willReturn('Paris');
		$address->method('getPostcode')->willReturn('75001');
		$address->method('getRoad')->willReturn('1 Rue de Rivoli');
		$address->method('getState')->willReturn(null);
		$address->method('getAttention')->willReturn('John Doe');

		$result = PostalAddress::formatInline($address);
		$expected = 'John Doe, 1 Rue de Rivoli, 75001 Paris, France';
		$this->assertSame($expected, $result);

		// Test with custom separator
		$address = $this->createMock(\Osimatic\Location\PostalAddressInterface::class);
		$address->method('getCountryCode')->willReturn('FR');
		$address->method('getCity')->willReturn('Lyon');
		$address->method('getPostcode')->willReturn('69001');
		$address->method('getRoad')->willReturn('1 Place Bellecour');
		$address->method('getState')->willReturn(null);
		$address->method('getAttention')->willReturn(null);

		$result = PostalAddress::formatInline($address, separator: ' - ');
		$expected = '1 Place Bellecour - 69001 Lyon - France';
		$this->assertSame($expected, $result);

		// Test without attention
		$address = $this->createMock(\Osimatic\Location\PostalAddressInterface::class);
		$address->method('getCountryCode')->willReturn('US');
		$address->method('getCity')->willReturn('New York');
		$address->method('getPostcode')->willReturn('10001');
		$address->method('getRoad')->willReturn('350 Fifth Avenue');
		$address->method('getState')->willReturn('NY');
		$address->method('getAttention')->willReturn(null);

		$result = PostalAddress::formatInline($address, withAttention: false);
		$expected = '350 Fifth Avenue, New York, NY 10001, États-Unis';
		$this->assertSame($expected, $result);

		// Test with pipe separator
		$address = $this->createMock(\Osimatic\Location\PostalAddressInterface::class);
		$address->method('getCountryCode')->willReturn('GB');
		$address->method('getCity')->willReturn('London');
		$address->method('getPostcode')->willReturn('SW1A 1AA');
		$address->method('getRoad')->willReturn('10 Downing Street');
		$address->method('getState')->willReturn(null);
		$address->method('getAttention')->willReturn(null);

		$result = PostalAddress::formatInline($address, separator: ' | ');
		$expected = '10 Downing Street | London | SW1A 1AA | Royaume-Uni';
		$this->assertSame($expected, $result);
	}

	public function testFormatFromComponents(): void
	{
		// Test with complete address - France
		$result = PostalAddress::formatFromComponents(
			countryCode: 'FR',
			city: 'Paris',
			postcode: '75001',
			road: '1 Rue de Rivoli',
			state: null,
			attention: 'John Doe'
		);
		$expected = 'John Doe<br/>1 Rue de Rivoli<br/>75001 Paris<br/>France';
		$this->assertSame($expected, $result);

		// Test with complete address - United States
		$result = PostalAddress::formatFromComponents(
			countryCode: 'US',
			city: 'New York',
			postcode: '10001',
			road: '350 Fifth Avenue',
			state: 'NY',
			attention: 'Jane Smith'
		);
		$expected = 'Jane Smith<br/>350 Fifth Avenue<br/>New York, NY 10001<br/>États-Unis';
		$this->assertSame($expected, $result);

		// Test with minimal data (only country code)
		$result = PostalAddress::formatFromComponents(countryCode: 'FR');
		$expected = 'France';
		$this->assertSame($expected, $result);

		// Test with custom separator
		$result = PostalAddress::formatFromComponents(
			countryCode: 'FR',
			city: 'Lyon',
			postcode: '69001',
			road: '1 Place Bellecour',
			separator: ' | '
		);
		$expected = '1 Place Bellecour | 69001 Lyon | France';
		$this->assertSame($expected, $result);

		// Test with custom locale
		$result = PostalAddress::formatFromComponents(
			countryCode: 'FR',
			city: 'Marseille',
			postcode: '13001',
			road: 'Vieux Port',
			locale: 'fr_FR'
		);
		$expected = 'Vieux Port<br/>13001 Marseille<br/>France';
		$this->assertSame($expected, $result);

		// Test United Kingdom format
		$result = PostalAddress::formatFromComponents(
			countryCode: 'GB',
			city: 'London',
			postcode: 'SW1A 1AA',
			road: '10 Downing Street'
		);
		$expected = '10 Downing Street<br/>London<br/>SW1A 1AA<br/>Royaume-Uni';
		$this->assertSame($expected, $result);

		// Test Germany format
		$result = PostalAddress::formatFromComponents(
			countryCode: 'DE',
			city: 'Berlin',
			postcode: '10115',
			road: 'Unter den Linden 77'
		);
		$expected = 'Unter den Linden 77<br/>10115 Berlin<br/>Allemagne';
		$this->assertSame($expected, $result);

		// Test without attention
		$result = PostalAddress::formatFromComponents(
			countryCode: 'FR',
			city: 'Nice',
			postcode: '06000',
			road: 'Promenade des Anglais',
			attention: null
		);
		$expected = 'Promenade des Anglais<br/>06000 Nice<br/>France';
		$this->assertSame($expected, $result);

		// Test with only partial data
		$result = PostalAddress::formatFromComponents(
			countryCode: 'FR',
			city: 'Toulouse',
			postcode: '31000'
		);
		$expected = '31000 Toulouse<br/>France';
		$this->assertSame($expected, $result);

		// Test with state (administrative area)
		$result = PostalAddress::formatFromComponents(
			countryCode: 'CA',
			city: 'Toronto',
			postcode: 'M5H 2N2',
			road: '301 Front Street West',
			state: 'ON'
		);
		$expected = '301 Front Street West<br/>Toronto ON M5H 2N2<br/>Canada';
		$this->assertSame($expected, $result);
	}

	public function testFormatInlineFromComponents(): void
	{
		// Test with complete address - default comma separator
		$result = PostalAddress::formatInlineFromComponents(
			countryCode: 'FR',
			city: 'Paris',
			postcode: '75001',
			road: '1 Rue de Rivoli',
			attention: 'John Doe'
		);
		$expected = 'John Doe, 1 Rue de Rivoli, 75001 Paris, France';
		$this->assertSame($expected, $result);

		// Test with custom separator
		$result = PostalAddress::formatInlineFromComponents(
			countryCode: 'FR',
			city: 'Lyon',
			postcode: '69001',
			road: '1 Place Bellecour',
			separator: ' - '
		);
		$expected = '1 Place Bellecour - 69001 Lyon - France';
		$this->assertSame($expected, $result);

		// Test with minimal data
		$result = PostalAddress::formatInlineFromComponents(countryCode: 'US');
		$expected = 'États-Unis';
		$this->assertSame($expected, $result);

		// Test US address inline
		$result = PostalAddress::formatInlineFromComponents(
			countryCode: 'US',
			city: 'New York',
			postcode: '10001',
			road: '350 Fifth Avenue',
			state: 'NY'
		);
		$expected = '350 Fifth Avenue, New York, NY 10001, États-Unis';
		$this->assertSame($expected, $result);

		// Test with pipe separator
		$result = PostalAddress::formatInlineFromComponents(
			countryCode: 'GB',
			city: 'London',
			postcode: 'SW1A 1AA',
			road: '10 Downing Street',
			separator: ' | '
		);
		$expected = '10 Downing Street | London | SW1A 1AA | Royaume-Uni';
		$this->assertSame($expected, $result);

		// Test with semicolon separator
		$result = PostalAddress::formatInlineFromComponents(
			countryCode: 'DE',
			city: 'Berlin',
			postcode: '10115',
			road: 'Unter den Linden 77',
			separator: '; '
		);
		$expected = 'Unter den Linden 77; 10115 Berlin; Allemagne';
		$this->assertSame($expected, $result);
	}

	// ========== Formatting ==========

	public function testReplaceSpecialChar(): void
	{
		// Arabic comma - sometimes used to separate street from city (e.g., Tunisian addresses)
		$result = PostalAddress::replaceSpecialChar('123،Paris');
		$this->assertSame('123,Paris', $result);

		// Combining acute accent (U+0301) - sometimes used for apostrophe
		// Using hex representation to create the combining accent character
		$input = "L" . hex2bin('CC81') . "exemple";
		$result = PostalAddress::replaceSpecialChar($input);
		$this->assertSame("L'exemple", $result);

		// Numero sign (№) - sometimes used for street number (e.g., Réunion addresses)
		$result = PostalAddress::replaceSpecialChar('№123');
		$this->assertSame('N°123', $result);

		// Multiple special characters in one string
		$input = '123،Rue de l' . hex2bin('CC81') . 'École №5';
		$result = PostalAddress::replaceSpecialChar($input);
		$this->assertSame('123,Rue de l\'École N°5', $result);

		// Null value
		$this->assertNull(PostalAddress::replaceSpecialChar(null));

		// Normal string (should remain unchanged)
		$result = PostalAddress::replaceSpecialChar('Normal Street 123');
		$this->assertSame('Normal Street 123', $result);

		// Empty string
		$result = PostalAddress::replaceSpecialChar('');
		$this->assertSame('', $result);
	}
}