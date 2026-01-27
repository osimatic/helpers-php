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

	/* ===================== Tests with Real Image Fixtures ===================== */

	/* ----- JPG Tests ----- */

	public function testGetWidthWithRealJpg(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$width = Image::getWidth($jpgFile);
		$this->assertSame(100, $width);
	}

	public function testGetHeightWithRealJpg(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$height = Image::getHeight($jpgFile);
		$this->assertSame(50, $height);
	}

	public function testGetMimeTypeWithRealJpg(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$mimeType = Image::getMimeType($jpgFile);
		$this->assertSame('image/jpeg', $mimeType);
	}

	public function testGetImageInfoWithRealJpg(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$info = Image::getImageInfo($jpgFile);
		$this->assertIsArray($info);
		$this->assertSame(100, $info['width']);
		$this->assertSame(50, $info['height']);
		$this->assertSame('image/jpeg', $info['mime']);
	}

	public function testCheckJpgFileWithRealJpg(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$result = Image::checkJpgFile($jpgFile, 'test.jpg');
		$this->assertTrue($result);
	}

	public function testCheckFileWithRealJpg(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$result = Image::checkFile($jpgFile, 'test.jpg');
		$this->assertTrue($result);
	}

	/* ----- PNG Tests ----- */

	public function testGetWidthWithRealPng(): void
	{
		$pngFile = __DIR__ . '/../fixtures/images/test_200x100.png';
		$this->assertFileExists($pngFile);

		$width = Image::getWidth($pngFile);
		$this->assertSame(200, $width);
	}

	public function testGetHeightWithRealPng(): void
	{
		$pngFile = __DIR__ . '/../fixtures/images/test_200x100.png';
		$this->assertFileExists($pngFile);

		$height = Image::getHeight($pngFile);
		$this->assertSame(100, $height);
	}

	public function testGetMimeTypeWithRealPng(): void
	{
		$pngFile = __DIR__ . '/../fixtures/images/test_200x100.png';
		$this->assertFileExists($pngFile);

		$mimeType = Image::getMimeType($pngFile);
		$this->assertSame('image/png', $mimeType);
	}

	public function testGetImageInfoWithRealPng(): void
	{
		$pngFile = __DIR__ . '/../fixtures/images/test_200x100.png';
		$this->assertFileExists($pngFile);

		$info = Image::getImageInfo($pngFile);
		$this->assertIsArray($info);
		$this->assertSame(200, $info['width']);
		$this->assertSame(100, $info['height']);
		$this->assertSame('image/png', $info['mime']);
	}

	public function testCheckPngFileWithRealPng(): void
	{
		$pngFile = __DIR__ . '/../fixtures/images/test_200x100.png';
		$this->assertFileExists($pngFile);

		$result = Image::checkPngFile($pngFile, 'test.png');
		$this->assertTrue($result);
	}

	public function testCheckFileWithRealPng(): void
	{
		$pngFile = __DIR__ . '/../fixtures/images/test_200x100.png';
		$this->assertFileExists($pngFile);

		$result = Image::checkFile($pngFile, 'test.png');
		$this->assertTrue($result);
	}

	/* ----- GIF Tests ----- */

	public function testGetWidthWithRealGif(): void
	{
		$gifFile = __DIR__ . '/../fixtures/images/test_150x75.gif';
		$this->assertFileExists($gifFile);

		$width = Image::getWidth($gifFile);
		$this->assertSame(150, $width);
	}

	public function testGetHeightWithRealGif(): void
	{
		$gifFile = __DIR__ . '/../fixtures/images/test_150x75.gif';
		$this->assertFileExists($gifFile);

		$height = Image::getHeight($gifFile);
		$this->assertSame(75, $height);
	}

	public function testGetMimeTypeWithRealGif(): void
	{
		$gifFile = __DIR__ . '/../fixtures/images/test_150x75.gif';
		$this->assertFileExists($gifFile);

		$mimeType = Image::getMimeType($gifFile);
		$this->assertSame('image/gif', $mimeType);
	}

	public function testGetImageInfoWithRealGif(): void
	{
		$gifFile = __DIR__ . '/../fixtures/images/test_150x75.gif';
		$this->assertFileExists($gifFile);

		$info = Image::getImageInfo($gifFile);
		$this->assertIsArray($info);
		$this->assertSame(150, $info['width']);
		$this->assertSame(75, $info['height']);
		$this->assertSame('image/gif', $info['mime']);
	}

	public function testCheckGifFileWithRealGif(): void
	{
		$gifFile = __DIR__ . '/../fixtures/images/test_150x75.gif';
		$this->assertFileExists($gifFile);

		$result = Image::checkGifFile($gifFile, 'test.gif');
		$this->assertTrue($result);
	}

	public function testCheckFileWithRealGif(): void
	{
		$gifFile = __DIR__ . '/../fixtures/images/test_150x75.gif';
		$this->assertFileExists($gifFile);

		$result = Image::checkFile($gifFile, 'test.gif');
		$this->assertTrue($result);
	}

	/* ----- SVG Tests ----- */

	public function testGetWidthWithRealSvg(): void
	{
		$svgFile = __DIR__ . '/../fixtures/images/test_icon.svg';
		$this->assertFileExists($svgFile);

		// Note: getimagesize() doesn't support SVG files in standard PHP
		$width = Image::getWidth($svgFile);
		$this->assertNull($width); // SVG returns null as getimagesize() doesn't support it
	}

	public function testGetHeightWithRealSvg(): void
	{
		$svgFile = __DIR__ . '/../fixtures/images/test_icon.svg';
		$this->assertFileExists($svgFile);

		// Note: getimagesize() doesn't support SVG files in standard PHP
		$height = Image::getHeight($svgFile);
		$this->assertNull($height); // SVG returns null as getimagesize() doesn't support it
	}

	public function testGetMimeTypeWithRealSvg(): void
	{
		$svgFile = __DIR__ . '/../fixtures/images/test_icon.svg';
		$this->assertFileExists($svgFile);

		// Note: getimagesize() doesn't support SVG files in standard PHP
		$mimeType = Image::getMimeType($svgFile);
		$this->assertNull($mimeType); // SVG returns null as getimagesize() doesn't support it
	}

	public function testGetImageInfoWithRealSvg(): void
	{
		$svgFile = __DIR__ . '/../fixtures/images/test_icon.svg';
		$this->assertFileExists($svgFile);

		// Note: getimagesize() doesn't support SVG files in standard PHP
		$info = Image::getImageInfo($svgFile);
		$this->assertNull($info); // SVG returns null as getimagesize() doesn't support it
	}

	public function testCheckFileWithRealSvg(): void
	{
		$svgFile = __DIR__ . '/../fixtures/images/test_icon.svg';
		$this->assertFileExists($svgFile);

		$result = Image::checkFile($svgFile, 'test.svg');
		$this->assertTrue($result);
	}

	/* ----- BMP Tests ----- */

	public function testGetWidthWithRealBmp(): void
	{
		$bmpFile = __DIR__ . '/../fixtures/images/test_100x100.bmp';
		$this->assertFileExists($bmpFile);

		$width = Image::getWidth($bmpFile);
		$this->assertSame(100, $width);
	}

	public function testGetHeightWithRealBmp(): void
	{
		$bmpFile = __DIR__ . '/../fixtures/images/test_100x100.bmp';
		$this->assertFileExists($bmpFile);

		$height = Image::getHeight($bmpFile);
		$this->assertSame(100, $height);
	}

	public function testGetMimeTypeWithRealBmp(): void
	{
		$bmpFile = __DIR__ . '/../fixtures/images/test_100x100.bmp';
		$this->assertFileExists($bmpFile);

		$mimeType = Image::getMimeType($bmpFile);
		// BMP MIME type can be 'image/bmp' or 'image/x-ms-bmp' depending on PHP/system version
		$this->assertContains($mimeType, ['image/bmp', 'image/x-ms-bmp']);
	}

	public function testGetImageInfoWithRealBmp(): void
	{
		$bmpFile = __DIR__ . '/../fixtures/images/test_100x100.bmp';
		$this->assertFileExists($bmpFile);

		$info = Image::getImageInfo($bmpFile);
		$this->assertIsArray($info);
		$this->assertSame(100, $info['width']);
		$this->assertSame(100, $info['height']);
		// BMP MIME type can be 'image/bmp' or 'image/x-ms-bmp' depending on PHP/system version
		$this->assertContains($info['mime'], ['image/bmp', 'image/x-ms-bmp']);
	}

	public function testCheckFileWithRealBmp(): void
	{
		$bmpFile = __DIR__ . '/../fixtures/images/test_100x100.bmp';
		$this->assertFileExists($bmpFile);

		$result = Image::checkFile($bmpFile, 'test.bmp');
		$this->assertTrue($result);
	}

	/* ----- WebP Tests ----- */

	public function testGetWidthWithRealWebp(): void
	{
		$webpFile = __DIR__ . '/../fixtures/images/test_80x60.webp';
		$this->assertFileExists($webpFile);

		$width = Image::getWidth($webpFile);
		$this->assertSame(80, $width);
	}

	public function testGetHeightWithRealWebp(): void
	{
		$webpFile = __DIR__ . '/../fixtures/images/test_80x60.webp';
		$this->assertFileExists($webpFile);

		$height = Image::getHeight($webpFile);
		$this->assertSame(60, $height);
	}

	public function testGetMimeTypeWithRealWebp(): void
	{
		$webpFile = __DIR__ . '/../fixtures/images/test_80x60.webp';
		$this->assertFileExists($webpFile);

		$mimeType = Image::getMimeType($webpFile);
		$this->assertSame('image/webp', $mimeType);
	}

	public function testGetImageInfoWithRealWebp(): void
	{
		$webpFile = __DIR__ . '/../fixtures/images/test_80x60.webp';
		$this->assertFileExists($webpFile);

		$info = Image::getImageInfo($webpFile);
		$this->assertIsArray($info);
		$this->assertSame(80, $info['width']);
		$this->assertSame(60, $info['height']);
		$this->assertSame('image/webp', $info['mime']);
	}

	public function testCheckFileWithRealWebp(): void
	{
		$webpFile = __DIR__ . '/../fixtures/images/test_80x60.webp';
		$this->assertFileExists($webpFile);

		$result = Image::checkFile($webpFile, 'test.webp');
		$this->assertTrue($result);
	}

	/* ----- TIFF Tests ----- */

	public function testGetWidthWithRealTiff(): void
	{
		$tiffFile = __DIR__ . '/../fixtures/images/test_120x90.tiff';
		$this->assertFileExists($tiffFile);

		$width = Image::getWidth($tiffFile);
		$this->assertSame(120, $width);
	}

	public function testGetHeightWithRealTiff(): void
	{
		$tiffFile = __DIR__ . '/../fixtures/images/test_120x90.tiff';
		$this->assertFileExists($tiffFile);

		$height = Image::getHeight($tiffFile);
		$this->assertSame(90, $height);
	}

	public function testGetMimeTypeWithRealTiff(): void
	{
		$tiffFile = __DIR__ . '/../fixtures/images/test_120x90.tiff';
		$this->assertFileExists($tiffFile);

		$mimeType = Image::getMimeType($tiffFile);
		$this->assertSame('image/tiff', $mimeType);
	}

	public function testGetImageInfoWithRealTiff(): void
	{
		$tiffFile = __DIR__ . '/../fixtures/images/test_120x90.tiff';
		$this->assertFileExists($tiffFile);

		$info = Image::getImageInfo($tiffFile);
		$this->assertIsArray($info);
		$this->assertSame(120, $info['width']);
		$this->assertSame(90, $info['height']);
		$this->assertSame('image/tiff', $info['mime']);
	}

	public function testCheckFileWithRealTiff(): void
	{
		$tiffFile = __DIR__ . '/../fixtures/images/test_120x90.tiff';
		$this->assertFileExists($tiffFile);

		$result = Image::checkFile($tiffFile, 'test.tiff');
		$this->assertTrue($result);
	}

	/* ----- EXIF Data Tests ----- */

	public function testReadExifDataWithRealJpg(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_with_exif.jpg';
		$this->assertFileExists($jpgFile);

		$exifData = Image::readExifData($jpgFile);

		if (function_exists('exif_read_data')) {
			$this->assertIsArray($exifData);
			$this->assertArrayHasKey('IFD0', $exifData);
		} else {
			$this->assertNull($exifData);
		}
	}

	public function testGetCameraMakeWithExifData(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_with_exif.jpg';
		$this->assertFileExists($jpgFile);

		$cameraMake = Image::getCameraMake($jpgFile);

		if (function_exists('exif_read_data')) {
			$this->assertIsString($cameraMake);
			$this->assertStringContainsString('Test', $cameraMake);
		} else {
			$this->assertNull($cameraMake);
		}
	}

	public function testGetCameraModelWithExifData(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_with_exif.jpg';
		$this->assertFileExists($jpgFile);

		$cameraModel = Image::getCameraModel($jpgFile);

		if (function_exists('exif_read_data')) {
			$this->assertIsString($cameraModel);
			$this->assertStringContainsString('Model', $cameraModel);
		} else {
			$this->assertNull($cameraModel);
		}
	}

	public function testGetPhotoTimestampWithExifData(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_with_exif.jpg';
		$this->assertFileExists($jpgFile);

		$timestamp = Image::getPhotoTimestamp($jpgFile);

		if (function_exists('exif_read_data')) {
			// May or may not have timestamp depending on EXIF success
			$this->assertTrue($timestamp === null || is_int($timestamp));
		} else {
			$this->assertNull($timestamp);
		}
	}

	public function testGetPhotoInfoWithExifData(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_with_exif.jpg';
		$this->assertFileExists($jpgFile);

		$photoInfo = Image::getPhotoInfo($jpgFile);

		if (function_exists('exif_read_data')) {
			$this->assertIsArray($photoInfo);
			$this->assertArrayHasKey('timestamp', $photoInfo);
			$this->assertArrayHasKey('cameraMake', $photoInfo);
			$this->assertArrayHasKey('cameraModel', $photoInfo);
			$this->assertArrayHasKey('iso', $photoInfo);
			$this->assertArrayHasKey('aperture', $photoInfo);
			$this->assertArrayHasKey('shutterSpeed', $photoInfo);
			$this->assertArrayHasKey('focalLength', $photoInfo);
		} else {
			$this->assertNull($photoInfo);
		}
	}

	public function testGetIsoReturnsNullWithoutExifData(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$iso = Image::getIso($jpgFile);
		$this->assertNull($iso);
	}

	public function testGetApertureReturnsNullWithoutExifData(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$aperture = Image::getAperture($jpgFile);
		$this->assertNull($aperture);
	}

	public function testGetShutterSpeedReturnsNullWithoutExifData(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$shutterSpeed = Image::getShutterSpeed($jpgFile);
		$this->assertNull($shutterSpeed);
	}

	public function testGetFocalLengthReturnsNullWithoutExifData(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$focalLength = Image::getFocalLength($jpgFile);
		$this->assertNull($focalLength);
	}

	public function testGetGpsCoordinatesReturnsNullWithoutGpsData(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_with_exif.jpg';
		$this->assertFileExists($jpgFile);

		$gpsCoordinates = Image::getGpsCoordinates($jpgFile);
		$this->assertNull($gpsCoordinates); // Our test image doesn't have GPS data
	}

	/* ----- ETag and Last Modified Tests ----- */

	public function testGetEtagConsistencyWithRealImage(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$etag1 = Image::getEtag($jpgFile);
		$etag2 = Image::getEtag($jpgFile);

		$this->assertSame($etag1, $etag2); // ETag should be consistent for same file
	}

	public function testGetLastModifiedStringFormatWithRealImage(): void
	{
		$pngFile = __DIR__ . '/../fixtures/images/test_200x100.png';
		$this->assertFileExists($pngFile);

		$lastModified = Image::getLastModifiedString($pngFile);

		$this->assertIsString($lastModified);
		$this->assertMatchesRegularExpression('/[A-Z][a-z]{2}, \d{2} [A-Z][a-z]{2} \d{4} \d{2}:\d{2}:\d{2} GMT/', $lastModified);
	}

	/* ----- HTTP Response Tests ----- */

	public function testGetHttpResponseWithRealJpg(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		$response = Image::getHttpResponse($jpgFile, false);

		$this->assertInstanceOf(\Symfony\Component\HttpFoundation\Response::class, $response);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertGreaterThan(1000, strlen($response->getContent()));
	}

	public function testGetHttpResponseWithRealPng(): void
	{
		$pngFile = __DIR__ . '/../fixtures/images/test_200x100.png';
		$this->assertFileExists($pngFile);

		$response = Image::getHttpResponse($pngFile, false);

		$this->assertInstanceOf(\Symfony\Component\HttpFoundation\Response::class, $response);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertNotEmpty($response->getContent());
	}

	public function testGetHttpResponseWithInvalidFile(): void
	{
		$response = Image::getHttpResponse('/non/existent/file.jpg', false);

		$this->assertInstanceOf(\Symfony\Component\HttpFoundation\Response::class, $response);
		$this->assertSame(400, $response->getStatusCode());
		$this->assertSame('file_not_found', $response->getContent());
	}

	/* ----- Format Validation Edge Cases ----- */

	public function testCheckJpgFileFailsWithPngFile(): void
	{
		$pngFile = __DIR__ . '/../fixtures/images/test_200x100.png';
		$this->assertFileExists($pngFile);

		// PNG file should not pass JPG check
		$result = Image::checkJpgFile($pngFile, 'test.jpg');
		$this->assertFalse($result);
	}

	public function testCheckPngFileFailsWithJpgFile(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		// JPG file should not pass PNG check
		$result = Image::checkPngFile($jpgFile, 'test.png');
		$this->assertFalse($result);
	}

	public function testCheckGifFileFailsWithJpgFile(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		// JPG file should not pass GIF check
		$result = Image::checkGifFile($jpgFile, 'test.gif');
		$this->assertFalse($result);
	}

	public function testCheckFileWithWrongExtension(): void
	{
		$jpgFile = __DIR__ . '/../fixtures/images/test_100x50.jpg';
		$this->assertFileExists($jpgFile);

		// JPG file with .txt extension should fail
		$result = Image::checkFile($jpgFile, 'test.txt');
		$this->assertFalse($result);
	}

	/* ----- Multiple Format Support Tests ----- */

	public function testCheckFileAcceptsAllSupportedFormats(): void
	{
		$formats = [
			'test_100x50.jpg' => 'test.jpg',
			'test_200x100.png' => 'test.png',
			'test_150x75.gif' => 'test.gif',
			'test_icon.svg' => 'test.svg',
			'test_100x100.bmp' => 'test.bmp',
			'test_80x60.webp' => 'test.webp',
			'test_120x90.tiff' => 'test.tiff',
		];

		foreach ($formats as $file => $clientName) {
			$filePath = __DIR__ . '/../fixtures/images/' . $file;
			$this->assertFileExists($filePath);
			$result = Image::checkFile($filePath, $clientName);
			$this->assertTrue($result, "Failed to validate $file");
		}
	}

	public function testGetImageInfoWorksForAllFormats(): void
	{
		$formats = [
			'test_100x50.jpg' => ['width' => 100, 'height' => 50],
			'test_200x100.png' => ['width' => 200, 'height' => 100],
			'test_150x75.gif' => ['width' => 150, 'height' => 75],
			// SVG is excluded as getimagesize() doesn't support it
			'test_100x100.bmp' => ['width' => 100, 'height' => 100],
			'test_80x60.webp' => ['width' => 80, 'height' => 60],
			'test_120x90.tiff' => ['width' => 120, 'height' => 90],
		];

		foreach ($formats as $file => $expectedDimensions) {
			$filePath = __DIR__ . '/../fixtures/images/' . $file;
			$this->assertFileExists($filePath);

			$info = Image::getImageInfo($filePath);
			$this->assertIsArray($info, "Failed to get info for $file");
			$this->assertSame($expectedDimensions['width'], $info['width'], "Width mismatch for $file");
			$this->assertSame($expectedDimensions['height'], $info['height'], "Height mismatch for $file");
			$this->assertNotEmpty($info['mime'], "MIME type empty for $file");
		}
	}
}