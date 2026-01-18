<?php

declare(strict_types=1);

namespace Tests\Text;

use Osimatic\Text\PDF;
use PHPUnit\Framework\TestCase;

final class PDFTest extends TestCase
{
	/* ===================== Constants ===================== */

	public function testFileExtensionConstant(): void
	{
		$this->assertSame('.pdf', PDF::FILE_EXTENSION);
	}

	public function testMimeTypesConstant(): void
	{
		$this->assertIsArray(PDF::MIME_TYPES);
		$this->assertContains('application/pdf', PDF::MIME_TYPES);
		$this->assertContains('application/x-pdf', PDF::MIME_TYPES);
		$this->assertContains('application/vnd.cups-pdf', PDF::MIME_TYPES);
		$this->assertContains('application/vnd.sealedmedia.softseal.pdf', PDF::MIME_TYPES);
	}

	/* ===================== checkFile() ===================== */

	public function testCheckFileValidPdf(): void
	{
		// Note: Ce test nécessiterait un vrai fichier PDF pour être complet
		// Pour l'instant, on teste juste que la méthode existe et accepte les bons paramètres
		$this->assertTrue(method_exists(PDF::class, 'checkFile'));
	}

	/* ===================== getHttpResponse() ===================== */

	public function testGetHttpResponse(): void
	{
		// Test que la méthode existe
		$this->assertTrue(method_exists(PDF::class, 'getHttpResponse'));
	}

	/* ===================== getFileSize() ===================== */

	public function testGetFileSizeWithNonExistentFile(): void
	{
		$result = PDF::getFileSize('/path/to/nonexistent.pdf');
		$this->assertNull($result);
	}

	public function testGetFileSizeWithExistingFile(): void
	{
		// Create a temporary PDF file
		$tempFile = $this->createTempPdfFile();

		$result = PDF::getFileSize($tempFile);

		$this->assertIsInt($result);
		$this->assertGreaterThan(0, $result);

		unlink($tempFile);
	}

	/* ===================== getPageCount() ===================== */

	public function testGetPageCountWithNonExistentFile(): void
	{
		$result = PDF::getPageCount('/path/to/nonexistent.pdf');
		$this->assertNull($result);
	}

	public function testGetPageCountWithValidPdf(): void
	{
		// Create a temporary PDF file
		$tempFile = $this->createTempPdfFile();

		$result = PDF::getPageCount($tempFile);

		$this->assertIsInt($result);
		$this->assertGreaterThan(0, $result);

		unlink($tempFile);
	}

	/* ===================== getMetadata() ===================== */

	public function testGetMetadataWithNonExistentFile(): void
	{
		$result = PDF::getMetadata('/path/to/nonexistent.pdf');
		$this->assertNull($result);
	}

	public function testGetMetadataWithValidPdf(): void
	{
		// Create a temporary PDF file
		$tempFile = $this->createTempPdfFile();

		$result = PDF::getMetadata($tempFile);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('version', $result);
		$this->assertArrayHasKey('page_count', $result);
		$this->assertArrayHasKey('file_size', $result);

		unlink($tempFile);
	}

	/* ===================== Helper Methods ===================== */

	/**
	 * Create a minimal valid PDF file for testing.
	 * @return string Path to the temporary PDF file
	 */
	private function createTempPdfFile(): string
	{
		$tempFile = sys_get_temp_dir() . '/test_' . uniqid() . '.pdf';

		// Minimal valid PDF content with metadata
		$pdfContent = <<<'PDF'
%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
>>
endobj
4 0 obj
<<
/Length 44
>>
stream
BT
/F1 12 Tf
100 700 Td
(Test PDF) Tj
ET
endstream
endobj
5 0 obj
<<
/Title (Test Document)
/Author (Test Author)
/Creator (Test Creator)
/Producer (Test Producer)
/CreationDate (D:20240101120000)
/Subject (Test Subject)
>>
endobj
xref
0 6
0000000000 65535 f
0000000009 00000 n
0000000058 00000 n
0000000115 00000 n
0000000214 00000 n
0000000307 00000 n
trailer
<<
/Size 6
/Root 1 0 R
/Info 5 0 R
>>
startxref
492
%%EOF
PDF;

		file_put_contents($tempFile, $pdfContent);

		return $tempFile;
	}
}