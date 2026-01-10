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
		$this->assertTrue(Book::checkIsbn10('2-2070-3641-5'));
		$this->assertTrue(Book::checkIsbn10('2207036415'));
		$this->assertTrue(Book::checkIsbn10('0-306-40615-2'));
		$this->assertTrue(Book::checkIsbn10('0306406152'));
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
		$this->assertTrue(Book::checkIsbn13('978-2-207-03641-2'));
		$this->assertTrue(Book::checkIsbn13('9782207036412'));
		$this->assertTrue(Book::checkIsbn13('978-0-306-40615-7'));
		$this->assertTrue(Book::checkIsbn13('9780306406157'));
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
		$this->assertTrue(Book::checkIssn('0028-084X'));
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
}