<?php

namespace Osimatic\Network;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class FTP
 * Provides FTP/FTPS client functionality for file and directory operations
 */
class FTP
{
	private const string START_LOG = 'FTP - ';

	/**
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 */
	public function __construct(
		private LoggerInterface $logger=new NullLogger(),
	) {
	}

	/**
	 * Sets the logger for error and debugging information.
	 * @param LoggerInterface $logger The PSR-3 logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Connects to an FTP server
	 * @param string $host FTP server hostname (remove trailing slash and must not be prefixed by ftp://)
	 * @param string $username FTP username
	 * @param string $password FTP password
	 * @param int $port FTP port (default: 21)
	 * @param int $timeout connection timeout in seconds (default: 2)
	 * @param bool $isSsl use FTPS (FTP over SSL) if true, plain FTP if false (default: true)
	 * @return \FTP\Connection|null FTP connection resource, null if connection failed
	 */
	public function connect(string $host, string $username, string $password, int $port=21, int $timeout=2, bool $isSsl=true): ?\FTP\Connection
	{
		// Attempt to connect to the remote server
		if ($isSsl) {
			$ftpConnection = ftp_ssl_connect($host, $port, $timeout);
		}
		else {
			$ftpConnection = ftp_connect($host, $port, $timeout);
		}

		if (false === $ftpConnection) {
			$this->logger->error(self::START_LOG.'Could not connect to server "'.$host.'".');
			return null;
		}

		// Attempt to login to the remote server
		if (false === ftp_login($ftpConnection, $username, $password)) {
			$this->logger->error(self::START_LOG.'Could not login as user "'.$username.'" to server "'.$host.'".');
			return null;
		}

		$this->logger->info(self::START_LOG.'Connection to server "'.$host.'" as user "'.$username.'" succeed.');
		return $ftpConnection;
	}

	/**
	 * Connects to an FTP server using FTPParams object
	 * @param FTPParams $ftpParams FTP connection parameters
	 * @param int $timeout connection timeout in seconds (default: 2)
	 * @param bool $isSsl use FTPS (FTP over SSL) if true, plain FTP if false (default: true)
	 * @return \FTP\Connection|null FTP connection resource, null if connection failed
	 */
	public function connectWithParams(FTPParams $ftpParams, int $timeout=2, bool $isSsl=true): ?\FTP\Connection
	{
		return $this->connect($ftpParams->getHost(), $ftpParams->getUserName(), $ftpParams->getPassword(), $ftpParams->getPort(), $timeout, $isSsl);
	}

	/**
	 * Tests if the FTP server connection is still active
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @return boolean true if the connection is still active, false otherwise
	 */
	public function isConnected(\FTP\Connection $ftpConnection): bool
	{
		return ftp_systype($ftpConnection) !== false;
	}

	/**
	 * Disconnects from the FTP server
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @return void
	 */
	public function disconnect(\FTP\Connection $ftpConnection): void
	{
		if (!$this->isConnected($ftpConnection)) {
			return;
		}

		// Attempt to quit to the remote server
		if (false === ftp_close($ftpConnection)) {
			$this->logger->error(self::START_LOG.'Could not disconnect to server.');
		}
	}


	/**
	 * Checks if connected to FTP server, throws exception if not
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @return void
	 * @throws \Exception if not connected to server
	 */
	public function checkConnection(\FTP\Connection $ftpConnection): void
	{
		if (!$this->isConnected($ftpConnection)) {
			throw new \Exception('Not connected to server.');
		}
	}

	/**
	 * Sets passive mode for FTP connection
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param bool $isPassiveMode true to enable passive mode, false to disable (default: true)
	 * @return bool true on success, false on failure
	 * @throws \Exception if not connected to server
	 */
	public function setPassiveMode(\FTP\Connection $ftpConnection, bool $isPassiveMode=true): bool
	{
		$this->logger->info(self::START_LOG.'Set passive mode to '.($isPassiveMode?'true':'false').'.');
		$this->checkConnection($ftpConnection);

		// Try to execute command
		if (false === ftp_pasv($ftpConnection, $isPassiveMode)) {
			$this->logger->error(self::START_LOG.'Could not set passive mode to '.($isPassiveMode?'true':'false').'.');
			return false;
		}

		return true;
	}

	/**
	 * Executes a raw FTP command
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $cmd the FTP command to execute
	 * @return bool true on success, false on failure
	 * @throws \Exception if not connected to server
	 */
	public function executeCommand(\FTP\Connection $ftpConnection, string $cmd): bool
	{
		$this->logger->info(self::START_LOG.'Execute command "'.$cmd.'"');
		$this->checkConnection($ftpConnection);

		// Try to execute command
		if (false === ftp_raw($ftpConnection, $cmd)) {
			$this->logger->error(self::START_LOG.'Could not execute command "'.$cmd.'".');
			return false;
		}

		return true;
	}

	/**
	 * Changes to the parent directory
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @return bool true on success, false on failure
	 * @throws \Exception if not connected to server
	 */
	public function changeParentDirectory(\FTP\Connection $ftpConnection): bool
	{
		$this->checkConnection($ftpConnection);

		if (false === ftp_cdup($ftpConnection)) {
			$this->logger->error(self::START_LOG.'Could not go to parent directory.');
			return false;
		}

		return true;
	}

	/**
	 * Changes the current directory
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $directory the directory to change to
	 * @return bool true on success, false on failure
	 * @throws \Exception if not connected to server
	 */
	public function changeDirectory(\FTP\Connection $ftpConnection, string $directory): bool
	{
		$this->checkConnection($ftpConnection);

		// Try and change into another directory
		if (false === ftp_chdir($ftpConnection, $directory)) {
			$this->logger->error(self::START_LOG.'Could not change current directory to "'.$this->getAbsolutePath($ftpConnection, $directory).'".');
			return false;
		}

		return true;
	}

	/**
	 * Gets the current directory path
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @return string|null current directory path, null on failure
	 */
	public function getCurrentDirectory(\FTP\Connection $ftpConnection): ?string
	{
		if (false === ($currentDir = ftp_pwd($ftpConnection))) {
			$this->logger->error(self::START_LOG.'Could not get current directory.');
			return null;
		}
		
		$this->logger->info(self::START_LOG.'Current directory: '.$currentDir);
		return $currentDir;
	}

	/**
	 * Converts a relative file path to an absolute path
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $fileName file or directory name (relative or absolute)
	 * @return string|null absolute path, null on failure
	 */
	public function getAbsolutePath(\FTP\Connection $ftpConnection, string $fileName): ?string
	{
		if (empty($fileName)) {
			return $this->getCurrentDirectory($ftpConnection);
		}

		if ($fileName[0] !== '/') {
			return $this->getCurrentDirectory($ftpConnection).'/'.$fileName;
		}

		return $fileName;
	}

	/**
	 * Uploads a local file to the FTP server
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $remoteFileName remote file name/path on the FTP server
	 * @param string $localFileName local file name/path to upload
	 * @param bool $deleteIfExists if true, delete existing remote file before upload (default: true)
	 * @param int $mode transfer mode (FTP_BINARY or FTP_ASCII, default: FTP_BINARY)
	 * @return bool true on success, false on failure
	 * @throws \Exception if not connected to server
	 */
	public function upload(\FTP\Connection $ftpConnection, string $remoteFileName, string $localFileName, bool $deleteIfExists=true, int $mode=FTP_BINARY): bool
	{
		$this->logger->info(self::START_LOG.'Upload local file "'.$localFileName.'" to remote file (FTP server) "'.$remoteFileName.'".');
		$this->checkConnection($ftpConnection);

		$mode = self::getModeTransmission($mode);

		if ($this->fileExist($ftpConnection, $remoteFileName)) {
			if (!$deleteIfExists) {
				$this->logger->error(self::START_LOG.'File '.$this->getAbsolutePath($ftpConnection, $remoteFileName).' already exists.');
				return false;
			}

			$this->deleteFile($ftpConnection, $remoteFileName);
		}

		if (false === ftp_put($ftpConnection, $remoteFileName, $localFileName, $mode)) {
			$this->logger->error(self::START_LOG.'Could not upload local file "'.$localFileName.'" to remote file "'.$remoteFileName.'".');
			return false;
		}

		return true;
	}

	/**
	 * Downloads a file from the FTP server to local system
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $localFileName local file name/path to save the downloaded file
	 * @param string $remoteFileName remote file name/path on the FTP server
	 * @param int $mode transfer mode (FTP_BINARY or FTP_ASCII, default: FTP_BINARY)
	 * @return bool true on success, false on failure
	 * @throws \Exception if not connected to server
	 */
	public function download(\FTP\Connection $ftpConnection, string $localFileName, string $remoteFileName, int $mode=FTP_BINARY): bool
	{
		$this->logger->info(self::START_LOG.'Download remote file (FTP server) "'.$remoteFileName.'" to local file "'.$localFileName.'".');
		$this->checkConnection($ftpConnection);

		$mode = self::getModeTransmission($mode);

		if (!$this->fileExist($ftpConnection, $remoteFileName)) {
			$this->logger->error(self::START_LOG.'Remote file '.$this->getAbsolutePath($ftpConnection, $remoteFileName).' does not exist.');
			return false;
		}

		if (false === ftp_get($ftpConnection, $localFileName, $remoteFileName, $mode)) {
			$this->logger->error(self::START_LOG.'Could not download remote file "'.$this->getAbsolutePath($ftpConnection, $remoteFileName).'" to local file "'.$localFileName.'".');
			return false;
		}

		return true;
	}

	private static function getModeTransmission(int $mode): int
	{
		return in_array($mode, [FTP_ASCII, FTP_BINARY], true) ? $mode : FTP_BINARY;
	}

	/**
	 * Gets the list of files in a directory (alias of getContentDirectory)
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string|null $dirName directory name/path (null for current directory)
	 * @param bool $includeDirectories if true, include subdirectories in the list (default: true)
	 * @return string[]|null array of file/directory names, null on failure
	 */
	public function getListFiles(\FTP\Connection $ftpConnection, ?string $dirName=null, bool $includeDirectories=true): ?array
	{
		return $this->getContentDirectory($ftpConnection, $dirName, $includeDirectories);
	}

	/**
	 * Gets the contents of a directory
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string|null $dirName directory name/path (null for current directory)
	 * @param bool $includeDirectories if true, include subdirectories in the list (default: true)
	 * @return string[]|null array of file/directory names, null on failure
	 * @throws \Exception if not connected to server
	 */
	public function getContentDirectory(\FTP\Connection $ftpConnection, ?string $dirName=null, bool $includeDirectories=true): ?array
	{
		$this->checkConnection($ftpConnection);

		$dirName ??= $this->getCurrentDirectory($ftpConnection);

		if (!$this->fileExist($ftpConnection, $dirName)) {
			$this->logger->error(self::START_LOG.'Directory "'.$this->getAbsolutePath($ftpConnection, $dirName).'" does not exist.');
			return null;
		}

		// Get directory content
		$remoteFiles = ftp_nlist($ftpConnection, $dirName);
		if (false === $remoteFiles || !is_array($remoteFiles)) {
			$this->logger->error(self::START_LOG.'Could not list content of directory "'.$this->getAbsolutePath($ftpConnection, $dirName).'".');
			return null;
		}

		$filesList = [];
		foreach ($remoteFiles as $file) {
			if ('.' === $file || '..' === $file) {
				continue;
			}

			if ($includeDirectories || $this->isFile($ftpConnection, $file)) {
				$filesList[] = $file;
			}
		}

		return $filesList;
	}

	/**
	 * Creates a new directory on the FTP server
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $dirName directory name/path to create
	 * @return bool true on success, false on failure
	 * @throws \Exception if not connected to server
	 */
	public function createDir(\FTP\Connection $ftpConnection, string $dirName): bool
	{
		$this->logger->info(self::START_LOG.'Create directory "'.$dirName.'".');
		$this->checkConnection($ftpConnection);

		if ($this->fileExist($ftpConnection, $dirName)) {
			$this->logger->error(self::START_LOG.'Directory '.$this->getAbsolutePath($ftpConnection, $dirName).' already exists.');
			return false;
		}

		// Create directory
		if (false === ftp_mkdir($ftpConnection, $dirName)) {
			$this->logger->error(self::START_LOG.'Could not create directory "'.$this->getAbsolutePath($ftpConnection, $dirName).'".');
			return false;
		}

		// Change the files permissions
		if (false === ftp_site($ftpConnection, 'chmod 0777 ' . $dirName . '/')) {
			$this->logger->error(self::START_LOG.'Could not set permissions to directory "'.$this->getAbsolutePath($ftpConnection, $dirName).'".');
			return false;
		}

		return true;
	}

	/**
	 * Renames a directory on the FTP server
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $oldDirName current directory name/path
	 * @param string $newDirName new directory name/path
	 * @return bool true on success, false on failure
	 * @throws \Exception if not connected to server
	 */
	public function renameDir(\FTP\Connection $ftpConnection, string $oldDirName, string $newDirName): bool
	{
		$this->logger->info(self::START_LOG.'Rename directory "'.$oldDirName.'" to "'.$newDirName.'".');
		$this->checkConnection($ftpConnection);

		if (!$this->fileExist($ftpConnection, $oldDirName)) {
			$this->logger->error(self::START_LOG.'Directory '.$this->getAbsolutePath($ftpConnection, $oldDirName).' does not exist.');
			return false;
		}

		if (false === ftp_rename($ftpConnection, $oldDirName, $newDirName)) {
			$this->logger->error(self::START_LOG.'Could not rename directory "'.$this->getAbsolutePath($ftpConnection, $oldDirName).'".');
			return false;
		}

		return true;
	}

	/**
	 * Deletes a directory and all its contents from the FTP server
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $dirName directory name/path to delete
	 * @return bool true on success, false on failure
	 * @throws \Exception if not connected to server
	 */
	public function deleteDir(\FTP\Connection $ftpConnection, string $dirName): bool
	{
		$this->logger->info(self::START_LOG.'Delete directory "'.$dirName.'".');
		$this->checkConnection($ftpConnection);

		if (!$this->fileExist($ftpConnection, $dirName)) {
			$this->logger->error(self::START_LOG.'Directory '.$this->getAbsolutePath($ftpConnection, $dirName).' does not exist.');
			return false;
		}

		$this->changeDirectory($ftpConnection, $dirName);

		$filesList = $this->getContentDirectory($ftpConnection, $dirName);
		foreach ($filesList as $fileName) {
			if ($this->isFile($ftpConnection, $fileName)) {
				$this->deleteFile($ftpConnection, $fileName);
			}
			else {
				$this->deleteDir($ftpConnection, $fileName);
			}
		}

		$this->changeParentDirectory($ftpConnection);

		if (false === ftp_rmdir($ftpConnection, $dirName)) {
			$this->logger->error(self::START_LOG.'Could not delete directory "'.$this->getAbsolutePath($ftpConnection, $dirName).'".');
			return false;
		}

		return true;
	}

	/**
	 * Checks if the specified path is a file (not a directory)
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $fileName file name/path to check
	 * @return bool true if it's a file, false if it's a directory or doesn't exist
	 * @throws \Exception if not connected to server
	 */
	public function isFile(\FTP\Connection $ftpConnection, string $fileName): bool
	{
		$this->checkConnection($ftpConnection);

		if (!$this->fileExist($ftpConnection, $fileName)) {
			$this->logger->error(self::START_LOG.'File '.$this->getAbsolutePath($ftpConnection, $fileName).' does not exist.');
			return false;
		}

		if ('/' === $fileName || '.' === $fileName || '..' === $fileName) {
			return false;
		}

		if (ftp_size($ftpConnection, $fileName) === -1) {
			return false; // Is directory
		}

		return true; // Is file
	}

	/**
	 * Checks if a file or directory exists on the FTP server
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $fileName file or directory name/path to check
	 * @return bool true if exists, false otherwise
	 * @throws \Exception if not connected to server
	 */
	public function fileExist(\FTP\Connection $ftpConnection, string $fileName): bool
	{
		$this->checkConnection($ftpConnection);

		$dirFileName = dirname($fileName);
		$nameFileName = basename($fileName);

		if ('/' === $fileName || '.' === $fileName || '..' === $fileName) {
			return true;
		}

		if (false === ($files = ftp_nlist($ftpConnection, $dirFileName))) {
			return false;
		}

		return in_array($nameFileName, $files, true);
	}

	/**
	 * Renames a file on the FTP server
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $oldFileName current file name/path
	 * @param string $newFileName new file name/path
	 * @return bool true on success, false on failure
	 * @throws \Exception if not connected to server
	 */
	public function renameFile(\FTP\Connection $ftpConnection, string $oldFileName, string $newFileName): bool
	{
		$this->logger->info(self::START_LOG.'Rename file "'.$oldFileName.'" to "'.$newFileName.'".');
		$this->checkConnection($ftpConnection);

		if (!$this->fileExist($ftpConnection, $oldFileName)) {
			$this->logger->error(self::START_LOG.'File "'.$this->getAbsolutePath($ftpConnection, $oldFileName).'" does not exist.');
			return false;
		}

		if (false === ftp_rename($ftpConnection, $oldFileName, $newFileName)) {
			$this->logger->error(self::START_LOG.'Could not rename file "'.$this->getAbsolutePath($ftpConnection, $oldFileName).'".');
			return false;
		}

		return true;
	}

	/**
	 * Deletes a file from the FTP server
	 * @param \FTP\Connection $ftpConnection FTP connection resource
	 * @param string $fileName file name/path to delete
	 * @return bool true on success, false on failure
	 * @throws \Exception if not connected to server
	 */
	public function deleteFile(\FTP\Connection $ftpConnection, string $fileName): bool
	{
		$this->logger->info(self::START_LOG.'Delete file "'.$fileName.'".');
		$this->checkConnection($ftpConnection);

		if (!$this->fileExist($ftpConnection, $fileName)) {
			$this->logger->error(self::START_LOG.'File "'.$this->getAbsolutePath($ftpConnection, $fileName).'" does not exist.');
			return false;
		}

		if (false === ftp_delete($ftpConnection, $fileName)) {
			$this->logger->error(self::START_LOG.'Could not delete file "'.$this->getAbsolutePath($ftpConnection, $fileName).'".');
			return false;
		}

		return true;
	}

}