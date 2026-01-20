<?php

namespace Tests\FileSystem;

use Osimatic\FileSystem\InputFile;
use PHPUnit\Framework\TestCase;

final class InputFileTest extends TestCase
{
	public function testConstructorWithUploadedFileInfos(): void
	{
		$uploadedFileInfos = [
			'tmp_name' => '/tmp/phpABC123',
			'name' => 'document.pdf',
		];

		$inputFile = new InputFile($uploadedFileInfos);

		$this->assertEquals('/tmp/phpABC123', $inputFile->getUploadedFilePath());
		$this->assertEquals('document.pdf', $inputFile->getOriginalFileName());
	}

	public function testConstructorWithData(): void
	{
		$data = 'binary file data';
		$mimeType = 'image/png';
		$base64Data = base64_encode($data);

		$inputFile = new InputFile(null, $data, $mimeType, $base64Data);

		$this->assertEquals($data, $inputFile->getData());
		$this->assertEquals($mimeType, $inputFile->getMimeType());
		$this->assertEquals($base64Data, $inputFile->getBase64EncodedData());
	}

	public function testSetUploadedFileInfos(): void
	{
		$inputFile = new InputFile();

		$uploadedFileInfos = [
			'tmp_name' => '/tmp/phpXYZ456',
			'name' => 'photo.jpg',
		];

		$inputFile->setUploadedFileInfos($uploadedFileInfos);

		$this->assertEquals('/tmp/phpXYZ456', $inputFile->getUploadedFilePath());
		$this->assertEquals('photo.jpg', $inputFile->getOriginalFileName());
	}

	public function testGettersAndSetters(): void
	{
		$inputFile = new InputFile();

		// Uploaded file path
		$inputFile->setUploadedFilePath('/tmp/upload123');
		$this->assertEquals('/tmp/upload123', $inputFile->getUploadedFilePath());

		// Path
		$inputFile->setPath('/var/www/files/document.pdf');
		$this->assertEquals('/var/www/files/document.pdf', $inputFile->getPath());

		// Original file name
		$inputFile->setOriginalFileName('mydocument.pdf');
		$this->assertEquals('mydocument.pdf', $inputFile->getOriginalFileName());

		// Data
		$inputFile->setData('file binary data');
		$this->assertEquals('file binary data', $inputFile->getData());

		// Base64 encoded data
		$inputFile->setBase64EncodedData('YmFzZTY0ZGF0YQ==');
		$this->assertEquals('YmFzZTY0ZGF0YQ==', $inputFile->getBase64EncodedData());

		// Mime type
		$inputFile->setMimeType('application/pdf');
		$this->assertEquals('application/pdf', $inputFile->getMimeType());

		// File size
		$inputFile->setFileSize(1024);
		$this->assertEquals(1024, $inputFile->getFileSize());
	}

	public function testGetExtensionFromOriginalFileName(): void
	{
		$inputFile = new InputFile();
		$inputFile->setOriginalFileName('document.pdf');

		$this->assertEquals('pdf', $inputFile->getExtension());

		$inputFile->setOriginalFileName('photo.jpg');
		$this->assertEquals('jpg', $inputFile->getExtension());

		$inputFile->setOriginalFileName('archive.tar.gz');
		$this->assertEquals('gz', $inputFile->getExtension()); // Returns last extension only by default
	}

	public function testGetExtensionFromMimeType(): void
	{
		$inputFile = new InputFile();
		$inputFile->setMimeType('image/jpeg');

		// Should fallback to mime type when no original filename
		$this->assertEquals('jpg', $inputFile->getExtension());

		$inputFile->setMimeType('application/pdf');
		$this->assertEquals('pdf', $inputFile->getExtension());

		$inputFile->setMimeType('text/plain');
		$this->assertEquals('txt', $inputFile->getExtension());
	}

	public function testGetExtensionReturnsNull(): void
	{
		$inputFile = new InputFile();

		// No original filename and no mime type
		$this->assertNull($inputFile->getExtension());

		// Unknown mime type
		$inputFile->setMimeType('application/x-unknown');
		$this->assertNull($inputFile->getExtension());
	}

	public function testFluentInterface(): void
	{
		$inputFile = new InputFile();

		// Test method chaining
		$result = $inputFile
			->setUploadedFilePath('/tmp/file')
			->setPath('/var/www/file')
			->setOriginalFileName('test.txt')
			->setData('data')
			->setBase64EncodedData('base64')
			->setMimeType('text/plain')
			->setFileSize(100);

		$this->assertInstanceOf(InputFile::class, $result);
		$this->assertEquals('/tmp/file', $inputFile->getUploadedFilePath());
		$this->assertEquals('/var/www/file', $inputFile->getPath());
		$this->assertEquals('test.txt', $inputFile->getOriginalFileName());
		$this->assertEquals('data', $inputFile->getData());
		$this->assertEquals('base64', $inputFile->getBase64EncodedData());
		$this->assertEquals('text/plain', $inputFile->getMimeType());
		$this->assertEquals(100, $inputFile->getFileSize());
	}

	public function testNullValues(): void
	{
		$inputFile = new InputFile();

		$this->assertNull($inputFile->getUploadedFilePath());
		$this->assertNull($inputFile->getPath());
		$this->assertNull($inputFile->getOriginalFileName());
		$this->assertNull($inputFile->getData());
		$this->assertNull($inputFile->getBase64EncodedData());
		$this->assertNull($inputFile->getMimeType());
		$this->assertNull($inputFile->getFileSize());
	}
}