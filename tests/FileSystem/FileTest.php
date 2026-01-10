<?php

namespace Tests\FileSystem;

use Osimatic\FileSystem\File;
use PHPUnit\Framework\TestCase;

final class FileTest extends TestCase
{
	/* ===================== Base64 Data Handling ===================== */

	public function testGetDataFromBase64Data(): void
	{
		// Valid base64 with prefix
		$data = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
		$result = File::getDataFromBase64Data($data);
		$this->assertNotNull($result);
		$this->assertIsString($result);

		// Valid base64 without prefix
		$base64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
		$result = File::getDataFromBase64Data($base64);
		$this->assertNotNull($result);

		// Invalid base64
		$this->assertNull(File::getDataFromBase64Data('invalid!!!'));
	}

	public function testGetMimeTypeFromBase64Data(): void
	{
		// With mime type prefix
		$data = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
		$this->assertEquals('image/png', File::getMimeTypeFromBase64Data($data));

		// PDF signature
		$pdfData = base64_encode("\x25\x50\x44\x46\x2D");
		$this->assertEquals('application/pdf', File::getMimeTypeFromBase64Data($pdfData));

		// JPEG signature
		$jpegData = base64_encode("\xFF\xD8\xFF");
		$this->assertEquals('image/jpeg', File::getMimeTypeFromBase64Data($jpegData));

		// PNG signature
		$pngData = base64_encode("\x89\x50\x4E\x47\x0D\x0A\x1A\x0A");
		$this->assertEquals('image/png', File::getMimeTypeFromBase64Data($pngData));

		// GIF signature
		$gifData = base64_encode("\x47\x49\x46\x38\x39\x61");
		$this->assertEquals('image/gif', File::getMimeTypeFromBase64Data($gifData));

		// Bitmap signature
		$bmpData = base64_encode("\x42\x4D");
		$this->assertEquals('image/bmp', File::getMimeTypeFromBase64Data($bmpData));

		// ZIP signature
		$zipData = base64_encode("\x50\x4B\x03\x04");
		$this->assertEquals('application/zip', File::getMimeTypeFromBase64Data($zipData));
	}

	/* ===================== File Extension ===================== */

	public function testGetExtension(): void
	{
		$this->assertEquals('txt', File::getExtension('file.txt'));
		$this->assertEquals('pdf', File::getExtension('/path/to/document.pdf'));
		$this->assertEquals('jpg', File::getExtension('C:\\Users\\test\\image.jpg'));
		$this->assertEquals('', File::getExtension('noextension'));

		// Double extensions
		$this->assertEquals('tar.gz', File::getExtension('archive.tar.gz'));
		$this->assertEquals('tar.bz2', File::getExtension('backup.tar.bz2'));
		$this->assertEquals('tar.xz', File::getExtension('file.tar.xz'));
	}

	public function testReplaceExtension(): void
	{
		$this->assertEquals('file.pdf', File::replaceExtension('file.txt', 'pdf'));
		$this->assertEquals('file.pdf', File::replaceExtension('file.txt', '.pdf'));
		$this->assertEquals('/path/to/document.jpg', File::replaceExtension('/path/to/document.png', 'jpg'));
		$this->assertEquals('C:\test\file.docx', File::replaceExtension('C:\test\file.txt', 'docx'));
		$this->assertEquals('noext.txt', File::replaceExtension('noext', 'txt'));
	}

	/* ===================== File Size Formatting ===================== */

	public function testFormatSize(): void
	{
		// Bytes
		$this->assertEquals('0.00 o', File::formatSize(0));
		$this->assertEquals('100.00 o', File::formatSize(100));
		$this->assertEquals('1023.00 o', File::formatSize(1023));

		// Kilobytes
		$this->assertEquals('1.00 Ko', File::formatSize(1024));
		$this->assertEquals('10.00 Ko', File::formatSize(10240));

		// Megabytes
		$this->assertEquals('1.00 Mo', File::formatSize(1048576)); // 1024^2
		$this->assertEquals('5.50 Mo', File::formatSize(5767168)); // 5.5 MB

		// Gigabytes
		$this->assertEquals('1.00 Go', File::formatSize(1073741824)); // 1024^3
		$this->assertEquals('2.50 Go', File::formatSize(2684354560)); // 2.5 GB

		// Custom decimal places
		$this->assertEquals('1.5 Mo', File::formatSize(1572864, 1)); // 1 decimal
		$this->assertEquals('1 Mo', File::formatSize(1048576, 0)); // No decimals

		// Negative values (edge case)
		$this->assertEquals('0.00 o', File::formatSize(-100));
	}

	/* ===================== MIME Type / Extension Mapping ===================== */

	public function testGetMimeTypeFromExtension(): void
	{
		// Image formats
		$this->assertEquals('image/jpeg', File::getMimeTypeFromExtension('jpg'));
		$this->assertEquals('image/jpeg', File::getMimeTypeFromExtension('.jpg'));
		$this->assertEquals('image/png', File::getMimeTypeFromExtension('png'));
		$this->assertEquals('image/gif', File::getMimeTypeFromExtension('gif'));

		// Document formats
		$this->assertEquals('application/pdf', File::getMimeTypeFromExtension('pdf'));
		$this->assertEquals('application/msword', File::getMimeTypeFromExtension('doc'));
		$this->assertEquals('application/vnd.openxmlformats-officedocument.wordprocessingml.document', File::getMimeTypeFromExtension('docx'));
		$this->assertEquals('application/vnd.ms-excel', File::getMimeTypeFromExtension('xls'));
		$this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', File::getMimeTypeFromExtension('xlsx'));

		// Text formats
		$this->assertEquals('text/plain', File::getMimeTypeFromExtension('txt'));
		$this->assertEquals('text/html', File::getMimeTypeFromExtension('html'));
		$this->assertEquals('text/css', File::getMimeTypeFromExtension('css'));
		$this->assertEquals('text/csv', File::getMimeTypeFromExtension('csv'));

		// Archive formats
		$this->assertEquals('application/zip', File::getMimeTypeFromExtension('zip'));

		// Case insensitivity
		$this->assertEquals('image/jpeg', File::getMimeTypeFromExtension('JPG'));
		$this->assertEquals('image/jpeg', File::getMimeTypeFromExtension('JpG'));

		// Unknown extension
		$this->assertNull(File::getMimeTypeFromExtension('unknown'));
	}

	public function testGetExtensionFromMimeType(): void
	{
		// Image formats
		$this->assertEquals('jpg', File::getExtensionFromMimeType('image/jpeg'));
		$this->assertEquals('png', File::getExtensionFromMimeType('image/png'));
		$this->assertEquals('gif', File::getExtensionFromMimeType('image/gif'));

		// Document formats
		$this->assertEquals('pdf', File::getExtensionFromMimeType('application/pdf'));
		$this->assertEquals('doc', File::getExtensionFromMimeType('application/msword'));
		$this->assertEquals('xlsx', File::getExtensionFromMimeType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'));

		// Text formats
		$this->assertEquals('txt', File::getExtensionFromMimeType('text/plain'));
		$this->assertEquals('html', File::getExtensionFromMimeType('text/html'));
		$this->assertEquals('css', File::getExtensionFromMimeType('text/css'));

		// Archive formats
		$this->assertEquals('zip', File::getExtensionFromMimeType('application/zip'));

		// Case insensitivity
		$this->assertEquals('jpg', File::getExtensionFromMimeType('IMAGE/JPEG'));

		// Unknown MIME type
		$this->assertNull(File::getExtensionFromMimeType('application/x-unknown'));
	}

	public function testGetMimeTypeForFile(): void
	{
		// With extension
		$this->assertEquals('image/jpeg', File::getMimeTypeForFile('image.jpg'));
		$this->assertEquals('application/pdf', File::getMimeTypeForFile('document.pdf'));
		$this->assertEquals('text/plain', File::getMimeTypeForFile('readme.txt'));

		// With full path
		$this->assertEquals('image/png', File::getMimeTypeForFile('/var/www/images/photo.png'));
		$this->assertEquals('application/zip', File::getMimeTypeForFile('C:\Downloads\archive.zip'));

		// With query string (like Bitly URLs)
		$this->assertEquals('image/jpeg', File::getMimeTypeForFile('photo.jpg?v=123&size=large'));

		// Unknown extension defaults to octet-stream
		$this->assertEquals('application/octet-stream', File::getMimeTypeForFile('file.unknown'));
		$this->assertEquals('application/octet-stream', File::getMimeTypeForFile('noextension'));
	}

	/* ===================== Multibyte-safe pathinfo ===================== */

	public function testMbPathinfo(): void
	{
		// Basic path
		$result = File::mb_pathinfo('/path/to/file.txt');
		$this->assertEquals('/path/to', $result['dirname']);
		$this->assertEquals('file.txt', $result['basename']);
		$this->assertEquals('txt', $result['extension']);
		$this->assertEquals('file', $result['filename']);

		// With specific option
		$this->assertEquals('txt', File::mb_pathinfo('/path/to/file.txt', PATHINFO_EXTENSION));
		$this->assertEquals('file', File::mb_pathinfo('/path/to/file.txt', PATHINFO_FILENAME));
		$this->assertEquals('file.txt', File::mb_pathinfo('/path/to/file.txt', PATHINFO_BASENAME));
		$this->assertEquals('/path/to', File::mb_pathinfo('/path/to/file.txt', PATHINFO_DIRNAME));

		// Windows path
		$result = File::mb_pathinfo('C:\Users\test\document.pdf');
		$this->assertEquals('C:\Users\test', $result['dirname']);
		$this->assertEquals('document.pdf', $result['basename']);
		$this->assertEquals('pdf', $result['extension']);
		$this->assertEquals('document', $result['filename']);

		// No extension
		$result = File::mb_pathinfo('/path/to/file');
		$this->assertEquals('', $result['extension']);
		$this->assertEquals('file', $result['filename']);

		// Multiple extensions
		$result = File::mb_pathinfo('archive.tar.gz');
		$this->assertEquals('gz', $result['extension']);
		$this->assertEquals('archive.tar', $result['filename']);

		// Filename only
		$result = File::mb_pathinfo('file.txt');
		$this->assertEquals('', $result['dirname']);
		$this->assertEquals('file.txt', $result['basename']);

		// With multibyte characters (UTF-8)
		$result = File::mb_pathinfo('/chemin/vers/fichier-été.txt');
		$this->assertEquals('fichier-été.txt', $result['basename']);
		$this->assertEquals('fichier-été', $result['filename']);
	}
}