<?php

namespace Osimatic\FileSystem;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Utility class for file operations including upload handling, validation, output and MIME type detection.
 * Provides comprehensive methods for working with files in web applications, including:
 * - Base64 data handling
 * - File upload processing from HTTP requests
 * - File validation with extension and MIME type checking
 * - File output to browser (download or inline display)
 * - MIME type detection and conversion
 * - File extension management
 */
class File
{
	// ========== Base64 Data Handling ==========

	/**
	 * Extracts and decodes binary data from a base64-encoded string.
	 * Handles data URIs (e.g., "data:image/png;base64,iVBORw0...") and validates the base64 encoding.
	 * @param string $data The base64-encoded data string, optionally with data URI prefix
	 * @return string|null The decoded binary data, or null if decoding fails or data is invalid
	 */
	public static function getDataFromBase64Data(string $data): ?string
	{
		if (str_contains($data, 'base64,')) {
			$data = explode('base64,', $data)[1] ?? '';
		}
		// Validate base64 with strict mode
		if (false === ($decoded = base64_decode($data, true)) || empty($decoded)) {
			return null;
		}
		// Verify the decoded data can be re-encoded to the same value
		if (base64_encode($decoded) !== $data) {
			return null;
		}
		return $decoded;
	}

	/**
	 * Detects the MIME type from base64-encoded data.
	 * First attempts to extract MIME type from data URI prefix (e.g., "data:image/png;base64,...").
	 * Falls back to file signature detection by analyzing the first bytes of the decoded data.
	 * @see https://en.wikipedia.org/wiki/List_of_file_signatures
	 * @param string $data The base64-encoded data string, optionally with data URI prefix
	 * @return string|null The detected MIME type, or null if detection fails
	 */
	public static function getMimeTypeFromBase64Data(string $data): ?string
	{
		if (str_contains($data, 'base64,')) {
			if (empty($fileInfos = explode('base64,', $data)[0] ?? '')) {
				return null;
			}
			if (str_starts_with($fileInfos, 'data:') && str_ends_with($fileInfos, ';')) {
				return substr($fileInfos, 5, -1);
			}

			if (empty($data = explode('base64,', $data)[1] ?? '')) {
				return null;
			}
		}

		// MIME type detection by file signature
		/** @var array $fileSignatures */
		$fileSignatures = [
			[["\x66\x74\x79\x70\x33\x67"], \Osimatic\Media\Video::_3GPP_MIME_TYPES[0]], // 3GPP
			[["\x42\x4D"], \Osimatic\Media\Image::BMP_MIME_TYPES[0]], // Bitmap
			[[""], 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'], // Excel
			[[""], 'application/vnd.ms-excel'], // Excel 97-2003
			[["\x4D\x5A"], 'application/octet-stream'], // Exe (Windows)
			[["\x47\x49\x46\x38\x37\x61", "\x47\x49\x46\x38\x39\x61"], \Osimatic\Media\Image::GIF_MIME_TYPES[0]], // GIF
			[["\xFF\xD8\xFF"], \Osimatic\Media\Image::JPG_MIME_TYPES[0]], // JPEG
			[["\x66\x74\x79\x70\x69\x73\x6F\x6D", "\x66\x74\x79\x70\x4D\x53\x4E\x56"], \Osimatic\Media\Video::MP4_MIME_TYPES[0]], // MP4
			[[""], 'application/vnd.oasis.opendocument.presentationn'], // Open Document Presentation
			[[""], 'application/vnd.oasis.opendocument.spreadsheet'], // Open Document Spreadhseet
			[[""], 'application/vnd.oasis.opendocument.text'], // Open Document Text
			[[""], 'application/vnd.ms-outlook'], // Outlook Message
			[["\x25\x50\x44\x46\x2D"], \Osimatic\Text\PDF::MIME_TYPES[0]], // PDF
			[["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"], \Osimatic\Media\Image::PNG_MIME_TYPES[0]], // PNG
			[[""], 'application/vnd.openxmlformats-officedocument.presentationml.presentation'], // PowerPoint
			[[""], 'application/vnd.ms-powerpoint'], // Powerpoint 97-2003
			[["\x71\x74\x20\x20"], \Osimatic\Media\Video::QUICKTIME_MIME_TYPES[0]], // QuickTime
			[["\x7B\x5C\x72\x74\x66\x31"], 'application/rtf'], // Rich Text Format
			[["\x49\x49\x2A\x00", "\x4D\x4D\x00\x2A"], \Osimatic\Media\Image::TIFF_MIME_TYPES[0]], // TIFF
			[[""], 'application/vnd.visio'], // Visio
			[[""], 'application/vnd.visio'], // Visio 97-2003
			[["\x52\x49\x46\x46", "\x57\x45\x42\x50"], \Osimatic\Media\Image::WEBP_MIME_TYPES[0]], // Webp
			[[""], 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], // Word
			[[""], 'application/msword'], // Word 97-2003
			[[""], 'application/vnd.ms-xpsdocument'], // Xps
			[["\x50\x4B\x03\x04"], ZipArchive::MIME_TYPES[0]], // Zip
		];

		foreach ($fileSignatures as [$hexList, $mimeType]) {
			foreach (array_filter($hexList) as $hex) {
				if (str_starts_with($data, base64_encode($hex))) {
					return $mimeType;
				}
			}
		}

		return null;
	}

	/**
	 * Gets the file extension from an uploaded file (InputFile or Symfony UploadedFile).
	 * @param InputFile|UploadedFile $uploadedFile The uploaded file object
	 * @return string|null The file extension (without dot), or null if not available
	 */
	public static function getExtensionOfUploadedFile(InputFile|UploadedFile $uploadedFile): ?string
	{
		return is_a($uploadedFile, InputFile::class) ? $uploadedFile->getExtension() : $uploadedFile->getClientOriginalExtension();
	}

	// ========== File Upload Processing ==========

	/**
	 * Validates an uploaded file against allowed format specifications.
	 * Uses specialized check methods for common formats (PDF, CSV, images, audio, video, ZIP).
	 * @param UploadedFile $uploadedFile The Symfony UploadedFile to validate
	 * @param string[] $allowedFormats Array of allowed format names (e.g., ['pdf', 'jpg', 'mp3'])
	 * @return bool True if file matches one of the allowed formats, false otherwise
	 */
	private static function checkAllowedFormat(UploadedFile $uploadedFile, array $allowedFormats): bool
	{
		$allowedFormats = array_map(mb_strtolower(...), $allowedFormats);

		$formatWithCheckCallable = [
			'pdf' => \Osimatic\Text\PDF::checkFile(...),
			'csv' => \Osimatic\Text\CSV::checkFile(...),
			'image' => \Osimatic\Media\Image::checkFile(...),
			'jpg' => \Osimatic\Media\Image::checkJpgFile(...),
			'png' => \Osimatic\Media\Image::checkPngFile(...),
			'audio' => \Osimatic\Media\Audio::checkFile(...),
			'mp3' => \Osimatic\Media\Audio::checkMp3File(...),
			'wav' => \Osimatic\Media\Audio::checkWavFile(...),
			'video' => \Osimatic\Media\Video::checkFile(...),
			'mp4' => \Osimatic\Media\Video::checkMp4File(...),
			'avi' => \Osimatic\Media\Video::checkAviFile(...),
			'mpg' => \Osimatic\Media\Video::checkMpgFile(...),
			'wmv' => \Osimatic\Media\Video::checkWmvFile(...),
			'zip' => ZipArchive::checkFile(...),
		];

		foreach ($formatWithCheckCallable as $format => $callable) {
			if (in_array($format, $allowedFormats, true) && $callable($uploadedFile->getRealPath(), $uploadedFile->getClientOriginalName())) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Extracts and validates an uploaded file from an HTTP request.
	 * Handles two types of uploads:
	 * 1. Traditional form file upload (multipart/form-data)
	 * 2. Base64-encoded data sent as POST parameter
	 * @param Request $request The Symfony HTTP request object
	 * @param string $inputFileName The form field name for file upload
	 * @param string $inputFileDataName The POST parameter name for base64 data
	 * @param string[] $allowedFormats Optional array of allowed formats (e.g., ['pdf', 'jpg'])
	 * @param LoggerInterface|null $logger Optional PSR-3 logger for debugging
	 * @return InputFile|UploadedFile|null The uploaded file object, or null if not found/invalid
	 */
	public static function getUploadedFileFromRequest(Request $request, string $inputFileName, string $inputFileDataName, array $allowedFormats=[], ?LoggerInterface $logger=null): InputFile|UploadedFile|null
	{
		if (!empty($data = $request->get($inputFileDataName))) {
			$logger?->info('Uploaded file from base64 content.');
			if (null === ($uploadedFileData = self::getDataFromBase64Data($data))) {
				$logger?->info('Decode base64 file content failed.');
				return null;
			}
			return new InputFile(data: $uploadedFileData, mimeType: self::getMimeTypeFromBase64Data($data), base64EncodedData: $data);
		}

		/** @var UploadedFile $uploadedFile */
		if (!empty($uploadedFile = $request->files->get($inputFileName)) && $uploadedFile->getSize() !== 0) {
			$logger?->info('Uploaded file from form.');
			if (UPLOAD_ERR_OK !== $uploadedFile->getError()) {
				$logger?->info('Upload failed with error code '.$uploadedFile->getError().'. Error message : '.$uploadedFile->getErrorMessage());
				return null;
			}

			if (!empty($allowedFormats) && !self::checkAllowedFormat($uploadedFile, $allowedFormats)) {
				$logger?->info('Uploaded file with invalid format.');
				return null;
			}

			$logger?->info('Uploaded file size: '.$uploadedFile->getSize());
			return $uploadedFile;
		}

		$logger?->info('Uploaded file not found in request.');
		return null;
	}

	/**
	 * Moves an uploaded file to its final destination.
	 * Handles both traditional uploads and InputFile objects containing binary data.
	 * Creates necessary directories and removes existing file at destination.
	 * @param InputFile|UploadedFile $uploadedFile The uploaded file to move
	 * @param string $filePath The destination file path
	 * @param LoggerInterface|null $logger Optional PSR-3 logger for error logging
	 * @return bool True if file was moved successfully, false otherwise
	 */
	public static function moveUploadedFile(InputFile|UploadedFile $uploadedFile, string $filePath, ?LoggerInterface $logger=null): bool
	{
		FileSystem::createDirectories($filePath);

		if (file_exists($filePath)) {
			unlink($filePath);
		}

		$tmpUploadedFileName = is_a($uploadedFile, InputFile::class) ? $uploadedFile->getOriginalFileName() : $filePath;
		if (!empty($tmpUploadedFileName)) {
			if (false === move_uploaded_file((is_a($uploadedFile, InputFile::class) ? $uploadedFile->getUploadedFilePath() : $uploadedFile->getPathname()), $filePath)) {
				$logger?->error('Failed to move uploaded file to "' . $filePath . '"');
				return false;
			}
			return true;
		}

		if (is_a($uploadedFile, InputFile::class) && !empty($uploadedFile->getData())) {
			if (false === file_put_contents($filePath, $uploadedFile->getData())) {
				$logger?->error('Failed to write file "' . $filePath . '" (data size: ' . strlen($uploadedFile->getData()) . ' bytes)');
				return false;
			}
			return true;
		}

		return false;
	}

	// ========== File Validation ==========

	/**
	 * Validates a file based on its extension and MIME type.
	 * Checks if the file exists, has an allowed extension, and matches one of the allowed MIME types.
	 * @param string $realPath The real path to the file on the server
	 * @param string $clientOriginalName The original filename from the client (used to extract extension)
	 * @param array|null $extensionsAllowed Optional array of allowed extensions (e.g., ['.pdf', '.jpg'])
	 * @param array|null $mimeTypesAllowed Optional array of allowed MIME types (e.g., ['application/pdf', 'image/jpeg'])
	 * @return bool True if file passes all validation checks, false otherwise
	 */
	public static function check(string $realPath, string $clientOriginalName, ?array $extensionsAllowed=null, ?array $mimeTypesAllowed=null): bool
	{
		if (empty($realPath) || !file_exists($realPath)) {
			return false;
		}

		$extension = mb_strtolower('.'.pathinfo($clientOriginalName, PATHINFO_EXTENSION));
		if (empty($extension) || (!empty($extensionsAllowed) && !in_array($extension, $extensionsAllowed, true))) {
			return false;
		}

		if (!empty($mimeTypesAllowed)) {
			$fileType = mime_content_type($realPath);
			if (!in_array($fileType, $mimeTypesAllowed, true)) {
				return false;
			}
		}

		return true;
	}

	// ========== File Extension Management ==========

	/**
	 * Returns the file extension from a file path.
	 * Handles common double extensions like .tar.gz, .tar.bz2, etc.
	 * @param string $filePath The file path or filename
	 * @return string The file extension (without dot). May be a double extension like 'tar.gz'
	 */
	public static function getExtension(string $filePath): string
	{
		$filename = basename($filePath);

		// List of common double extensions
		$doubleExtensions = [
			'tar.gz', 'tar.bz2', 'tar.xz', 'tar.z', 'tar.lz',
			'tar.zst', 'tar.lzma', 'tar.lzo', 'tar.bz'
		];

		// Check if file has a double extension
		foreach ($doubleExtensions as $doubleExt) {
			if (str_ends_with(mb_strtolower($filename), '.' . $doubleExt)) {
				return $doubleExt;
			}
		}

		// Otherwise return simple extension
		return pathinfo($filePath, PATHINFO_EXTENSION);
	}

	/**
	 * Replaces the file extension in a filename or path.
	 * Automatically adds a dot before the new extension if not present.
	 * Preserves the directory path and handles both Unix (/) and Windows (\) separators.
	 * @param string $filename The filename or full path whose extension should be replaced
	 * @param string $newExtension The new extension (with or without leading dot)
	 * @return string The filename/path with the new extension
	 */
	public static function replaceExtension(string $filename, string $newExtension): string
	{
		$info = pathinfo($filename);
		$dirname = $info['dirname'] ?? '';

		// Detect the separator used in the original path
		$separator = str_contains($filename, '/') ? '/' : DIRECTORY_SEPARATOR;

		// pathinfo() returns '.' for files without path, we should not include it
		$prefix = ($dirname && $dirname !== '.') ? $dirname . $separator : '';
		return $prefix . $info['filename'] . (!str_starts_with($newExtension, '.') ? '.' : '') . $newExtension;
	}

	// ========== File Output ==========

	/**
	 * Sends a file to the client browser for inline viewing.
	 * Sets appropriate headers for displaying the file in the browser (not forcing download).
	 * No output should be performed before or after calling this function.
	 * @param string $filePath The path to the file to output
	 * @param string|null $fileName Optional name for the file (defaults to basename)
	 * @param string|null $mimeType Optional MIME type (auto-detected if not provided)
	 */
	public static function output(string $filePath, ?string $fileName=null, ?string $mimeType=null): void
	{
		self::_output($filePath, $fileName, false, $mimeType, null, true);
	}

	/**
	 * Sends a file to the client browser for inline viewing (using OutputFile object).
	 * Sets appropriate headers for displaying the file in the browser (not forcing download).
	 * No output should be performed before or after calling this function.
	 * @param OutputFile $file The OutputFile object containing file information
	 * @param string|null $mimeType Optional MIME type (auto-detected if not provided)
	 */
	public static function outputFile(OutputFile $file, ?string $mimeType=null): void
	{
		if (null === $file->getFilePath()) {
			return;
		}

		self::_output($file->getFilePath(), $file->getFileName(), false, $mimeType, null, true);
	}

	/**
	 * Forces download of a file to the client browser.
	 * Sets appropriate headers to force browser to download the file instead of displaying it inline.
	 * No output should be performed before or after calling this function.
	 * @param string $filePath The path to the file to download
	 * @param string|null $fileName Optional name for the downloaded file (defaults to basename)
	 * @param string|null $transferEncoding Optional transfer encoding (defaults to 'binary')
	 */
	public static function download(string $filePath, ?string $fileName=null, ?string $transferEncoding=null): void
	{
		self::_output($filePath, $fileName, true, null, $transferEncoding, true);
	}

	/**
	 * Forces download of a file to the client browser (using OutputFile object).
	 * Sets appropriate headers to force browser to download the file instead of displaying it inline.
	 * No output should be performed before or after calling this function.
	 * @param OutputFile $file The OutputFile object containing file information
	 * @param string|null $transferEncoding Optional transfer encoding (defaults to 'binary')
	 */
	public static function downloadFile(OutputFile $file, ?string $transferEncoding=null): void
	{
		if (null === $file->getFilePath()) {
			return;
		}

		self::_output($file->getFilePath(), $file->getFileName(), true, null, $transferEncoding, true);
	}

	/**
	 * Creates a Symfony HTTP Response object for a file.
	 * Use this method when working with Symfony framework to return file responses.
	 * @param string $filePath The path to the file
	 * @param string|null $fileName Optional name for the file (defaults to basename)
	 * @param bool $forceDownload Whether to force download (true) or allow inline viewing (false)
	 * @param string|null $mimeType Optional MIME type (auto-detected if not provided, ignored if forceDownload=true)
	 * @param string|null $transferEncoding Optional transfer encoding (defaults to 'binary' if forceDownload=true)
	 * @return Response|null The HTTP response object, or null if file not found
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null, bool $forceDownload=true, ?string $mimeType=null, ?string $transferEncoding=null): ?Response
	{
		return self::_output($filePath, $fileName, $forceDownload, $mimeType, $transferEncoding, false);
	}

	/**
	 * Internal method for outputting files to the browser or creating HTTP responses.
	 * Handles both direct output and Symfony Response creation.
	 * Sets appropriate headers based on whether download is forced or inline viewing is allowed.
	 * @param string $filePath The path to the file
	 * @param string|null $fileName Optional name for the file (defaults to basename)
	 * @param bool $forceDownload Whether to force download (true) or allow inline viewing (false)
	 * @param string|null $mimeType Optional MIME type (auto-detected if not provided)
	 * @param string|null $transferEncoding Optional transfer encoding (defaults to 'binary' if forceDownload=true)
	 * @param bool $sendResponse Whether to send response directly (true) or return Response object (false)
	 * @return Response|null The HTTP response object if sendResponse=false, null otherwise
	 */
	private static function _output(string $filePath, ?string $fileName=null, bool $forceDownload=true, ?string $mimeType=null, ?string $transferEncoding=null, bool $sendResponse=true): ?Response
	{
		if (!file_exists($filePath)) {
			if (!$sendResponse) {
				return new Response('file_not_found', Response::HTTP_BAD_REQUEST);
			}
			return null;
		}

		$headers = [
			'Content-Disposition' => 'attachment; filename="'.($fileName ?? basename($filePath)).'"',
			'Content-Length' => filesize($filePath),
		];

		if ($forceDownload) {
			$headers['Content-Type'] = 'application/force-download';
			$headers['Content-Transfer-Encoding'] = $transferEncoding ?? 'binary';
			$headers['Content-Description'] = 'File Transfer';
			$headers['Pragma'] = 'no-cache'; //header('Pragma: public')
			$headers['Cache-Control'] = 'must-revalidate, post-check=0, pre-check=0, public';
			$headers['Expires'] = '0';
		}
		else {
			$headers['Content-Type'] = $mimeType ?? self::getMimeTypeForFile($filePath);
		}

		if (!$sendResponse) {
			/*$response = new BinaryFileResponse($filePath);
			$response->headers->set('Content-Type', 'application/force-download');
			$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_ATTACHMENT,
				basename($filePath)
			);
			return $response;*/

			return new Response(file_get_contents($filePath), 200, $headers);
		}

		if (headers_sent()) {
			return null;
		}

		foreach ($headers as $key => $value) {
			header($key.': '.$value);
		}
		readfile($filePath);
		exit();
	}

	// ========== Utility Methods ==========

	/**
	 * Formats a file size in bytes to a human-readable string with appropriate unit.
	 * Automatically selects the appropriate unit (B, KB, MB, GB, TB) based on the size.
	 * Units are localized based on the current locale (e.g., 'o', 'Ko', 'Mo' for French).
	 * @param float $bytes The file size in bytes
	 * @param int $numberOfDecimalPlaces The number of decimal places to display (default: 2)
	 * @return string The formatted size string (e.g., "1.5 MB", "2.34 GB")
	 */
	public static function formatSize(float $bytes, int $numberOfDecimalPlaces=2): string
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];
		switch (strtoupper(substr(\Locale::getDefault(), 0, 2))) {
			case 'FR': $units = ['o', 'Ko', 'Mo', 'Go', 'To']; break;
		}

		$b = $bytes;

		// Handle negative file sizes
		if ($b > 0) {
			$e = (int)(log($b,1024));
			// If unit is not available, return in TB
			if (isset($units[$e]) === false) {
				$e = 4;
			}
			$b = $b/pow(1024,$e);
		}
		else {
			$b = 0;
			$e = 0;
		}
		$format = '%.'.$numberOfDecimalPlaces.'f';
		$float = sprintf($format, $b);

		return $float.' '.$units[$e];
	}

	// ========== MIME Type Detection and Conversion ==========

	/**
	 * Gets the MIME type corresponding to a file extension.
	 * Searches through the extension/MIME type mapping to find the appropriate MIME type.
	 * @param string $extension The file extension (with or without leading dot, e.g., 'jpg' or '.jpg')
	 * @param array|null $extensionsAndMimeTypes Optional custom mapping (uses default if not provided)
	 * @return string|null The MIME type (e.g., 'image/jpeg'), or null if extension not found
	 */
	public static function getMimeTypeFromExtension(string $extension, ?array $extensionsAndMimeTypes=null): ?string
	{
		$extension = mb_strtolower(str_starts_with($extension, '.') ? substr($extension, 1) : $extension);

		$extensionsAndMimeTypes = (null !== $extensionsAndMimeTypes) ? self::formatExtensionsAndMimeTypes($extensionsAndMimeTypes) : self::getExtensionsAndMimeTypes();
		foreach ($extensionsAndMimeTypes as [$extensions, $mimeTypes]) {
			if (in_array($extension, $extensions, true)) {
				return $mimeTypes[0];
			}
		}

		return null;
	}

	/**
	 * Gets the file extension corresponding to a MIME type.
	 * Searches through the extension/MIME type mapping to find the appropriate extension.
	 * @param string $mimeType The MIME type (e.g., 'image/jpeg', 'application/pdf')
	 * @param array|null $extensionsAndMimeTypes Optional custom mapping (uses default if not provided)
	 * @return string|null The file extension without dot (e.g., 'jpg', 'pdf'), or null if MIME type not found
	 */
	public static function getExtensionFromMimeType(string $mimeType, ?array $extensionsAndMimeTypes=null): ?string
	{
		$mimeType = mb_strtolower($mimeType);

		$extensionsAndMimeTypes = (null !== $extensionsAndMimeTypes) ? self::formatExtensionsAndMimeTypes($extensionsAndMimeTypes) : self::getExtensionsAndMimeTypes();
		foreach ($extensionsAndMimeTypes as [$extensions, $mimeTypes]) {
			if (in_array($mimeType, $mimeTypes, true)) {
				return $extensions[0];
			}
		}

		return null;
	}

	/**
	 * Formats and normalizes an extension/MIME type mapping array.
	 * Removes leading dots from extensions to ensure consistent formatting.
	 * @param array $extensionsAndMimeTypes The raw mapping array
	 * @return array The formatted mapping array with normalized extensions
	 */
	private static function formatExtensionsAndMimeTypes(array $extensionsAndMimeTypes): array
	{
		return array_values(array_map(static fn($extensionsAndMimeTypesOfFormat) => [array_map(static fn($extension) => str_starts_with($extension, '.') ? substr($extension, 1) : $extension, $extensionsAndMimeTypesOfFormat[0]), $extensionsAndMimeTypesOfFormat[1]], $extensionsAndMimeTypes));
	}

	/**
	 * Gets the complete mapping of file extensions to MIME types.
	 * Merges mappings from media classes (Image, Audio, Video) with additional common file types.
	 * Each entry is an array with two elements: [extensions array, MIME types array].
	 * @return array The complete extension/MIME type mapping array
	 */
	public static function getExtensionsAndMimeTypes(): array
	{
		return array_merge(
			self::formatExtensionsAndMimeTypes(\Osimatic\Media\Image::getExtensionsAndMimeTypes()),
			self::formatExtensionsAndMimeTypes(\Osimatic\Media\Audio::getExtensionsAndMimeTypes()),
			self::formatExtensionsAndMimeTypes(\Osimatic\Media\Video::getExtensionsAndMimeTypes()),
			[
				[['xl'], ['application/excel']],
				[['js'], ['application/javascript']],
				[['hqx'], ['application/mac-binhex40']],
				[['cpt'], ['application/mac-compactpro']],
				[['bin'], ['application/macbinary']],
				[['doc', 'word'], ['application/msword']],
				[['xlsx'], ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
				[['xltx'], ['application/vnd.openxmlformats-officedocument.spreadsheetml.template']],
				[['potx'], ['application/vnd.openxmlformats-officedocument.presentationml.template']],
				[['ppsx'], ['application/vnd.openxmlformats-officedocument.presentationml.slideshow']],
				[['pptx'], ['application/vnd.openxmlformats-officedocument.presentationml.presentation']],
				[['sldx'], ['application/vnd.openxmlformats-officedocument.presentationml.slide']],
				[['docx'], ['application/vnd.openxmlformats-officedocument.wordprocessingml.document']],
				[['dotx'], ['application/vnd.openxmlformats-officedocument.wordprocessingml.template']],
				[['xlam'], ['application/vnd.ms-excel.addin.macroEnabled.12']],
				[['xlsb'], ['application/vnd.ms-excel.sheet.binary.macroEnabled.12']],
				[['eot'], ['application/vnd.ms-fontobject']],
				[['class'], ['application/octet-stream']],
				[['dll'], ['application/octet-stream']],
				[['dms'], ['application/octet-stream']],
				[['exe'], ['application/octet-stream']],
				[['lha'], ['application/octet-stream']],
				[['lzh'], ['application/octet-stream']],
				[['psd'], ['application/octet-stream']],
				[['sea'], ['application/octet-stream']],
				[['so'], ['application/octet-stream']],
				[['oda'], ['application/oda']],
				[['pdf'], ['application/pdf']],
				[['ai', 'eps', 'ps'], ['application/postscript']],
				[['smi', 'smil'], ['application/smil']],
				[['mif'], ['application/vnd.mif']],
				[['xls'], ['application/vnd.ms-excel']],
				[['ppt'], ['application/vnd.ms-powerpoint']],
				[['wbxml'],['application/vnd.wap.wbxml']],
				[['wmlc'], ['application/vnd.wap.wmlc']],
				[['dcr', 'dir', 'dxr'], ['application/x-director']],
				[['dvi'], ['application/x-dvi']],
				[['gtar'], ['application/x-gtar']],
				[['php', 'php3', 'php4', 'phtml'], ['application/x-httpd-php']],
				[['phps'], ['application/x-httpd-php-source']],
				[['swf'], ['application/x-shockwave-flash']],
				[['sit'], ['application/x-stuffit']],
				[['tar', 'tgz'], ['application/x-tar']],
				[['xhtml', 'xht'],['application/xhtml+xml']],
				[['zip'], ['application/zip']],
				[['bz'], ['application/x-bzip']],
				[['bz2'], ['application/x-bzip2']],
				[['mid', 'midi'], ['audio/midi']],
				[['ram', 'rm'], ['audio/x-pn-realaudio']],
				[['rpm'], ['audio/x-pn-realaudio-plugin']],
				[['ra'], ['audio/x-realaudio']],
				[['eml'], ['message/rfc822']],
				[['css'], ['text/css']],
				[['html', 'htm', 'shtml'], ['text/html']],
				[['txt', 'text', 'log'], ['text/plain']],
				[['rtx'], ['text/richtext']],
				[['rtf'], ['text/rtf']],
				[['vcf', 'vcard'], ['text/vcard']],
				[['xml', 'xsl'], ['text/xml']],
				[['csh'], ['application/x-csh']],
				[['csv'], ['text/csv']],
				[['ico'], ['image/x-icon']],
				[['ics'], ['text/calendar']],
				[['jar'], ['application/java-archive']],
				[['json'], ['application/json']],
				[['mpkg'], ['application/vnd.apple.installer+xml']],
				[['odp'], ['application/vnd.oasis.opendocument.presentation']],
				[['ods'], ['application/vnd.oasis.opendocument.spreadsheet']],
				[['odt'], ['application/vnd.oasis.opendocument.text']],
				[['ogx'], ['application/ogg']],
				[['otf'], ['font/otf']],
				[['ttf'], ['font/ttf']],
				[['woff'], ['font/woff']],
				[['woff2'], ['font/woff2']],
				[['rar'], ['application/x-rar-compressed']],
				[['sh'], ['application/x-sh']],
				[['ts'], ['application/typescript']],
				[['vsd'], ['application/vnd.visio']],
				[['xul'], ['application/vnd.mozilla.xul+xml']],
				[['3gp'], ['video/3gpp', 'audio/3gpp']],
				[['3g2'], ['video/3gpp2', 'audio/3gpp2']],
				[['7z'], ['application/x-7z-compressed']],
				[['azw'], ['application/vnd.amazon.ebook']],
				[['rv'], ['video/vnd.rn-realvideo']],
				[['movie'], ['video/x-sgi-movie']],
			]
		);
	}

	/**
	 * Map a file name to a MIME type.
	 * Defaults to 'application/octet-stream', i.e. arbitrary binary data.
	 * @param string $filename A file name or full path, does not need to exist as a file
	 * @return string
	 */
	public static function getMimeTypeForFile(string $filename): string
	{
		// In case the path is a Bitly, strip any query string before getting extension
		$qpos = strpos($filename, '?');
		if (false !== $qpos) {
			$filename = substr($filename, 0, $qpos);
		}

		return self::getMimeTypeFromExtension(self::mb_pathinfo($filename, PATHINFO_EXTENSION)) ?? 'application/octet-stream';
	}

	/**
	 * Multi-byte-safe pathinfo replacement.
	 * Drop-in replacement for pathinfo(), but multibyte-safe, cross-platform-safe, old-version-safe.
	 * Works similarly to the one in PHP >= 5.2.0
	 * @link http://www.php.net/manual/en/function.pathinfo.php#107461
	 * @param string $path A filename or path, does not need to exist as a file
	 * @param int|null $options Either a PATHINFO_* constant
	 * @return string|array
	 */
	public static function mb_pathinfo(string $path, ?int $options = null): array|string
	{
		$ret = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
		$pathinfo = [];
		if (preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $path, $pathinfo)) {
			if (array_key_exists(1, $pathinfo)) {
				$ret['dirname'] = $pathinfo[1];
			}
			if (array_key_exists(2, $pathinfo)) {
				$ret['basename'] = $pathinfo[2];
			}
			if (array_key_exists(5, $pathinfo)) {
				$ret['extension'] = $pathinfo[5];
			}
			if (array_key_exists(3, $pathinfo)) {
				$ret['filename'] = $pathinfo[3];
			}
		}
		return match ($options) {
			PATHINFO_DIRNAME => $ret['dirname'],
			PATHINFO_BASENAME => $ret['basename'],
			PATHINFO_EXTENSION => $ret['extension'],
			PATHINFO_FILENAME => $ret['filename'],
			default => $ret,
		};
	}

}