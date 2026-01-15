<?php

declare(strict_types=1);

namespace Tests\Media;

use Osimatic\Media\Book;
use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
	/* ===================== checkIsbn() ===================== */

	public function testCheckIsbnWithValidIsbn10(): void
	{
		$this->assertTrue(Book::checkIsbn('0-306-40615-2'));
		$this->assertTrue(Book::checkIsbn('0306406152'));
		$this->assertTrue(Book::checkIsbn('043942089X'));
	}

	public function testCheckIsbnWithValidIsbn13(): void
	{
		$this->assertTrue(Book::checkIsbn('978-0-306-40615-7'));
		$this->assertTrue(Book::checkIsbn('9780306406157'));
		$this->assertTrue(Book::checkIsbn('978-3-16-148410-0'));
	}

	public function testCheckIsbnWithInvalidIsbn(): void
	{
		$this->assertFalse(Book::checkIsbn('invalid'));
		$this->assertFalse(Book::checkIsbn('123'));
		$this->assertFalse(Book::checkIsbn('0000000000'));
		$this->assertFalse(Book::checkIsbn('9780000000000'));
	}

	public function testCheckIsbnWithEmptyString(): void
	{
		$this->assertFalse(Book::checkIsbn(''));
	}

	/* ===================== checkIsbn10() ===================== */

	public function testCheckIsbn10WithValidIsbn10(): void
	{
		$this->assertTrue(Book::checkIsbn10('0-306-40615-2'));
		$this->assertTrue(Book::checkIsbn10('0306406152'));
		$this->assertTrue(Book::checkIsbn10('0-684-84328-5'));
		$this->assertTrue(Book::checkIsbn10('0684843285'));
	}

	public function testCheckIsbn10WithValidIsbn10WithX(): void
	{
		// ISBN-10 can have 'X' as the last check digit
		$this->assertTrue(Book::checkIsbn10('043942089X'));
	}

	public function testCheckIsbn10WithInvalidIsbn10(): void
	{
		$this->assertFalse(Book::checkIsbn10('invalid'));
		$this->assertFalse(Book::checkIsbn10('123'));
		$this->assertFalse(Book::checkIsbn10('0000000000'));
	}

	public function testCheckIsbn10WithIsbn13(): void
	{
		// Should reject ISBN-13 when checking for ISBN-10
		$this->assertFalse(Book::checkIsbn10('978-2-207-03641-2'));
		$this->assertFalse(Book::checkIsbn10('9782207036412'));
	}

	/* ===================== checkIsbn13() ===================== */

	public function testCheckIsbn13WithValidIsbn13(): void
	{
		$this->assertTrue(Book::checkIsbn13('978-0-306-40615-7'));
		$this->assertTrue(Book::checkIsbn13('9780306406157'));
		$this->assertTrue(Book::checkIsbn13('978-3-16-148410-0'));
		$this->assertTrue(Book::checkIsbn13('9783161484100'));
	}

	public function testCheckIsbn13WithInvalidIsbn13(): void
	{
		$this->assertFalse(Book::checkIsbn13('invalid'));
		$this->assertFalse(Book::checkIsbn13('123'));
		$this->assertFalse(Book::checkIsbn13('9780000000000'));
	}

	public function testCheckIsbn13WithIsbn10(): void
	{
		// Should reject ISBN-10 when checking for ISBN-13
		$this->assertFalse(Book::checkIsbn13('2-2070-3641-5'));
		$this->assertFalse(Book::checkIsbn13('2207036415'));
	}

	/* ===================== checkIssn() ===================== */

	public function testCheckIssnWithValidIssn(): void
	{
		$this->assertTrue(Book::checkIssn('0378-5955'));
		$this->assertTrue(Book::checkIssn('03785955'));
		$this->assertTrue(Book::checkIssn('0317-8471'));
		$this->assertTrue(Book::checkIssn('03178471'));
	}

	public function testCheckIssnWithValidIssnWithX(): void
	{
		// ISSN can have 'X' as the last check digit
		$this->assertTrue(Book::checkIssn('0003-004X'));
	}

	public function testCheckIssnWithInvalidIssn(): void
	{
		$this->assertFalse(Book::checkIssn('invalid'));
		$this->assertFalse(Book::checkIssn('123'));
		$this->assertFalse(Book::checkIssn('00000000'));
		$this->assertFalse(Book::checkIssn('0000-0000'));
	}

	public function testCheckIssnWithEmptyString(): void
	{
		$this->assertFalse(Book::checkIssn(''));
	}

	/* ===================== normalizeIsbn() ===================== */

	public function testNormalizeIsbnRemovesHyphens(): void
	{
		$this->assertSame('0306406152', Book::normalizeIsbn('0-306-40615-2'));
	}

	public function testNormalizeIsbnRemovesSpaces(): void
	{
		$this->assertSame('0306406152', Book::normalizeIsbn('0306 40615 2'));
	}

	public function testNormalizeIsbnConvertsToUppercase(): void
	{
		$this->assertSame('043942089X', Book::normalizeIsbn('043942089x'));
	}

	public function testNormalizeIsbnTrimsWhitespace(): void
	{
		$this->assertSame('0306406152', Book::normalizeIsbn('  0-306-40615-2  '));
	}

	/* ===================== convertIsbn10ToIsbn13() ===================== */

	public function testConvertIsbn10ToIsbn13WithValidIsbn10(): void
	{
		$this->assertSame('9780306406157', Book::convertIsbn10ToIsbn13('0-306-40615-2'));
		$this->assertSame('9780306406157', Book::convertIsbn10ToIsbn13('0306406152'));
	}

	public function testConvertIsbn10ToIsbn13WithInvalidIsbn10(): void
	{
		$this->assertNull(Book::convertIsbn10ToIsbn13('invalid'));
		$this->assertNull(Book::convertIsbn10ToIsbn13('123'));
	}

	public function testConvertIsbn10ToIsbn13WithIsbn13(): void
	{
		// Should return null for ISBN-13 input
		$this->assertNull(Book::convertIsbn10ToIsbn13('978-0-306-40615-7'));
	}

	/* ===================== Invalid Pattern Detection ===================== */

	public function testCheckIsbnRejectsAllIdenticalDigits(): void
	{
		$this->assertFalse(Book::checkIsbn('0000000000'));
		$this->assertFalse(Book::checkIsbn('1111111111'));
		$this->assertFalse(Book::checkIsbn('9999999999999'));
	}

	public function testCheckIsbnRejectsSequentialDigits(): void
	{
		$this->assertFalse(Book::checkIsbn('0123456789'));
		$this->assertFalse(Book::checkIsbn('9876543210'));
		$this->assertFalse(Book::checkIsbn('1234567890123'));
	}

	/* ===================== getIsbnPrefix() ===================== */

	public function testGetIsbnPrefixWithIsbn13(): void
	{
		$this->assertSame('978', Book::getIsbnPrefix('978-0-306-40615-7'));
		$this->assertSame('979', Book::getIsbnPrefix('979-10-90636-07-1'));
	}

	public function testGetIsbnPrefixWithIsbn10(): void
	{
		$this->assertNull(Book::getIsbnPrefix('0-306-40615-2'));
	}

	/* ===================== getIsbnRegistrationGroup() ===================== */

	public function testGetIsbnRegistrationGroupWithIsbn13(): void
	{
		$this->assertSame('0', Book::getIsbnRegistrationGroup('978-0-306-40615-7'));
		$this->assertSame('1', Book::getIsbnRegistrationGroup('978-1-234-56789-0'));
		$this->assertSame('2', Book::getIsbnRegistrationGroup('978-2-207-03641-2'));
		$this->assertSame('3', Book::getIsbnRegistrationGroup('978-3-16-148410-0'));
	}

	public function testGetIsbnRegistrationGroupWithIsbn10(): void
	{
		$this->assertNull(Book::getIsbnRegistrationGroup('0-306-40615-2'));
	}

	/* ===================== getIsbnCheckDigit() ===================== */

	public function testGetIsbnCheckDigitWithIsbn10(): void
	{
		$this->assertSame('2', Book::getIsbnCheckDigit('0-306-40615-2'));
		$this->assertSame('X', Book::getIsbnCheckDigit('043942089X'));
	}

	public function testGetIsbnCheckDigitWithIsbn13(): void
	{
		$this->assertSame('7', Book::getIsbnCheckDigit('978-0-306-40615-7'));
		$this->assertSame('0', Book::getIsbnCheckDigit('978-3-16-148410-0'));
	}

	public function testGetIsbnCheckDigitWithInvalidLength(): void
	{
		$this->assertNull(Book::getIsbnCheckDigit('123'));
	}

	/* ===================== getIsbnInfo() ===================== */

	public function testGetIsbnInfoWithValidIsbn10(): void
	{
		$info = Book::getIsbnInfo('0-306-40615-2');
		$this->assertSame('isbn10', $info['type']);
		$this->assertNull($info['prefix']);
		$this->assertNull($info['registration_group']);
		$this->assertSame('2', $info['check_digit']);
		$this->assertTrue($info['is_valid']);
	}

	public function testGetIsbnInfoWithValidIsbn13(): void
	{
		$info = Book::getIsbnInfo('978-0-306-40615-7');
		$this->assertSame('isbn13', $info['type']);
		$this->assertSame('978', $info['prefix']);
		$this->assertSame('0', $info['registration_group']);
		$this->assertSame('7', $info['check_digit']);
		$this->assertTrue($info['is_valid']);
	}

	public function testGetIsbnInfoWithInvalidIsbn(): void
	{
		$info = Book::getIsbnInfo('invalid');
		$this->assertNull($info['type']);
		$this->assertNull($info['prefix']);
		$this->assertNull($info['registration_group']);
		$this->assertNull($info['check_digit']);
		$this->assertFalse($info['is_valid']);
	}

	/* ===================== getRegistrationGroupName() ===================== */

	public function testGetRegistrationGroupNameWithCommonGroups(): void
	{
		$this->assertSame('English language', Book::getRegistrationGroupName('0'));
		$this->assertSame('English language', Book::getRegistrationGroupName('1'));
		$this->assertSame('French language', Book::getRegistrationGroupName('2'));
		$this->assertSame('German language', Book::getRegistrationGroupName('3'));
		$this->assertSame('Japan', Book::getRegistrationGroupName('4'));
		$this->assertSame('Russian Federation', Book::getRegistrationGroupName('5'));
		$this->assertSame('China', Book::getRegistrationGroupName('7'));
	}

	public function testGetRegistrationGroupNameWithTwoDigitGroups(): void
	{
		$this->assertSame('Spain', Book::getRegistrationGroupName('84'));
		$this->assertSame('Italy', Book::getRegistrationGroupName('88'));
		$this->assertSame('Netherlands', Book::getRegistrationGroupName('90'));
	}

	public function testGetRegistrationGroupNameWithThreeDigitGroups(): void
	{
		$this->assertSame('Finland', Book::getRegistrationGroupName('951'));
		$this->assertSame('Greece', Book::getRegistrationGroupName('960'));
		$this->assertSame('Mexico', Book::getRegistrationGroupName('970'));
	}

	public function testGetRegistrationGroupNameWithUnknownGroup(): void
	{
		$this->assertNull(Book::getRegistrationGroupName('999'));
		$this->assertNull(Book::getRegistrationGroupName('unknown'));
	}
}