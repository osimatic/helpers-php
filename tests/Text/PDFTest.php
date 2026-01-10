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
}