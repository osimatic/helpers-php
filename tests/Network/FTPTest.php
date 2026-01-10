<?php

declare(strict_types=1);

namespace Tests\Network;

use Osimatic\Network\FTP;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class FTPTest extends TestCase
{
	/* ===================== Constructor and Logger ===================== */

	public function testCanBeInstantiated(): void
	{
		$ftp = new FTP();
		$this->assertInstanceOf(FTP::class, $ftp);
	}

	public function testCanBeInstantiatedWithLogger(): void
	{
		$logger = new NullLogger();
		$ftp = new FTP($logger);
		$this->assertInstanceOf(FTP::class, $ftp);
	}

	public function testSetLoggerReturnsInstance(): void
	{
		$ftp = new FTP();
		$logger = new NullLogger();
		$result = $ftp->setLogger($logger);
		$this->assertInstanceOf(FTP::class, $result);
		$this->assertSame($ftp, $result);
	}

	/* ===================== Method Existence Tests ===================== */

	public function testConnectMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'connect'));
	}

	public function testConnectWithParamsMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'connectWithParams'));
	}

	public function testIsConnectedMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'isConnected'));
	}

	public function testDisconnectMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'disconnect'));
	}

	public function testCheckConnectionMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'checkConnection'));
	}

	public function testSetPassiveModeMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'setPassiveMode'));
	}

	public function testExecuteCommandMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'executeCommand'));
	}

	public function testChangeParentDirectoryMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'changeParentDirectory'));
	}

	public function testChangeDirectoryMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'changeDirectory'));
	}

	public function testGetCurrentDirectoryMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'getCurrentDirectory'));
	}

	public function testGetAbsolutePathMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'getAbsolutePath'));
	}

	public function testUploadMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'upload'));
	}

	public function testDownloadMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'download'));
	}

	public function testGetListFilesMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'getListFiles'));
	}

	public function testGetContentDirectoryMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'getContentDirectory'));
	}

	public function testCreateDirMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'createDir'));
	}

	public function testRenameDirMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'renameDir'));
	}

	public function testDeleteDirMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'deleteDir'));
	}

	public function testIsFileMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'isFile'));
	}

	public function testFileExistMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'fileExist'));
	}

	public function testRenameFileMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'renameFile'));
	}

	public function testDeleteFileMethodExists(): void
	{
		$this->assertTrue(method_exists(FTP::class, 'deleteFile'));
	}
}
