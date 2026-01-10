<?php

declare(strict_types=1);

namespace Tests\Text;

use Osimatic\Text\CSV;
use PHPUnit\Framework\TestCase;

final class CSVTest extends TestCase
{
	/* ===================== Constants ===================== */

	public function testFileExtensionConstant(): void
	{
		$this->assertSame('.csv', CSV::FILE_EXTENSION);
	}

	public function testMimeTypesConstant(): void
	{
		$this->assertIsArray(CSV::MIME_TYPES);
		$this->assertContains('text/csv', CSV::MIME_TYPES);
		$this->assertContains('application/vnd.ms-excel', CSV::MIME_TYPES);
		$this->assertNotEmpty(CSV::MIME_TYPES);
	}

	/* ===================== checkFile() ===================== */

	public function testCheckFileMethod(): void
	{
		// Test que la méthode existe
		$this->assertTrue(method_exists(CSV::class, 'checkFile'));
	}

	/* ===================== convertToArray() ===================== */

	public function testConvertToArrayWithNonExistentFile(): void
	{
		$result = CSV::convertToArray('/path/to/nonexistent/file.csv');
		$this->assertNull($result);
	}

	public function testConvertToArrayWithValidFile(): void
	{
		// Créer un fichier CSV temporaire pour le test
		$tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
		$csvContent = "Name,Age,City\nJohn,25,Paris\nJane,30,London\n";
		file_put_contents($tempFile, $csvContent);

		$result = CSV::convertToArray($tempFile);

		$this->assertIsArray($result);
		$this->assertCount(2, $result);
		$this->assertSame('John', $result[0]['Name']);
		$this->assertSame('25', $result[0]['Age']);
		$this->assertSame('Paris', $result[0]['City']);
		$this->assertSame('Jane', $result[1]['Name']);
		$this->assertSame('30', $result[1]['Age']);
		$this->assertSame('London', $result[1]['City']);

		// Nettoyer
		unlink($tempFile);
	}

	public function testConvertToArrayWithCustomDelimiter(): void
	{
		// Créer un fichier CSV temporaire avec séparateur point-virgule
		$tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
		$csvContent = "Name;Age;City\nJohn;25;Paris\nJane;30;London\n";
		file_put_contents($tempFile, $csvContent);

		$result = CSV::convertToArray($tempFile, ';');

		$this->assertIsArray($result);
		$this->assertCount(2, $result);
		$this->assertSame('John', $result[0]['Name']);
		$this->assertSame('25', $result[0]['Age']);

		// Nettoyer
		unlink($tempFile);
	}

	public function testConvertToArrayWithEmptyFile(): void
	{
		// Créer un fichier CSV vide
		$tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
		file_put_contents($tempFile, '');

		$result = CSV::convertToArray($tempFile);

		$this->assertIsArray($result);
		$this->assertEmpty($result);

		// Nettoyer
		unlink($tempFile);
	}

	/* ===================== parseSeparator() ===================== */

	public function testParseSeparatorWithSemicolon(): void
	{
		$this->assertSame(';', CSV::parseSeparator(';'));
	}

	public function testParseSeparatorWithComma(): void
	{
		$this->assertSame(',', CSV::parseSeparator(','));
	}

	public function testParseSeparatorWithNull(): void
	{
		$this->assertSame(',', CSV::parseSeparator(null));
	}

	public function testParseSeparatorWithOtherValue(): void
	{
		$this->assertSame(',', CSV::parseSeparator('|'));
		$this->assertSame(',', CSV::parseSeparator('tab'));
	}

	/* ===================== forceStringForExcel() ===================== */

	public function testForceStringForExcelWithString(): void
	{
		$this->assertSame('="test"', CSV::forceStringForExcel('test'));
		$this->assertSame('="hello world"', CSV::forceStringForExcel('hello world'));
	}

	public function testForceStringForExcelWithInteger(): void
	{
		$this->assertSame('="123"', CSV::forceStringForExcel(123));
		$this->assertSame('="0"', CSV::forceStringForExcel(0));
	}

	public function testForceStringForExcelWithFloat(): void
	{
		$this->assertSame('="123.45"', CSV::forceStringForExcel(123.45));
		$this->assertSame('="0"', CSV::forceStringForExcel(0.0)); // PHP convertit 0.0 en "0"
	}

	public function testForceStringForExcelWithNull(): void
	{
		$this->assertSame('', CSV::forceStringForExcel(null));
	}

	public function testForceStringForExcelWithEmptyString(): void
	{
		$this->assertSame('', CSV::forceStringForExcel(''));
	}

	public function testForceStringForExcelWithZeroString(): void
	{
		$this->assertSame('="0"', CSV::forceStringForExcel('0'));
	}

	/* ===================== getHttpResponse() ===================== */

	public function testGetHttpResponseMethod(): void
	{
		// Test que la méthode existe
		$this->assertTrue(method_exists(CSV::class, 'getHttpResponse'));
	}
}