<?php

namespace Tests\FileSystem;

use Osimatic\FileSystem\OutputFile;
use PHPUnit\Framework\TestCase;

final class OutputFileTest extends TestCase
{
	public function testConstructorWithParameters(): void
	{
		$outputFile = new OutputFile('/path/to/file.pdf', 'document.pdf');

		$this->assertEquals('/path/to/file.pdf', $outputFile->getFilePath());
		$this->assertEquals('document.pdf', $outputFile->getFileName());
	}

	public function testConstructorWithoutParameters(): void
	{
		$outputFile = new OutputFile();

		$this->assertNull($outputFile->getFilePath());
		$this->assertNull($outputFile->getFileName());
	}

	public function testSetFilePath(): void
	{
		$outputFile = new OutputFile();

		$outputFile->setFilePath('/var/www/files/document.pdf');
		$this->assertEquals('/var/www/files/document.pdf', $outputFile->getFilePath());

		$outputFile->setFilePath(null);
		$this->assertNull($outputFile->getFilePath());
	}

	public function testSetFileName(): void
	{
		$outputFile = new OutputFile();

		$outputFile->setFileName('mydocument.pdf');
		$this->assertEquals('mydocument.pdf', $outputFile->getFileName());

		$outputFile->setFileName(null);
		$this->assertNull($outputFile->getFileName());
	}

	public function testGetExtensionFromFilePath(): void
	{
		$outputFile = new OutputFile('/path/to/document.pdf');

		$this->assertEquals('pdf', $outputFile->getExtension());

		$outputFile->setFilePath('/path/to/photo.jpg');
		$this->assertEquals('jpg', $outputFile->getExtension());

		$outputFile->setFilePath('/path/to/archive.tar.gz');
		$this->assertEquals('gz', $outputFile->getExtension());
	}

	public function testGetExtensionFromFileName(): void
	{
		$outputFile = new OutputFile(null, 'document.pdf');

		// Should use fileName when filePath is null
		$this->assertEquals('pdf', $outputFile->getExtension());

		$outputFile->setFileName('photo.PNG');
		$this->assertEquals('png', $outputFile->getExtension()); // Lowercase

		$outputFile->setFileName('archive.tar.gz');
		$this->assertEquals('gz', $outputFile->getExtension());
	}

	public function testGetExtensionPreferesFilePath(): void
	{
		$outputFile = new OutputFile('/path/to/file.pdf', 'display.txt');

		// Should prefer filePath over fileName
		$this->assertEquals('pdf', $outputFile->getExtension());
	}

	public function testGetExtensionReturnsNull(): void
	{
		$outputFile = new OutputFile();

		// No filePath and no fileName
		$this->assertNull($outputFile->getExtension());

		// No extension
		$outputFile->setFilePath('/path/to/noextension');
		$this->assertNull($outputFile->getExtension());

		$outputFile->setFilePath(null);
		$outputFile->setFileName('noextension');
		$this->assertNull($outputFile->getExtension());
	}

	public function testGetExtensionCaseInsensitive(): void
	{
		$outputFile = new OutputFile('/path/to/FILE.PDF');
		$this->assertEquals('pdf', $outputFile->getExtension());

		$outputFile->setFilePath('/path/to/PHOTO.JPG');
		$this->assertEquals('jpg', $outputFile->getExtension());

		$outputFile = new OutputFile(null, 'DOCUMENT.TXT');
		$this->assertEquals('txt', $outputFile->getExtension());
	}

	public function testEmptyExtension(): void
	{
		$outputFile = new OutputFile('/path/to/file.');

		// Trailing dot with no extension
		$this->assertNull($outputFile->getExtension());
	}

	public function testMultipleExtensions(): void
	{
		$outputFile = new OutputFile('/path/to/backup.tar.gz.bak');

		// Should return the last extension
		$this->assertEquals('bak', $outputFile->getExtension());
	}

	public function testWindowsPaths(): void
	{
		$outputFile = new OutputFile('C:\\Users\\test\\document.docx');
		$this->assertEquals('docx', $outputFile->getExtension());

		$outputFile->setFilePath('D:\\Projects\\archive.zip');
		$this->assertEquals('zip', $outputFile->getExtension());
	}

	public function testUnixPaths(): void
	{
		$outputFile = new OutputFile('/var/www/html/index.php');
		$this->assertEquals('php', $outputFile->getExtension());

		$outputFile->setFilePath('/home/user/photo.jpeg');
		$this->assertEquals('jpeg', $outputFile->getExtension());
	}
}