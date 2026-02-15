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
		$this->assertNotNull($result);
		$this->assertIsString($result);
		$this->assertStringContainsString('Paris', $result);
		$this->assertStringContainsString('75001', $result);
		$this->assertStringContainsString('1 Rue de Rivoli', $result);
		$this->assertStringContainsString('John Doe', $result);
		$this->assertStringContainsString('<br/>', $result); // default separator

		// Test with complete address - United States
		$result = PostalAddress::formatFromComponents(
			countryCode: 'US',
			city: 'New York',
			postcode: '10001',
			road: '350 Fifth Avenue',
			state: 'NY',
			attention: 'Jane Smith'
		);
		$this->assertNotNull($result);
		$this->assertStringContainsString('New York', $result);
		$this->assertStringContainsString('10001', $result);
		$this->assertStringContainsString('350 Fifth Avenue', $result);
		$this->assertStringContainsString('NY', $result);
		$this->assertStringContainsString('Jane Smith', $result);

		// Test with minimal data (only country code)
		$result = PostalAddress::formatFromComponents(countryCode: 'FR');
		$this->assertNotNull($result);

		// Test with custom separator
		$result = PostalAddress::formatFromComponents(
			countryCode: 'FR',
			city: 'Lyon',
			postcode: '69001',
			road: '1 Place Bellecour',
			separator: ' | '
		);
		$this->assertNotNull($result);
		$this->assertStringContainsString(' | ', $result);

		// Test with custom locale
		$result = PostalAddress::formatFromComponents(
			countryCode: 'FR',
			city: 'Marseille',
			postcode: '13001',
			road: 'Vieux Port',
			locale: 'fr_FR'
		);
		$this->assertNotNull($result);

		// Test United Kingdom format
		$result = PostalAddress::formatFromComponents(
			countryCode: 'GB',
			city: 'London',
			postcode: 'SW1A 1AA',
			road: '10 Downing Street'
		);
		$this->assertNotNull($result);
		$this->assertStringContainsString('London', $result);
		$this->assertStringContainsString('SW1A 1AA', $result);

		// Test Germany format
		$result = PostalAddress::formatFromComponents(
			countryCode: 'DE',
			city: 'Berlin',
			postcode: '10115',
			road: 'Unter den Linden 77'
		);
		$this->assertNotNull($result);
		$this->assertStringContainsString('Berlin', $result);
		$this->assertStringContainsString('10115', $result);

		// Test without attention
		$result = PostalAddress::formatFromComponents(
			countryCode: 'FR',
			city: 'Nice',
			postcode: '06000',
			road: 'Promenade des Anglais',
			attention: null
		);
		$this->assertNotNull($result);
		$this->assertStringContainsString('Nice', $result);

		// Test with only partial data
		$result = PostalAddress::formatFromComponents(
			countryCode: 'FR',
			city: 'Toulouse',
			postcode: '31000'
		);
		$this->assertNotNull($result);
		$this->assertStringContainsString('Toulouse', $result);
		$this->assertStringContainsString('31000', $result);

		// Test with state (administrative area)
		$result = PostalAddress::formatFromComponents(
			countryCode: 'CA',
			city: 'Toronto',
			postcode: 'M5H 2N2',
			road: '301 Front Street West',
			state: 'ON'
		);
		$this->assertNotNull($result);
		$this->assertStringContainsString('Toronto', $result);
		$this->assertStringContainsString('ON', $result);
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
		$this->assertNotNull($result);
		$this->assertIsString($result);
		$this->assertStringContainsString('Paris', $result);
		$this->assertStringContainsString('75001', $result);
		$this->assertStringContainsString('1 Rue de Rivoli', $result);
		$this->assertStringContainsString(', ', $result); // default inline separator

		// Test with custom separator
		$result = PostalAddress::formatInlineFromComponents(
			countryCode: 'FR',
			city: 'Lyon',
			postcode: '69001',
			road: '1 Place Bellecour',
			separator: ' - '
		);
		$this->assertNotNull($result);
		$this->assertStringContainsString(' - ', $result);

		// Test with minimal data
		$result = PostalAddress::formatInlineFromComponents(countryCode: 'US');
		$this->assertNotNull($result);

		// Test US address inline
		$result = PostalAddress::formatInlineFromComponents(
			countryCode: 'US',
			city: 'New York',
			postcode: '10001',
			road: '350 Fifth Avenue',
			state: 'NY'
		);
		$this->assertNotNull($result);
		$this->assertStringContainsString('New York', $result);
		$this->assertStringContainsString('NY', $result);
		$this->assertStringContainsString('10001', $result);

		// Test with pipe separator
		$result = PostalAddress::formatInlineFromComponents(
			countryCode: 'GB',
			city: 'London',
			postcode: 'SW1A 1AA',
			road: '10 Downing Street',
			separator: ' | '
		);
		$this->assertNotNull($result);
		$this->assertStringContainsString(' | ', $result);

		// Test with semicolon separator
		$result = PostalAddress::formatInlineFromComponents(
			countryCode: 'DE',
			city: 'Berlin',
			postcode: '10115',
			road: 'Unter den Linden 77',
			separator: '; '
		);
		$this->assertNotNull($result);
		$this->assertStringContainsString('; ', $result);
		$this->assertStringContainsString('Berlin', $result);
	}

	// ========== Formatting ==========

	public function testReplaceSpecialChar(): void
	{
		// Arabic comma
		$result = PostalAddress::replaceSpecialChar('123،Paris');
		$this->assertStringContainsString(',', $result);

		// Special apostrophe
		$result = PostalAddress::replaceSpecialChar('Ĺexemple');
		$this->assertStringContainsString("'", $result);

		// Special numero sign
		$result = PostalAddress::replaceSpecialChar('№123');
		$this->assertStringContainsString('N°', $result);

		// Null value
		$this->assertNull(PostalAddress::replaceSpecialChar(null));

		// Normal string
		$input = 'Normal Street 123';
		$result = PostalAddress::replaceSpecialChar($input);
		$this->assertNotNull($result);
		$this->assertIsString($result);
	}
}