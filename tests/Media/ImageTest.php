<?php

declare(strict_types=1);

namespace Tests\Media;

use Osimatic\Media\Image;
use PHPUnit\Framework\TestCase;

final class ImageTest extends TestCase
{
	/* ===================== Constants ===================== */

	public function testJpgConstants(): void
	{
		$this->assertSame('.jpg', Image::JPG_EXTENSION);
		$this->assertIsArray(Image::JPG_EXTENSIONS);
		$this->assertContains('.jpg', Image::JPG_EXTENSIONS);
		$this->assertContains('.jpeg', Image::JPG_EXTENSIONS);
		$this->assertIsArray(Image::JPG_MIME_TYPES);
		$this->assertContains('image/jpeg', Image::JPG_MIME_TYPES);
	}

	public function testPngConstants(): void
	{
		$this->assertSame('.png', Image::PNG_EXTENSION);
		$this->assertIsArray(Image::PNG_MIME_TYPES);
		$this->assertContains('image/png', Image::PNG_MIME_TYPES);
	}

	public function testGifConstants(): void
	{
		$this->assertSame('.gif', Image::GIF_EXTENSION);
		$this->assertIsArray(Image::GIF_MIME_TYPES);
		$this->assertContains('image/gif', Image::GIF_MIME_TYPES);
	}

	public function testSvgConstants(): void
	{
		$this->assertSame('.svg', Image::SVG_EXTENSION);
		$this->assertIsArray(Image::SVG_MIME_TYPES);
		$this->assertContains('image/svg+xml', Image::SVG_MIME_TYPES);
	}

	public function testBmpConstants(): void
	{
		$this->assertSame('.bmp', Image::BMP_EXTENSION);
		$this->assertIsArray(Image::BMP_MIME_TYPES);
		$this->assertContains('image/bmp', Image::BMP_MIME_TYPES);
	}

	public function testWebpConstants(): void
	{
		$this->assertSame('.webp', Image::WEBP_EXTENSION);
		$this->assertIsArray(Image::WEBP_MIME_TYPES);
		$this->assertContains('image/webp', Image::WEBP_MIME_TYPES);
	}

	public function testTiffConstants(): void
	{
		$this->assertSame('.tiff', Image::TIFF_EXTENSION);
		$this->assertIsArray(Image::TIFF_EXTENSIONS);
		$this->assertContains('.tiff', Image::TIFF_EXTENSIONS);
		$this->assertContains('.tif', Image::TIFF_EXTENSIONS);
		$this->assertIsArray(Image::TIFF_MIME_TYPES);
		$this->assertContains('image/tiff', Image::TIFF_MIME_TYPES);
	}

	/* ===================== getExtensionsAndMimeTypes() ===================== */

	public function testGetExtensionsAndMimeTypes(): void
	{
		$result = Image::getExtensionsAndMimeTypes();
		$this->assertIsArray($result);
		$this->assertArrayHasKey('jpg', $result);
		$this->assertArrayHasKey('png', $result);
		$this->assertArrayHasKey('gif', $result);
		$this->assertArrayHasKey('svg', $result);
		$this->assertArrayHasKey('bmp', $result);
		$this->assertArrayHasKey('webp', $result);
		$this->assertArrayHasKey('tiff', $result);
	}

	public function testGetExtensionsAndMimeTypesJpgStructure(): void
	{
		$result = Image::getExtensionsAndMimeTypes();
		$this->assertCount(2, $result['jpg']);
		$this->assertIsArray($result['jpg'][0]); // Extensions
		$this->assertIsArray($result['jpg'][1]); // Mime types
	}

	/* ===================== getMimeTypeFromExtension() ===================== */

	public function testGetMimeTypeFromExtensionWithJpg(): void
	{
		$this->assertSame('image/jpeg', Image::getMimeTypeFromExtension('.jpg'));
		$this->assertSame('image/jpeg', Image::getMimeTypeFromExtension('.jpeg'));
	}

	public function testGetMimeTypeFromExtensionWithPng(): void
	{
		$this->assertSame('image/png', Image::getMimeTypeFromExtension('.png'));
	}

	public function testGetMimeTypeFromExtensionWithGif(): void
	{
		$this->assertSame('image/gif', Image::getMimeTypeFromExtension('.gif'));
	}

	public function testGetMimeTypeFromExtensionWithSvg(): void
	{
		$this->assertSame('image/svg+xml', Image::getMimeTypeFromExtension('.svg'));
	}

	public function testGetMimeTypeFromExtensionWithBmp(): void
	{
		$this->assertSame('image/bmp', Image::getMimeTypeFromExtension('.bmp'));
	}

	public function testGetMimeTypeFromExtensionWithWebp(): void
	{
		$this->assertSame('image/webp', Image::getMimeTypeFromExtension('.webp'));
	}

	public function testGetMimeTypeFromExtensionWithTiff(): void
	{
		$this->assertSame('image/tiff', Image::getMimeTypeFromExtension('.tiff'));
		$this->assertSame('image/tiff', Image::getMimeTypeFromExtension('.tif'));
	}

	public function testGetMimeTypeFromExtensionWithInvalidExtension(): void
	{
		$this->assertNull(Image::getMimeTypeFromExtension('.invalid'));
		$this->assertNull(Image::getMimeTypeFromExtension('.txt'));
	}

	/* ===================== getExtensionFromMimeType() ===================== */

	public function testGetExtensionFromMimeTypeWithJpeg(): void
	{
		$this->assertSame('jpg', Image::getExtensionFromMimeType('image/jpeg'));
	}

	public function testGetExtensionFromMimeTypeWithPng(): void
	{
		$this->assertSame('png', Image::getExtensionFromMimeType('image/png'));
	}

	public function testGetExtensionFromMimeTypeWithGif(): void
	{
		$this->assertSame('gif', Image::getExtensionFromMimeType('image/gif'));
	}

	public function testGetExtensionFromMimeTypeWithSvg(): void
	{
		$this->assertSame('svg', Image::getExtensionFromMimeType('image/svg+xml'));
	}

	public function testGetExtensionFromMimeTypeWithBmp(): void
	{
		$this->assertSame('bmp', Image::getExtensionFromMimeType('image/bmp'));
	}

	public function testGetExtensionFromMimeTypeWithWebp(): void
	{
		$this->assertSame('webp', Image::getExtensionFromMimeType('image/webp'));
	}

	public function testGetExtensionFromMimeTypeWithTiff(): void
	{
		$this->assertSame('tiff', Image::getExtensionFromMimeType('image/tiff'));
	}

	public function testGetExtensionFromMimeTypeWithInvalidMimeType(): void
	{
		$this->assertNull(Image::getExtensionFromMimeType('invalid/mime'));
		$this->assertNull(Image::getExtensionFromMimeType('text/plain'));
	}

	/* ===================== getWidth() ===================== */

	public function testGetWidthWithNonExistentFile(): void
	{
		$result = Image::getWidth('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getHeight() ===================== */

	public function testGetHeightWithNonExistentFile(): void
	{
		$result = Image::getHeight('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getMimeType() ===================== */

	public function testGetMimeTypeWithNonExistentFile(): void
	{
		$result = Image::getMimeType('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getEtag() ===================== */

	public function testGetEtagWithNonExistentFile(): void
	{
		$result = Image::getEtag('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getLastModifiedString() ===================== */

	public function testGetLastModifiedStringWithNonExistentFile(): void
	{
		$result = Image::getLastModifiedString('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getPhotoTimestamp() ===================== */

	public function testGetPhotoTimestampWithNonExistentFile(): void
	{
		$result = Image::getPhotoTimestamp('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getCameraMake() ===================== */

	public function testGetCameraMakeWithNonExistentFile(): void
	{
		$result = Image::getCameraMake('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getCameraModel() ===================== */

	public function testGetCameraModelWithNonExistentFile(): void
	{
		$result = Image::getCameraModel('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== readExifData() ===================== */

	public function testReadExifDataWithNonExistentFile(): void
	{
		$result = Image::readExifData('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getImageInfo() ===================== */

	public function testGetImageInfoWithNonExistentFile(): void
	{
		$result = Image::getImageInfo('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getPhotoInfo() ===================== */

	public function testGetPhotoInfoWithNonExistentFile(): void
	{
		$result = Image::getPhotoInfo('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getIso() ===================== */

	public function testGetIsoWithNonExistentFile(): void
	{
		$result = Image::getIso('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getAperture() ===================== */

	public function testGetApertureWithNonExistentFile(): void
	{
		$result = Image::getAperture('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getShutterSpeed() ===================== */

	public function testGetShutterSpeedWithNonExistentFile(): void
	{
		$result = Image::getShutterSpeed('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getFocalLength() ===================== */

	public function testGetFocalLengthWithNonExistentFile(): void
	{
		$result = Image::getFocalLength('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== getGpsCoordinates() ===================== */

	public function testGetGpsCoordinatesWithNonExistentFile(): void
	{
		$result = Image::getGpsCoordinates('/non/existent/file.jpg');
		$this->assertNull($result);
	}

	/* ===================== checkFile() ===================== */

	public function testCheckFileWithNonExistentFile(): void
	{
		$result = Image::checkFile('/non/existent/file.jpg', 'test.jpg');
		$this->assertFalse($result);
	}

	/* ===================== checkJpgFile() ===================== */

	public function testCheckJpgFileWithNonExistentFile(): void
	{
		$result = Image::checkJpgFile('/non/existent/file.jpg', 'test.jpg');
		$this->assertFalse($result);
	}

	/* ===================== checkPngFile() ===================== */

	public function testCheckPngFileWithNonExistentFile(): void
	{
		$result = Image::checkPngFile('/non/existent/file.png', 'test.png');
		$this->assertFalse($result);
	}

	/* ===================== checkGifFile() ===================== */

	public function testCheckGifFileWithNonExistentFile(): void
	{
		$result = Image::checkGifFile('/non/existent/file.gif', 'test.gif');
		$this->assertFalse($result);
	}

	/* ===================== getWidth/Height/MimeType with real image ===================== */

	public function testGetWidthWithRealImage(): void
	{
		// Create a minimal 1x1 PNG image
		$tempFile = $this->createTempPngImage();

		$width = Image::getWidth($tempFile);
		$this->assertSame(1, $width);

		unlink($tempFile);
	}

	public function testGetHeightWithRealImage(): void
	{
		// Create a minimal 1x1 PNG image
		$tempFile = $this->createTempPngImage();

		$height = Image::getHeight($tempFile);
		$this->assertSame(1, $height);

		unlink($tempFile);
	}

	public function testGetMimeTypeWithRealImage(): void
	{
		// Create a minimal 1x1 PNG image
		$tempFile = $this->createTempPngImage();

		$mimeType = Image::getMimeType($tempFile);
		$this->assertSame('image/png', $mimeType);

		unlink($tempFile);
	}

	public function testGetImageInfoWithRealImage(): void
	{
		// Create a minimal 1x1 PNG image
		$tempFile = $this->createTempPngImage();

		$info = Image::getImageInfo($tempFile);
		$this->assertIsArray($info);
		$this->assertArrayHasKey('width', $info);
		$this->assertArrayHasKey('height', $info);
		$this->assertArrayHasKey('mime', $info);
		$this->assertSame(1, $info['width']);
		$this->assertSame(1, $info['height']);
		$this->assertSame('image/png', $info['mime']);

		unlink($tempFile);
	}

	public function testGetEtagWithRealImage(): void
	{
		// Create a minimal 1x1 PNG image
		$tempFile = $this->createTempPngImage();

		$etag = Image::getEtag($tempFile);
		$this->assertIsString($etag);
		$this->assertNotEmpty($etag);
		$this->assertSame(32, strlen($etag)); // MD5 hash is 32 characters

		unlink($tempFile);
	}

	public function testGetLastModifiedStringWithRealImage(): void
	{
		// Create a minimal 1x1 PNG image
		$tempFile = $this->createTempPngImage();

		$lastModified = Image::getLastModifiedString($tempFile);
		$this->assertIsString($lastModified);
		$this->assertStringContainsString('GMT', $lastModified);

		unlink($tempFile);
	}

	/* ===================== Helper Methods ===================== */

	/**
	 * Create a minimal valid 1x1 PNG image for testing.
	 * @return string Path to the temporary PNG file
	 */
	private function createTempPngImage(): string
	{
		$tempFile = sys_get_temp_dir() . '/test_' . uniqid() . '.png';

		// Minimal valid 1x1 transparent PNG
		$pngContent = base64_decode(
			'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
		);

		file_put_contents($tempFile, $pngContent);

		return $tempFile;
	}
}