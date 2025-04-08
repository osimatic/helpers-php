<?php

namespace Osimatic\Network;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class FTP
{
	private const string START_LOG = 'FTP - ';
	
	public function __construct(
		private LoggerInterface $logger=new NullLogger(),
	) {
	}

	/**
	 * Set the logger to use to log debugging data.
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * @param string $host Retirer slash final et ne doit pas être préfixé par ftp://.
	 * @param string $username
	 * @param string $password
	 * @param int $port
	 * @param int $timeout
	 * @param bool $isSsl
	 * @return \FTP\Connection|null
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
	 * @param FTPParams $ftpParams
	 * @param int $timeout
	 * @param bool $isSsl
	 * @return \FTP\Connection|null
	 */
	public function connectWithParams(FTPParams $ftpParams, int $timeout=2, bool $isSsl=true): ?\FTP\Connection
	{
		return $this->connect($ftpParams->getHost(), $ftpParams->getUserName(), $ftpParams->getPassword(), $ftpParams->getPort(), $timeout, $isSsl);
	}

	/**
	 * Teste si la connexion au serveur FTP est toujours active.
	 * @param \FTP\Connection $ftpConnection
	 * @return boolean true si la connexion est toujours active, false sinon.
	 */
	public function isConnected(\FTP\Connection $ftpConnection): bool
	{
		return ftp_systype($ftpConnection);
	}

	/**
	 * Se connecte du serveur FTP.
	 * @param \FTP\Connection $ftpConnection
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
	 * @param \FTP\Connection $ftpConnection
	 * @return void
	 * @throws \Exception
	 */
	public function checkConnection(\FTP\Connection $ftpConnection): void
	{
		if (!$this->isConnected($ftpConnection)) {
			throw new \Exception('Not connected to server.');
		}
	}

	/**
	 * @param \FTP\Connection $ftpConnection
	 * @param bool $isPassiveMode
	 * @return bool
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string $cmd
	 * @return bool
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
	 * @param \FTP\Connection $ftpConnection
	 * @return bool
	 */
	public function changeParentDirectory(\FTP\Connection $ftpConnection): bool
	{
		//$this->logger->info(self::START_LOG.'Back to parent directory.');
		$this->checkConnection($ftpConnection);

		if (false === ftp_cdup($ftpConnection)) {
			$this->logger->error(self::START_LOG.'Could not go to parent directory.');
			return false;
		}

		return true;
	}

	/**
	 * @param \FTP\Connection $ftpConnection
	 * @param string $directory
	 * @return bool
	 */
	public function changeDirectory(\FTP\Connection $ftpConnection, string $directory): bool
	{
		//$this->logger->info(self::START_LOG.'Change current directory to "'.$directory.'".');
		$this->checkConnection($ftpConnection);

		// Try and change into another directory
		if (false === ftp_chdir($ftpConnection, $directory)) {
			$this->logger->error(self::START_LOG.'Could not change current directory to "'.$this->getAbsolutePath($ftpConnection, $directory).'".');
			return false;
		}

		return true;
	}

	/**
	 * @param \FTP\Connection $ftpConnection
	 * @return string|null
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string $fileName
	 * @return string|null
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string $remoteFileName
	 * @param string $localFileName
	 * @param bool $deleteSiExiste
	 * @param int $mode
	 * @return bool
	 */
	public function upload(\FTP\Connection $ftpConnection, string $remoteFileName, string $localFileName, bool $deleteSiExiste=true, int $mode=FTP_BINARY): bool
	{
		$this->logger->info(self::START_LOG.'Upload local file "'.$localFileName.'" to remote file (FTP server) "'.$remoteFileName.'".');
		$this->checkConnection($ftpConnection);

		$mode = self::getModeTransmission($mode);

		if ($this->fileExist($ftpConnection, $remoteFileName)) {
			if (!$deleteSiExiste) {
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string $localFileName
	 * @param string $remoteFileName
	 * @param int $mode
	 * @return bool
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string|null $dirName
	 * @param bool $includeDirectories
	 * @return string[]|null
	 */
	public function getListFiles(\FTP\Connection $ftpConnection, ?string $dirName=null, bool $includeDirectories=true): ?array
	{
		return $this->getContentDirectory($ftpConnection, $dirName, $includeDirectories);
	}

	/**
	 * @param \FTP\Connection $ftpConnection
	 * @param string|null $dirName
	 * @param bool $includeDirectories
	 * @return string[]|null
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
		$remoteFiles = @ftp_nlist($ftpConnection, $dirName);
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string $dirName
	 * @return bool
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string $oldDirName
	 * @param string $newDirName
	 * @return bool
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string $dirName
	 * @return bool
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string $fileName
	 * @return bool
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string $fileName
	 * @return bool
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string $oldFileName
	 * @param string $newFileName
	 * @return bool
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
	 * @param \FTP\Connection $ftpConnection
	 * @param string $fileName
	 * @return bool
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