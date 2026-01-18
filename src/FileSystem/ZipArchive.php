<?php

namespace Osimatic\FileSystem;

use Symfony\Component\HttpFoundation\Response;

/**
 * Utility class for ZIP archive operations.
 * Provides methods for:
 * - ZIP file validation
 * - ZIP archive creation from files or string content
 * - ZIP file output to browser (download)
 * - HTTP response generation for ZIP files
 */
class ZipArchive
{
	/**
	 * ZIP file extension constant.
	 */
	public const string FILE_EXTENSION = '.zip';

	/**
	 * Accepted MIME types for ZIP files.
	 */
	public const array MIME_TYPES = [
		'application/zip',
		'application/x-zip',
		'application/x-zip-compressed',
		'application/x-rar',
		'application/x-rar-compressed',
		'application/octet-stream',
	];

	// ========== Validation ==========

	/**
	 * Checks if a file is a valid ZIP file based on extension and MIME type.
	 * @param string $filePath The path to the file to check
	 * @param string $clientOriginalName The original file name from client
	 * @return bool True if the file is a valid ZIP file, false otherwise
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return File::check($filePath, $clientOriginalName, [self::FILE_EXTENSION], self::MIME_TYPES);
	}

	// ========== Output ==========

	/**
	 * Sends a ZIP file to the client browser for download.
	 * No output should be performed before or after calling this function.
	 * @param string $filePath The path to the ZIP file to send
	 * @param string|null $fileName Optional filename for the download (defaults to basename)
	 */
	public static function output(string $filePath, ?string $fileName=null): void
	{
		File::download($filePath, $fileName);
	}

	/**
	 * Sends a ZIP file to the client browser for download.
	 * No output should be performed before or after calling this function.
	 * @param OutputFile $file The OutputFile object containing the file to send
	 */
	public static function outputFile(OutputFile $file): void
	{
		File::downloadFile($file);
	}

	/**
	 * Creates an HTTP response for a ZIP file.
	 * @param string $filePath The path to the ZIP file
	 * @param string|null $fileName Optional filename for the response
	 * @return Response The HTTP response object
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): Response
	{
		return File::getHttpResponse($filePath, $fileName, true);
	}

	// ========== Archive Creation ==========

	/**
	 * Creates a ZIP archive containing the list of files passed as parameter.
	 * If the archive already exists, it will be deleted and recreated.
	 * @param string $filePath The path of the archive to create
	 * @param string[] $files An array containing the list of file paths to add to the archive
	 */
	public static function archive(string $filePath, array $files): void
	{
		FileSystem::initializeFile($filePath);

		$zip = new \ZipArchive();
		$zip->open($filePath, \ZipArchive::CREATE);
		foreach ($files as $f) {
			if (file_exists($f)) {
				$zip->addFile($f, basename($f));
			}
		}
		$zip->close();
	}

	/**
	 * Creates a ZIP archive containing the list of OutputFile objects passed as parameter.
	 * If the archive already exists, it will be deleted and recreated.
	 * Uses the display filenames from OutputFile objects for the archive entries.
	 * @param string $filePath The path of the archive to create
	 * @param OutputFile[] $files An array of OutputFile objects to add to the archive
	 */
	public static function archiveOutputFiles(string $filePath, array $files): void
	{
		FileSystem::initializeFile($filePath);

		$zip = new \ZipArchive();
		$zip->open($filePath, \ZipArchive::CREATE);
		foreach ($files as $outputFile) {
			if (null !== ($currentFilePath = $outputFile->getFilePath()) && file_exists($currentFilePath)) {
				$zip->addFile($currentFilePath, $outputFile->getFileName() ?? basename($currentFilePath));
			}
		}
		$zip->close();
	}

	/**
	 * Creates a ZIP archive from string content.
	 * If the archive already exists, it will be deleted and recreated.
	 * @param string $filePath The path of the archive to create
	 * @param array $contentFiles An associative array where keys are filenames in the archive and values are the file contents
	 */
	public static function archiveFilesFromString(string $filePath, array $contentFiles): void
	{
		FileSystem::initializeFile($filePath);

		$zip = new \ZipArchive();
		$zip->open($filePath, \ZipArchive::CREATE);
		foreach ($contentFiles as $filename => $content) {
			$zip->addFromString($filename, $content);
		}
		$zip->close();
	}

}