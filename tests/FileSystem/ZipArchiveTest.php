<?php

namespace Tests\FileSystem;

use Osimatic\FileSystem\OutputFile;
use Osimatic\FileSystem\ZipArchive;
use PHPUnit\Framework\TestCase;

final class ZipArchiveTest extends TestCase
{
	private string $testDir;

	protected function setUp(): void
	{
		$this->testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit_zip_test_' . uniqid();
		mkdir($this->testDir, 0777, true);
	}

	protected function tearDown(): void
	{
		// Cleanup test directory
		$this->removeDirectory($this->testDir);
	}

	private function removeDirectory(string $dir): void
	{
		if (!is_dir($dir)) {
			return;
		}

		$files = array_diff(scandir($dir), ['.', '..']);
		foreach ($files as $file) {
			$path = $dir . DIRECTORY_SEPARATOR . $file;
			is_dir($path) ? $this->removeDirectory($path) : unlink($path);
		}
		rmdir($dir);
	}

	/* ===================== Archive Creation ===================== */

	public function testArchive(): void
	{
		// Create test files
		$file1 = $this->testDir . DIRECTORY_SEPARATOR . 'file1.txt';
		$file2 = $this->testDir . DIRECTORY_SEPARATOR . 'file2.txt';
		file_put_contents($file1, 'Content of file 1');
		file_put_contents($file2, 'Content of file 2');

		// Create archive
		$zipPath = $this->testDir . DIRECTORY_SEPARATOR . 'archive.zip';
		ZipArchive::archive($zipPath, [$file1, $file2]);

		// Verify archive exists
		$this->assertFileExists($zipPath);

		// Verify archive contains files
		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($zipPath));
		$this->assertEquals(2, $zip->numFiles);
		$this->assertEquals('file1.txt', $zip->getNameIndex(0));
		$this->assertEquals('file2.txt', $zip->getNameIndex(1));
		$zip->close();
	}

	public function testArchiveWithNonExistentFiles(): void
	{
		$file1 = $this->testDir . DIRECTORY_SEPARATOR . 'existing.txt';
		$file2 = $this->testDir . DIRECTORY_SEPARATOR . 'nonexistent.txt';
		file_put_contents($file1, 'Content');

		$zipPath = $this->testDir . DIRECTORY_SEPARATOR . 'archive.zip';
		ZipArchive::archive($zipPath, [$file1, $file2]);

		$this->assertFileExists($zipPath);

		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($zipPath));
		// Only the existing file should be in the archive
		$this->assertEquals(1, $zip->numFiles);
		$this->assertEquals('existing.txt', $zip->getNameIndex(0));
		$zip->close();
	}

	public function testArchiveOutputFiles(): void
	{
		// Create test files
		$file1 = $this->testDir . DIRECTORY_SEPARATOR . 'test1.txt';
		$file2 = $this->testDir . DIRECTORY_SEPARATOR . 'test2.txt';
		file_put_contents($file1, 'Test content 1');
		file_put_contents($file2, 'Test content 2');

		// Create OutputFile objects
		$outputFiles = [
			new OutputFile($file1, 'renamed1.txt'),
			new OutputFile($file2, 'renamed2.txt'),
		];

		// Create archive
		$zipPath = $this->testDir . DIRECTORY_SEPARATOR . 'output_archive.zip';
		ZipArchive::archiveOutputFiles($zipPath, $outputFiles);

		$this->assertFileExists($zipPath);

		// Verify files are renamed in archive
		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($zipPath));
		$this->assertEquals(2, $zip->numFiles);
		$this->assertEquals('renamed1.txt', $zip->getNameIndex(0));
		$this->assertEquals('renamed2.txt', $zip->getNameIndex(1));
		$zip->close();
	}

	public function testArchiveOutputFilesWithoutCustomName(): void
	{
		$file = $this->testDir . DIRECTORY_SEPARATOR . 'original.txt';
		file_put_contents($file, 'Content');

		$outputFiles = [
			new OutputFile($file), // No custom fileName
		];

		$zipPath = $this->testDir . DIRECTORY_SEPARATOR . 'archive.zip';
		ZipArchive::archiveOutputFiles($zipPath, $outputFiles);

		$this->assertFileExists($zipPath);

		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($zipPath));
		$this->assertEquals(1, $zip->numFiles);
		$this->assertEquals('original.txt', $zip->getNameIndex(0)); // Uses basename
		$zip->close();
	}

	public function testArchiveOutputFilesWithNullFilePath(): void
	{
		$file = $this->testDir . DIRECTORY_SEPARATOR . 'valid.txt';
		file_put_contents($file, 'Content');

		$outputFiles = [
			new OutputFile($file, 'valid.txt'),
			new OutputFile(null, 'invalid.txt'), // Null filePath should be skipped
		];

		$zipPath = $this->testDir . DIRECTORY_SEPARATOR . 'archive.zip';
		ZipArchive::archiveOutputFiles($zipPath, $outputFiles);

		$this->assertFileExists($zipPath);

		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($zipPath));
		$this->assertEquals(1, $zip->numFiles); // Only valid file
		$this->assertEquals('valid.txt', $zip->getNameIndex(0));
		$zip->close();
	}

	public function testArchiveFilesFromString(): void
	{
		$contentFiles = [
			'file1.txt' => 'This is the content of file 1',
			'file2.txt' => 'This is the content of file 2',
			'subdir/file3.txt' => 'Content in subdirectory',
		];

		$zipPath = $this->testDir . DIRECTORY_SEPARATOR . 'string_archive.zip';
		ZipArchive::archiveFilesFromString($zipPath, $contentFiles);

		$this->assertFileExists($zipPath);

		// Verify archive contents
		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($zipPath));
		$this->assertEquals(3, $zip->numFiles);

		// Verify file names
		$this->assertEquals('file1.txt', $zip->getNameIndex(0));
		$this->assertEquals('file2.txt', $zip->getNameIndex(1));
		$this->assertEquals('subdir/file3.txt', $zip->getNameIndex(2));

		// Verify content
		$this->assertEquals('This is the content of file 1', $zip->getFromName('file1.txt'));
		$this->assertEquals('This is the content of file 2', $zip->getFromName('file2.txt'));
		$this->assertEquals('Content in subdirectory', $zip->getFromName('subdir/file3.txt'));

		$zip->close();
	}

	public function testArchiveFilesFromStringWithEmptyContent(): void
	{
		$contentFiles = [
			'empty.txt' => '',
			'nonempty.txt' => 'Has content',
		];

		$zipPath = $this->testDir . DIRECTORY_SEPARATOR . 'mixed_archive.zip';
		ZipArchive::archiveFilesFromString($zipPath, $contentFiles);

		$this->assertFileExists($zipPath);

		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($zipPath));
		$this->assertEquals(2, $zip->numFiles);
		$this->assertEquals('', $zip->getFromName('empty.txt'));
		$this->assertEquals('Has content', $zip->getFromName('nonempty.txt'));
		$zip->close();
	}

	public function testArchiveReplacesExistingFile(): void
	{
		$zipPath = $this->testDir . DIRECTORY_SEPARATOR . 'existing.zip';

		// Create initial archive
		$file1 = $this->testDir . DIRECTORY_SEPARATOR . 'file1.txt';
		file_put_contents($file1, 'Initial content');
		ZipArchive::archive($zipPath, [$file1]);

		$this->assertFileExists($zipPath);
		$initialSize = filesize($zipPath);

		// Create new archive (should replace)
		$file2 = $this->testDir . DIRECTORY_SEPARATOR . 'file2.txt';
		file_put_contents($file2, 'New content that is much longer');
		ZipArchive::archive($zipPath, [$file2]);

		$this->assertFileExists($zipPath);

		// Verify archive was replaced
		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($zipPath));
		$this->assertEquals(1, $zip->numFiles);
		$this->assertEquals('file2.txt', $zip->getNameIndex(0));
		$zip->close();
	}

	/* ===================== Constants ===================== */

	public function testConstants(): void
	{
		$this->assertEquals('.zip', ZipArchive::FILE_EXTENSION);
		$this->assertIsArray(ZipArchive::MIME_TYPES);
		$this->assertContains('application/zip', ZipArchive::MIME_TYPES);
		$this->assertContains('application/x-zip', ZipArchive::MIME_TYPES);
		$this->assertContains('application/x-zip-compressed', ZipArchive::MIME_TYPES);
	}
}