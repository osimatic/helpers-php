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
}