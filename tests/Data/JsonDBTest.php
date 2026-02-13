<?php

namespace Tests\Data;

use Osimatic\Data\JsonDB;
use PHPUnit\Framework\TestCase;

class JsonDBTest extends TestCase
{
	private string $testDataDir;

	protected function setUp(): void
	{
		// Create a temporary directory for tests
		$this->testDataDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jsondb_test_' . uniqid();
		if (!is_dir($this->testDataDir)) {
			mkdir($this->testDataDir, 0755, true);
		}

		// Reset singleton instance before each test
		JsonDB::resetInstance();
	}

	protected function tearDown(): void
	{
		// Reset singleton instance after each test
		JsonDB::resetInstance();

		// Clean up test directory
		if (is_dir($this->testDataDir)) {
			$this->recursiveRemoveDirectory($this->testDataDir);
		}
	}

	private function recursiveRemoveDirectory(string $directory): void
	{
		if (!is_dir($directory)) {
			return;
		}

		$files = array_diff(scandir($directory), ['.', '..']);
		foreach ($files as $file) {
			$path = $directory . DIRECTORY_SEPARATOR . $file;
			is_dir($path) ? $this->recursiveRemoveDirectory($path) : unlink($path);
		}
		rmdir($directory);
	}

	// ========================================
	// Singleton Pattern Methods Tests
	// ========================================

	public function testGetInstance(): void
	{
		$instance1 = JsonDB::getInstance();
		$instance2 = JsonDB::getInstance();

		self::assertInstanceOf(JsonDB::class, $instance1);
		self::assertSame($instance1, $instance2);
	}

	public function testGetInstanceCreatesDefaultDataDirectory(): void
	{
		$instance = JsonDB::getInstance();

		self::assertNotEmpty($instance->getDataDirectory());
		self::assertIsString($instance->getDataDirectory());
	}

	public function testInitialize(): void
	{
		JsonDB::initialize($this->testDataDir);
		$instance = JsonDB::getInstance();

		self::assertSame($this->testDataDir, $instance->getDataDirectory());
	}

	public function testInitializeThrowsExceptionWhenGetInstanceAlreadyCalled(): void
	{
		JsonDB::getInstance();

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Cannot initialize JsonDB: getInstance() has already been called');

		JsonDB::initialize($this->testDataDir);
	}

	public function testResetInstance(): void
	{
		$instance1 = JsonDB::getInstance();
		JsonDB::resetInstance();
		$instance2 = JsonDB::getInstance();

		self::assertNotSame($instance1, $instance2);
	}

	public function testResetInstanceAllowsReinitialize(): void
	{
		JsonDB::initialize($this->testDataDir);
		$instance1 = JsonDB::getInstance();
		JsonDB::resetInstance();

		$newDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jsondb_test_new_' . uniqid();
		mkdir($newDir, 0755, true);

		JsonDB::initialize($newDir);
		$instance2 = JsonDB::getInstance();

		self::assertNotSame($instance1, $instance2);
		self::assertSame($newDir, $instance2->getDataDirectory());

		// Clean up
		$this->recursiveRemoveDirectory($newDir);
	}

	public function testCloneIsNotAllowed(): void
	{
		$instance = JsonDB::getInstance();

		try {
			$clone = clone $instance;
			self::fail('Expected Error to be thrown when cloning singleton');
		} catch (\Error $e) {
			self::assertStringContainsString('__clone', $e->getMessage());
		}
	}

	public function testUnserializeThrowsException(): void
	{
		$instance = JsonDB::getInstance();
		$serialized = serialize($instance);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Cannot unserialize singleton');

		unserialize($serialized);
	}

	public function testDirectInstantiationWithoutPath(): void
	{
		$instance = new JsonDB();

		self::assertInstanceOf(JsonDB::class, $instance);
		self::assertNotEmpty($instance->getDataDirectory());
	}

	public function testDirectInstantiationWithCustomPath(): void
	{
		$instance = new JsonDB($this->testDataDir);

		self::assertSame($this->testDataDir, $instance->getDataDirectory());
	}

	public function testDirectInstantiationDoesNotAffectSingleton(): void
	{
		$directInstance = new JsonDB($this->testDataDir);
		$singletonInstance = JsonDB::getInstance();

		self::assertNotSame($directInstance, $singletonInstance);
		self::assertNotSame($directInstance->getDataDirectory(), $singletonInstance->getDataDirectory());
	}

	// ========================================
	// Configuration Methods Tests
	// ========================================

	public function testSetDataDirectory(): void
	{
		$instance = new JsonDB();
		$result = $instance->setDataDirectory($this->testDataDir);

		self::assertSame($instance, $result);
		self::assertSame($this->testDataDir, $instance->getDataDirectory());
	}

	public function testSetDataDirectoryCreatesDirectoryWhenNotExists(): void
	{
		$newDir = $this->testDataDir . DIRECTORY_SEPARATOR . 'subdir';
		$instance = new JsonDB();

		$instance->setDataDirectory($newDir, true);

		self::assertTrue(is_dir($newDir));
		self::assertSame($newDir, $instance->getDataDirectory());
	}

	public function testSetDataDirectoryThrowsExceptionWhenPathIsEmpty(): void
	{
		$instance = new JsonDB();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Data directory path cannot be empty');

		$instance->setDataDirectory('');
	}

	public function testSetDataDirectoryThrowsExceptionWhenPathDoesNotExistAndCreateIsFalse(): void
	{
		$instance = new JsonDB();
		$nonExistentPath = $this->testDataDir . DIRECTORY_SEPARATOR . 'nonexistent';

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Data directory does not exist');

		$instance->setDataDirectory($nonExistentPath, false);
	}

	public function testSetDataDirectoryThrowsExceptionWhenPathIsNotDirectory(): void
	{
		$filePath = $this->testDataDir . DIRECTORY_SEPARATOR . 'file.txt';
		file_put_contents($filePath, 'test');
		$instance = new JsonDB();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Path is not a directory');

		$instance->setDataDirectory($filePath);
	}

	public function testSetDataDirectoryThrowsExceptionWhenPathIsNotWritable(): void
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			self::markTestSkipped('Test skipped on Windows (chmod behavior differs)');
		}

		$readOnlyDir = $this->testDataDir . DIRECTORY_SEPARATOR . 'readonly';
		mkdir($readOnlyDir, 0755);
		chmod($readOnlyDir, 0444);

		$instance = new JsonDB();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Data directory is not writable');

		try {
			$instance->setDataDirectory($readOnlyDir);
		} finally {
			chmod($readOnlyDir, 0755);
		}
	}

	public function testGetDataDirectory(): void
	{
		$instance = new JsonDB($this->testDataDir);

		self::assertSame($this->testDataDir, $instance->getDataDirectory());
	}

	public function testGetDataDir(): void
	{
		$instance = new JsonDB($this->testDataDir);

		self::assertSame($this->testDataDir, $instance->getDataDir());
	}

	public function testGetDataDirIsDeprecatedAlias(): void
	{
		$instance = new JsonDB($this->testDataDir);

		self::assertSame($instance->getDataDirectory(), $instance->getDataDir());
	}

	public function testSetDirectoryPermissions(): void
	{
		$instance = new JsonDB();
		$result = $instance->setDirectoryPermissions(0777);

		self::assertSame($instance, $result);
	}

	public function testSetDirectoryPermissionsAffectsNewDirectories(): void
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			self::markTestSkipped('Test skipped on Windows (chmod behavior differs)');
		}

		$newDir = $this->testDataDir . DIRECTORY_SEPARATOR . 'custom_perms';
		$instance = new JsonDB();
		$instance->setDirectoryPermissions(0700);
		$instance->setDataDirectory($newDir, true);

		$perms = fileperms($newDir) & 0777;
		self::assertSame(0700, $perms);
	}

	// ========================================
	// File Operations Methods Tests
	// ========================================

	public function testWrite(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$data = ['name' => 'John Doe', 'email' => 'john@example.com'];

		$bytesWritten = $instance->write('users.json', $data);

		self::assertGreaterThan(0, $bytesWritten);
		self::assertTrue($instance->exists('users.json'));
	}

	public function testWriteCreatesDataDirectoryIfNotExists(): void
	{
		$newDir = $this->testDataDir . DIRECTORY_SEPARATOR . 'auto_created';
		$instance = new JsonDB($newDir);
		$data = ['test' => 'value'];

		$instance->write('test.json', $data);

		self::assertTrue(is_dir($newDir));
		self::assertTrue($instance->exists('test.json'));
	}

	public function testWriteWithCustomJsonOptions(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$data = ['name' => 'Test', 'unicode' => 'Café'];

		$instance->write('test.json', $data, JSON_UNESCAPED_UNICODE);

		$content = file_get_contents($this->testDataDir . DIRECTORY_SEPARATOR . 'test.json');
		self::assertStringContainsString('Café', $content);
	}

	public function testWriteThrowsExceptionWhenDataCannotBeEncoded(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$data = ['invalid' => "\xB1\x31"];

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Failed to encode data to JSON');

		$instance->write('invalid.json', $data);
	}

	public function testWriteThrowsExceptionForInvalidFilename(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$this->expectException(\InvalidArgumentException::class);

		$instance->write('../escape.json', ['data' => 'test']);
	}

	public function testRead(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$data = ['id' => 1, 'name' => 'John', 'active' => true];
		$instance->write('users.json', $data);

		$result = $instance->read('users.json');

		self::assertSame($data, $result);
	}

	public function testReadReturnsEmptyArrayWhenFileDoesNotExist(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$result = $instance->read('nonexistent.json');

		self::assertSame([], $result);
	}

	public function testReadReturnsEmptyArrayWhenFileIsEmpty(): void
	{
		$instance = new JsonDB($this->testDataDir);
		file_put_contents($this->testDataDir . DIRECTORY_SEPARATOR . 'empty.json', '');

		$result = $instance->read('empty.json');

		self::assertSame([], $result);
	}

	public function testReadThrowsExceptionWhenJsonIsInvalid(): void
	{
		$instance = new JsonDB($this->testDataDir);
		file_put_contents($this->testDataDir . DIRECTORY_SEPARATOR . 'invalid.json', '{invalid json}');

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Invalid JSON in file');

		$instance->read('invalid.json');
	}

	public function testReadThrowsExceptionForInvalidFilename(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$this->expectException(\InvalidArgumentException::class);

		$instance->read('../escape.json');
	}

	public function testExists(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$instance->write('test.json', ['data' => 'value']);

		self::assertTrue($instance->exists('test.json'));
		self::assertFalse($instance->exists('nonexistent.json'));
	}

	public function testExistsThrowsExceptionForInvalidFilename(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$this->expectException(\InvalidArgumentException::class);

		$instance->exists('../escape.json');
	}

	public function testDelete(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$instance->write('test.json', ['data' => 'value']);

		$result = $instance->delete('test.json');

		self::assertTrue($result);
		self::assertFalse($instance->exists('test.json'));
	}

	public function testDeleteReturnsFalseWhenFileDoesNotExist(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$result = $instance->delete('nonexistent.json');

		self::assertFalse($result);
	}

	public function testDeleteThrowsExceptionForInvalidFilename(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$this->expectException(\InvalidArgumentException::class);

		$instance->delete('../escape.json');
	}

	public function testList(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$instance->write('users.json', []);
		$instance->write('products.json', []);
		$instance->write('orders.json', []);

		$files = $instance->list();

		self::assertCount(3, $files);
		self::assertContains('users.json', $files);
		self::assertContains('products.json', $files);
		self::assertContains('orders.json', $files);
	}

	public function testListWithFullPath(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$instance->write('test.json', []);

		$files = $instance->list(true);

		self::assertCount(1, $files);
		self::assertStringContainsString($this->testDataDir, $files[0]);
		self::assertStringEndsWith('test.json', $files[0]);
	}

	public function testListReturnsEmptyArrayWhenNoJsonFiles(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$files = $instance->list();

		self::assertSame([], $files);
	}

	public function testListReturnsEmptyArrayWhenDirectoryDoesNotExist(): void
	{
		$nonExistentDir = $this->testDataDir . DIRECTORY_SEPARATOR . 'nonexistent';
		$instance = new JsonDB($nonExistentDir);

		$files = $instance->list();

		self::assertSame([], $files);
	}

	public function testListIgnoresNonJsonFiles(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$instance->write('valid.json', []);
		file_put_contents($this->testDataDir . DIRECTORY_SEPARATOR . 'text.txt', 'content');
		file_put_contents($this->testDataDir . DIRECTORY_SEPARATOR . 'data.xml', '<xml/>');

		$files = $instance->list();

		self::assertCount(1, $files);
		self::assertContains('valid.json', $files);
	}

	// ========================================
	// Helper Methods Tests
	// ========================================

	public function testGetFilePath(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$path = $instance->getFilePath('users.json');

		self::assertStringStartsWith($this->testDataDir, $path);
		self::assertStringEndsWith('users.json', $path);
	}

	public function testGetFilePathAddsJsonExtensionWhenMissing(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$path = $instance->getFilePath('users');

		self::assertStringEndsWith('.json', $path);
	}

	public function testGetFilePathThrowsExceptionForEmptyFilename(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Filename cannot be empty');

		$instance->getFilePath('');
	}

	public function testGetFilePathThrowsExceptionForPathTraversalWithDoubleDots(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Filename cannot contain parent directory references');

		$instance->getFilePath('../etc/passwd');
	}

	public function testGetFilePathThrowsExceptionForPathTraversalWithBackslash(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Filename cannot contain directory separators');

		$instance->getFilePath('subdir\\file.json');
	}

	public function testGetFilePathThrowsExceptionForPathTraversalWithSlash(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Filename cannot contain directory separators');

		$instance->getFilePath('subdir/file.json');
	}

	public function testGetFilePathThrowsExceptionForAbsolutePath(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$this->expectException(\InvalidArgumentException::class);

		$instance->getFilePath('/etc/passwd.json');
	}

	// ========================================
	// Integration Tests
	// ========================================

	public function testCompleteWorkflow(): void
	{
		$instance = new JsonDB($this->testDataDir);

		// Write data
		$users = [
			['id' => 1, 'name' => 'John Doe'],
			['id' => 2, 'name' => 'Jane Smith'],
		];
		$instance->write('users.json', $users);

		// Read data
		$readUsers = $instance->read('users.json');
		self::assertSame($users, $readUsers);

		// Check existence
		self::assertTrue($instance->exists('users.json'));

		// List files
		$files = $instance->list();
		self::assertContains('users.json', $files);

		// Delete file
		$deleted = $instance->delete('users.json');
		self::assertTrue($deleted);
		self::assertFalse($instance->exists('users.json'));
	}

	public function testMultipleInstancesWithDifferentDirectories(): void
	{
		$dir1 = $this->testDataDir . DIRECTORY_SEPARATOR . 'db1';
		$dir2 = $this->testDataDir . DIRECTORY_SEPARATOR . 'db2';
		mkdir($dir1, 0755, true);
		mkdir($dir2, 0755, true);

		$instance1 = new JsonDB($dir1);
		$instance2 = new JsonDB($dir2);

		$instance1->write('data.json', ['source' => 'db1']);
		$instance2->write('data.json', ['source' => 'db2']);

		$data1 = $instance1->read('data.json');
		$data2 = $instance2->read('data.json');

		self::assertSame('db1', $data1['source']);
		self::assertSame('db2', $data2['source']);
	}

	public function testSymfonyDIUsageScenario(): void
	{
		// Simulate Symfony DI configuration
		$projectDir = $this->testDataDir;
		$dataPath = $projectDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'data';

		// Service instantiation with constructor injection
		$jsonDB = new JsonDB($dataPath);

		// Use the service
		$jsonDB->write('config.json', ['app_name' => 'MyApp', 'version' => '1.0']);
		$config = $jsonDB->read('config.json');

		self::assertSame('MyApp', $config['app_name']);
		self::assertSame('1.0', $config['version']);
	}

	// ========================================
	// Edge Cases Tests
	// ========================================

	public function testWriteAndReadWithComplexDataStructure(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$complexData = [
			'users' => [
				['id' => 1, 'name' => 'John', 'roles' => ['admin', 'user']],
				['id' => 2, 'name' => 'Jane', 'roles' => ['user']],
			],
			'settings' => [
				'enabled' => true,
				'max_users' => 100,
				'features' => ['feature_a', 'feature_b'],
			],
			'metadata' => [
				'created_at' => '2024-01-01 12:00:00',
				'version' => 1.5,
			],
		];

		$instance->write('complex.json', $complexData);
		$result = $instance->read('complex.json');

		self::assertSame($complexData, $result);
	}

	public function testWriteAndReadWithUnicodeCharacters(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$data = [
			'french' => 'Café français',
			'arabic' => 'مرحبا',
			'chinese' => '你好',
			'emoji' => '😀🎉',
		];

		$instance->write('unicode.json', $data);
		$result = $instance->read('unicode.json');

		self::assertSame($data, $result);
	}

	public function testWriteAndReadWithEmptyArray(): void
	{
		$instance = new JsonDB($this->testDataDir);

		$instance->write('empty.json', []);
		$result = $instance->read('empty.json');

		self::assertSame([], $result);
	}

	public function testWriteAndReadWithNumericKeys(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$data = [0 => 'first', 1 => 'second', 2 => 'third'];

		$instance->write('numeric.json', $data);
		$result = $instance->read('numeric.json');

		self::assertSame($data, $result);
	}

	public function testFilenameNormalizationWithoutExtension(): void
	{
		$instance = new JsonDB($this->testDataDir);
		$data = ['test' => 'value'];

		// Write with extension
		$instance->write('test.json', $data);

		// Read without extension (should add .json automatically)
		$result = $instance->read('test');

		self::assertSame($data, $result);
	}

	public function testConcurrentWritesAndReads(): void
	{
		$instance = new JsonDB($this->testDataDir);

		// Simulate concurrent operations
		for ($i = 0; $i < 10; $i++) {
			$instance->write("file{$i}.json", ['index' => $i]);
		}

		for ($i = 0; $i < 10; $i++) {
			$data = $instance->read("file{$i}.json");
			self::assertSame($i, $data['index']);
		}

		$files = $instance->list();
		self::assertCount(10, $files);
	}
}
