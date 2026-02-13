<?php

namespace Osimatic\Data;

/**
 * JsonDB - Simple JSON-based database manager with flexible instantiation.
 * This class provides a lightweight data access layer for storing and retrieving data in JSON format.
 * It is designed to be project-agnostic and contains no business logic.
 *
 * Usage modes:
 * 1. Singleton with default path:
 *    <code>$db = JsonDB::getInstance();</code>
 *
 * 2. Singleton with custom path:
 *    <code>
 *    JsonDB::initialize('/custom/path');
 *    $db = JsonDB::getInstance();
 *    </code>
 *
 * 3. Direct instantiation (e.g., for Symfony DI):
 *    <code>$db = new JsonDB('/custom/path');</code>
 *
 * @link https://www.php.net/manual/en/function.json-encode.php JSON encoding in PHP
 * @link https://en.wikipedia.org/wiki/Singleton_pattern Singleton pattern
 * @link https://symfony.com/doc/current/service_container.html Symfony service container
 * @link https://owasp.org/www-community/attacks/Path_Traversal Path traversal attack prevention
 */
class JsonDB
{
	// ========================================
	// Properties
	// ========================================

	/**
	 * @var self|null Singleton instance
	 */
	private static ?self $instance = null;

	/**
	 * @var string|null Path configured for singleton initialization
	 */
	private static ?string $singletonDataPath = null;

	/**
	 * @var string Path to the data directory where JSON files are stored
	 */
	private string $dataDir;

	/**
	 * @var int Default permissions for created directories
	 */
	private int $dirPermissions = 0755;

	// ========================================
	// Singleton Pattern Methods
	// ========================================

	/**
	 * Constructor - can be used for direct instantiation (e.g., Symfony DI) or singleton pattern.
	 * When used directly, you can specify a custom data directory path.
	 * When used via getInstance(), it will use the path set by initialize() or a default path.
	 *
	 * @param string|null $dataPath Optional path to the data directory. If null, uses a default path relative to the class location.
	 */
	public function __construct(?string $dataPath = null)
	{
		if ($dataPath !== null) {
			$this->dataDir = $dataPath;
		} else {
			$this->dataDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'data';
		}
	}

	/**
	 * Prevents cloning of the singleton instance.
	 *
	 * @return void
	 */
	private function __clone(): void
	{
	}

	/**
	 * Prevents unserialization of the singleton instance.
	 *
	 * @throws \Exception When attempting to unserialize the singleton
	 * @return void
	 */
	public function __wakeup(): void
	{
		throw new \Exception('Cannot unserialize singleton');
	}

	/**
	 * Initializes the singleton instance with a custom data directory path.
	 * This method must be called before the first call to getInstance() to take effect.
	 * Once getInstance() has been called, this method will throw an exception.
	 *
	 * Usage example:
	 * <code>
	 * JsonDB::initialize('/path/to/data');
	 * $db = JsonDB::getInstance();
	 * </code>
	 *
	 * @param string $dataPath Path to the data directory
	 * @throws \RuntimeException If getInstance() has already been called
	 * @return void
	 */
	public static function initialize(string $dataPath): void
	{
		if (self::$instance !== null) {
			throw new \RuntimeException('Cannot initialize JsonDB: getInstance() has already been called. Call initialize() before the first getInstance() call.');
		}
		self::$singletonDataPath = $dataPath;
	}

	/**
	 * Gets the unique instance of JsonDB (singleton pattern).
	 * Creates the instance if it doesn't exist yet.
	 * If initialize() was called before, uses the configured path, otherwise uses the default path.
	 *
	 * Usage examples:
	 * <code>
	 * // With default path
	 * $db = JsonDB::getInstance();
	 *
	 * // With custom path (initialize first)
	 * JsonDB::initialize('/custom/path');
	 * $db = JsonDB::getInstance();
	 * </code>
	 *
	 * @return self The singleton instance
	 */
	public static function getInstance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self(self::$singletonDataPath);
		}
		return self::$instance;
	}

	/**
	 * Resets the singleton instance.
	 * This method is primarily intended for testing purposes to allow re-initialization.
	 * Use with caution in production code.
	 *
	 * @return void
	 */
	public static function resetInstance(): void
	{
		self::$instance = null;
		self::$singletonDataPath = null;
	}

	// ========================================
	// Configuration Methods
	// ========================================

	/**
	 * Sets the data directory path where JSON files will be stored.
	 * Creates the directory if it doesn't exist.
	 *
	 * @param string $path Absolute or relative path to the data directory
	 * @param bool $createIfNotExists Whether to create the directory if it doesn't exist (default: true)
	 * @throws \InvalidArgumentException If the path is invalid or cannot be created
	 * @return self Returns the instance for method chaining
	 */
	public function setDataDirectory(string $path, bool $createIfNotExists = true): self
	{
		if (empty($path)) {
			throw new \InvalidArgumentException('Data directory path cannot be empty');
		}

		// Convert to absolute path if relative
		$absolutePath = realpath($path);
		if ($absolutePath === false) {
			if ($createIfNotExists) {
				// Try to create the directory
				if (!@mkdir($path, $this->dirPermissions, true) && !is_dir($path)) {
					throw new \InvalidArgumentException(sprintf('Unable to create data directory: %s', $path));
				}
				$absolutePath = realpath($path);
			} else {
				throw new \InvalidArgumentException(sprintf('Data directory does not exist: %s', $path));
			}
		}

		if (!is_dir($absolutePath)) {
			throw new \InvalidArgumentException(sprintf('Path is not a directory: %s', $absolutePath));
		}

		if (!is_writable($absolutePath)) {
			throw new \InvalidArgumentException(sprintf('Data directory is not writable: %s', $absolutePath));
		}

		$this->dataDir = $absolutePath;
		return $this;
	}

	/**
	 * Gets the current data directory path.
	 *
	 * @return string The absolute path to the data directory
	 */
	public function getDataDirectory(): string
	{
		return $this->dataDir;
	}

	/**
	 * Gets the current data directory path.
	 *
	 * @deprecated Use getDataDirectory() instead
	 * @return string The absolute path to the data directory
	 */
	public function getDataDir(): string
	{
		return $this->getDataDirectory();
	}

	/**
	 * Sets the permissions for directories created by this class.
	 *
	 * @param int $permissions Unix-style permissions (e.g., 0755)
	 * @return self Returns the instance for method chaining
	 */
	public function setDirectoryPermissions(int $permissions): self
	{
		$this->dirPermissions = $permissions;
		return $this;
	}

	// ========================================
	// File Operations Methods
	// ========================================

	/**
	 * Reads and decodes a JSON file.
	 * Returns an empty array if the file doesn't exist.
	 *
	 * @param string $filename Name of the JSON file (e.g., 'users.json')
	 * @throws \InvalidArgumentException If the filename is invalid or contains path traversal
	 * @throws \RuntimeException If the file cannot be read or contains invalid JSON
	 * @return array The decoded JSON data as an associative array
	 */
	public function read(string $filename): array
	{
		$filepath = $this->getFilePath($filename);

		if (!file_exists($filepath)) {
			return [];
		}

		$content = @file_get_contents($filepath);
		if ($content === false) {
			throw new \RuntimeException(sprintf('Failed to read file: %s', $filename));
		}

		if (empty($content)) {
			return [];
		}

		$data = json_decode($content, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new \RuntimeException(sprintf('Invalid JSON in file %s: %s', $filename, json_last_error_msg()));
		}

		return $data ?? [];
	}

	/**
	 * Encodes and writes data to a JSON file.
	 * Creates the data directory if it doesn't exist.
	 *
	 * @param string $filename Name of the JSON file (e.g., 'users.json')
	 * @param mixed $data Data to encode and write (typically an array)
	 * @param int $options JSON encoding options (default: JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
	 * @throws \InvalidArgumentException If the filename is invalid or data cannot be encoded
	 * @throws \RuntimeException If the file cannot be written
	 * @return int Number of bytes written to the file
	 */
	public function write(string $filename, $data, int $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): int
	{
		$filepath = $this->getFilePath($filename);

		// Ensure data directory exists
		if (!is_dir($this->dataDir)) {
			if (!@mkdir($this->dataDir, $this->dirPermissions, true) && !is_dir($this->dataDir)) {
				throw new \RuntimeException(sprintf('Failed to create data directory: %s', $this->dataDir));
			}
		}

		$jsonContent = json_encode($data, $options);
		if ($jsonContent === false) {
			throw new \InvalidArgumentException(sprintf('Failed to encode data to JSON: %s', json_last_error_msg()));
		}

		$bytesWritten = @file_put_contents($filepath, $jsonContent);
		if ($bytesWritten === false) {
			throw new \RuntimeException(sprintf('Failed to write file: %s', $filename));
		}

		return $bytesWritten;
	}

	/**
	 * Checks if a JSON file exists in the data directory.
	 *
	 * @param string $filename Name of the JSON file (e.g., 'users.json')
	 * @throws \InvalidArgumentException If the filename is invalid or contains path traversal
	 * @return bool True if the file exists, false otherwise
	 */
	public function exists(string $filename): bool
	{
		$filepath = $this->getFilePath($filename);
		return file_exists($filepath);
	}

	/**
	 * Deletes a JSON file from the data directory.
	 *
	 * @param string $filename Name of the JSON file (e.g., 'users.json')
	 * @throws \InvalidArgumentException If the filename is invalid or contains path traversal
	 * @throws \RuntimeException If the file cannot be deleted
	 * @return bool True if the file was successfully deleted, false if it didn't exist
	 */
	public function delete(string $filename): bool
	{
		$filepath = $this->getFilePath($filename);

		if (!file_exists($filepath)) {
			return false;
		}

		if (!@unlink($filepath)) {
			throw new \RuntimeException(sprintf('Failed to delete file: %s', $filename));
		}

		return true;
	}

	/**
	 * Lists all JSON files in the data directory.
	 *
	 * @param bool $fullPath Whether to return full paths (true) or just filenames (false)
	 * @return array Array of JSON filenames or full paths
	 */
	public function list(bool $fullPath = false): array
	{
		if (!is_dir($this->dataDir)) {
			return [];
		}

		$files = glob($this->dataDir . DIRECTORY_SEPARATOR . '*.json');
		if ($files === false) {
			return [];
		}

		if (!$fullPath) {
			$files = array_map('basename', $files);
		}

		return $files;
	}

	// ========================================
	// Helper Methods
	// ========================================

	/**
	 * Gets the full file path for a given filename, with security validation.
	 * Uses File::buildSecurePath() and File::ensureExtension() to ensure security and correct extension.
	 *
	 * @param string $filename Name of the JSON file (extension optional)
	 * @throws \InvalidArgumentException If the filename is invalid or contains path traversal attempts
	 * @return string The validated absolute file path with .json extension
	 * @link https://owasp.org/www-community/attacks/Path_Traversal
	 */
	public function getFilePath(string $filename): string
	{
		if (empty($filename)) {
			throw new \InvalidArgumentException('Filename cannot be empty');
		}

		// Ensure .json extension
		$filename = \Osimatic\FileSystem\File::ensureExtension($filename, 'json');

		// Build secure path (don't require existing directory as write() will create it)
		return \Osimatic\FileSystem\File::buildSecurePath($this->dataDir, $filename, false);
	}
}