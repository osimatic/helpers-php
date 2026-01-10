<?php

namespace Tests\FileSystem;

use Osimatic\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;

final class FileSystemTest extends TestCase
{
	/* ===================== Path Formatting ===================== */

	public function testFormatPath(): void
	{
		$ds = DIRECTORY_SEPARATOR;

		// Remove double slashes
		$this->assertEquals("path{$ds}to{$ds}file", FileSystem::formatPath('path//to//file'));
		$this->assertEquals("path{$ds}to{$ds}file", FileSystem::formatPath('path///to///file'));

		// Remove unnecessary ./
		$this->assertEquals("path{$ds}to{$ds}file", FileSystem::formatPath('path/./to/./file'));

		// Replace backslashes with directory separator
		$this->assertEquals("path{$ds}to{$ds}file", FileSystem::formatPath('path\\to\\file'));
		$this->assertEquals("path{$ds}to{$ds}file", FileSystem::formatPath('path/to/file'));

		// Mixed slashes
		$this->assertEquals("path{$ds}to{$ds}file", FileSystem::formatPath('path\\to/file'));
		$this->assertEquals("path{$ds}to{$ds}file", FileSystem::formatPath('path/to\\file'));

		// UNC paths (Windows network paths)
		$this->assertEquals("\\\\server{$ds}share{$ds}file", FileSystem::formatPath('\\\\server\\share\\file'));
		$this->assertEquals("\\\\server{$ds}share{$ds}path", FileSystem::formatPath('\\\\server//share//path'));

		// Absolute paths
		$this->assertEquals("{$ds}var{$ds}www{$ds}html", FileSystem::formatPath('/var/www/html'));
		$this->assertEquals("C:{$ds}Users{$ds}test", FileSystem::formatPath('C:\\Users\\test'));

		// Already formatted path
		$formatted = "path{$ds}to{$ds}file";
		$this->assertEquals($formatted, FileSystem::formatPath($formatted));
	}

	public function testDirname(): void
	{
		$ds = DIRECTORY_SEPARATOR;

		// File path
		$this->assertEquals("path{$ds}to{$ds}", FileSystem::dirname('path/to/file.txt'));
		$this->assertEquals("{$ds}var{$ds}www{$ds}", FileSystem::dirname('/var/www/index.php'));

		// Directory path (with trailing separator)
		$this->assertEquals("path{$ds}to{$ds}dir{$ds}", FileSystem::dirname("path/to/dir/"));
		$this->assertEquals("path{$ds}to{$ds}dir{$ds}", FileSystem::dirname("path/to/dir\\"));

		// Windows paths
		$this->assertEquals("C:{$ds}Users{$ds}", FileSystem::dirname('C:\\Users\\file.txt'));

		// Single file (returns current directory with separator)
		$this->assertEquals(".{$ds}", FileSystem::dirname('file.txt'));
	}

	/* ===================== Directory Creation ===================== */

	public function testCreateDirectories(): void
	{
		$testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit_test_' . uniqid();
		$testPath = $testDir . DIRECTORY_SEPARATOR . 'subdir1' . DIRECTORY_SEPARATOR . 'subdir2' . DIRECTORY_SEPARATOR . 'file.txt';

		// Create nested directories
		$this->assertTrue(FileSystem::createDirectories($testPath));
		$this->assertDirectoryExists($testDir . DIRECTORY_SEPARATOR . 'subdir1');
		$this->assertDirectoryExists($testDir . DIRECTORY_SEPARATOR . 'subdir1' . DIRECTORY_SEPARATOR . 'subdir2');

		// Call again on existing directory
		$this->assertTrue(FileSystem::createDirectories($testPath));

		// Cleanup
		rmdir($testDir . DIRECTORY_SEPARATOR . 'subdir1' . DIRECTORY_SEPARATOR . 'subdir2');
		rmdir($testDir . DIRECTORY_SEPARATOR . 'subdir1');
		rmdir($testDir);
	}

	public function testInitializeFile(): void
	{
		$testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit_test_' . uniqid();
		$testFile = $testDir . DIRECTORY_SEPARATOR . 'subdir' . DIRECTORY_SEPARATOR . 'test.txt';

		// Create file first
		FileSystem::createDirectories($testFile);
		file_put_contents($testFile, 'initial content');
		$this->assertFileExists($testFile);

		// Initialize file (should delete it and recreate directories)
		FileSystem::initializeFile($testFile);
		$this->assertFileDoesNotExist($testFile);
		$this->assertDirectoryExists(dirname($testFile));

		// Cleanup
		rmdir($testDir . DIRECTORY_SEPARATOR . 'subdir');
		rmdir($testDir);
	}
}