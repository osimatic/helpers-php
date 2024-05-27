<?php

namespace Osimatic\FileSystem;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class File
{
	/**
	 * @param string $data
	 * @return string|null
	 */
	public static function getDataFromBase64Data(string $data): ?string
	{
		if (str_contains($data, 'base64,')) {
			$data = explode('base64,', $data)[1] ?? '';
		}
		if (false === ($data = base64_decode($data)) || empty($data)) {
			return null;
		}
		return $data;
	}

	/**
	 * @param string $data
	 * @return string|null
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

		// Détection du mime type par la signature de fichier
		// https://en.wikipedia.org/wiki/List_of_file_signatures

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
			[["\x50\x4B\x03\x04"], \Osimatic\FileSystem\ZipArchive::MIME_TYPES[0]], // Zip
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
	 * @param InputFile|UploadedFile $uploadedFile
	 * @return string|null
	 */
	public static function getExtensionOfUploadedFile(InputFile|UploadedFile $uploadedFile): ?string
	{
		return is_a($uploadedFile, \Osimatic\FileSystem\InputFile::class) ? $uploadedFile->getExtension() : $uploadedFile->getClientOriginalExtension();
	}


	/**
	 * @param UploadedFile $uploadedFile
	 * @param string[] $allowedFormats
	 * @return bool
	 */
	private static function checkAllowedFormat(UploadedFile $uploadedFile, array $allowedFormats): bool
	{
		$allowedFormats = array_map(fn(string $format) => mb_strtolower($format), $allowedFormats);

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
			'zip' => \Osimatic\FileSystem\ZipArchive::checkFile(...),
		];

		foreach ($formatWithCheckCallable as $format => $callable) {
			if (in_array($format, $allowedFormats) && $callable($uploadedFile->getRealPath(), $uploadedFile->getClientOriginalName())) {
				return true;
			}
		}
		return false;
	}


	/**
	 * @param Request $request
	 * @param string $inputFileName
	 * @param string $inputFileDataName
	 * @param string[] $allowedFormats
	 * @param LoggerInterface|null $logger
	 * @return InputFile|UploadedFile|null
	 */
	public static function getUploadedFileFromRequest(Request $request, string $inputFileName, string $inputFileDataName, array $allowedFormats=[], ?LoggerInterface $logger=null): InputFile|UploadedFile|null
	{
		if (!empty($data = $request->get($inputFileDataName))) {
			$logger?->info('Uploaded file from base64 content.');
			if (null === ($uploadedFileData = self::getDataFromBase64Data($data))) {
				$logger?->info('Decode base64 file content failed.');
				return null;
			}
			return new InputFile(null, $uploadedFileData, self::getMimeTypeFromBase64Data($data));
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

			return $uploadedFile;
		}

		$logger?->info('Uploaded file not found in request.');
		return null;
	}

	/**
	 * @param InputFile|UploadedFile $uploadedFile
	 * @param string $filePath
	 * @param LoggerInterface|null $logger
	 * @return bool
	 */
	public static function moveUploadedFile(InputFile|UploadedFile $uploadedFile, string $filePath, ?LoggerInterface $logger=null): bool
	{
		\Osimatic\FileSystem\FileSystem::createDirectories($filePath);

		if (file_exists($filePath)) {
			unlink($filePath);
		}

		$tmpUploadedFileName = is_a($uploadedFile, InputFile::class) ? $uploadedFile->getOriginalFileName() : $filePath;
		if (!empty($tmpUploadedFileName)) {
			if (false === move_uploaded_file((is_a($uploadedFile, InputFile::class) ? $uploadedFile->getUploadedFilePath() : $uploadedFile->getPathname()), $filePath)) {
				$logger?->error('Erreur lors du téléchargement du fichier "' . $filePath . '"');
				return false;
			}
			return true;
		}

		if (is_a($uploadedFile, InputFile::class) && !empty($uploadedFile->getData())) {
			if (false === file_put_contents($filePath, $uploadedFile->getData())) {
				$logger?->error('Erreur lors de l\'écriture du fichier "' . $filePath . '" (taille données : ' . strlen($uploadedFile->getData()) . ')');
				return false;
			}
			return true;
		}

		return false;
	}

	/**
	 * @param string $realPath
	 * @param string $clientOriginalName
	 * @param array $extensionsAllowed
	 * @param array|null $mimeTypesAllowed
	 * @return bool
	 */
	public static function check(string $realPath, string $clientOriginalName, array $extensionsAllowed, ?array $mimeTypesAllowed=null): bool
	{
		if (empty($realPath) || !file_exists($realPath)) {
			return false;
		}

		$extension = mb_strtolower('.'.pathinfo($clientOriginalName, PATHINFO_EXTENSION));
		if (empty($extension) || !in_array($extension, $extensionsAllowed)) {
			return false;
		}

		if (!empty($mimeTypesAllowed)) {
			$fileType = mime_content_type($realPath);
			if (!in_array($fileType, $mimeTypesAllowed)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Retourne l'exension d'un fichier
	 * @param string $filePath
	 * @return string
	 */
	public static function getExtension(string $filePath): string
	{
		return pathinfo($filePath, PATHINFO_EXTENSION);
	}

	/**
	 * @param string $filename
	 * @param string $newExtension
	 * @return string
	 */
	public static function replaceExtension(string $filename, string $newExtension): string
	{
		$info = pathinfo($filename);
		return ($info['dirname'] ? $info['dirname'] . DIRECTORY_SEPARATOR : '') . $info['filename'] . (!str_starts_with($newExtension, '.') ? '.' : '') . $newExtension;
	}

	/**
	 * Envoi un fichier au navigateur du client.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param string $filePath
	 * @param string|null $fileName
	 * @param string|null $mimeType
	 */
	public static function output(string $filePath, ?string $fileName=null, ?string $mimeType=null): void
	{
		self::_output($filePath, $fileName, false, $mimeType, null, true);
	}

	/**
	 * Envoi un fichier au navigateur du client.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param OutputFile $file
	 * @param string|null $mimeType
	 */
	public static function outputFile(OutputFile $file, ?string $mimeType=null): void
	{
		if (null === $file->getFilePath()) {
			return;
		}

		self::_output($file->getFilePath(), $file->getFileName(), false, $mimeType, null, true);
	}

	/**
	 * Envoi un fichier au navigateur du client.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param string $filePath
	 * @param string|null $fileName
	 * @param string|null $transferEncoding
	 */
	public static function download(string $filePath, ?string $fileName=null, ?string $transferEncoding=null): void
	{
		self::_output($filePath, $fileName, true, null, $transferEncoding, true);
	}

	/**
	 * Envoi un fichier au navigateur du client.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param OutputFile $file
	 * @param string|null $transferEncoding
	 */
	public static function downloadFile(OutputFile $file, ?string $transferEncoding=null): void
	{
		if (null === $file->getFilePath()) {
			return;
		}

		self::_output($file->getFilePath(), $file->getFileName(), true, null, $transferEncoding, true);
	}

	/**
	 * @param string $filePath
	 * @param string|null $fileName
	 * @param bool $forceDownload
	 * @param string|null $mimeType
	 * @param string|null $transferEncoding
	 * @return Response|null
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null, bool $forceDownload=true, ?string $mimeType=null, ?string $transferEncoding=null): ?Response
	{
		return self::_output($filePath, $fileName, $forceDownload, $mimeType, $transferEncoding, false);
	}

	/**
	 * @param string $filePath
	 * @param string|null $fileName
	 * @param bool $forceDownload
	 * @param string|null $mimeType
	 * @param string|null $transferEncoding
	 * @param bool $sendResponse
	 * @return Response|null
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

	/**
	 * Retourne la taille plus l'unité arrondie
	 * @param float $bytes taille en octets
	 * @param int $numberOfDecimalPlaces le nombre de chiffre après la virgule pour l'affichage du nombre correspondant à la taille
	 * @return string chaine de caractères formatée
	 */
	public static function formatSize(float $bytes, int $numberOfDecimalPlaces=2): string
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];
		switch (strtoupper(substr(\Locale::getDefault(), 0, 2))) {
			case 'FR': $units = ['o', 'Ko', 'Mo', 'Go', 'To']; break;
		}

		$b = $bytes;

		// Cas des tailles de fichier négatives
		if ($b > 0) {
			$e = (int)(log($b,1024));
			// Si on a pas l'unité on retourne en To
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

	/**
	 * @param string $extension
	 * @param array|null $extensionsAndMimeTypes
	 * @return string|null
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
	 * @param string $mimeType
	 * @param array|null $extensionsAndMimeTypes
	 * @return string|null
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
	 * @param array $extensionsAndMimeTypes
	 * @return array
	 */
	private static function formatExtensionsAndMimeTypes(array $extensionsAndMimeTypes): array
	{
		return array_values(array_map(fn($extensionsAndMimeTypesOfFormat) => [array_map(fn($extension) => str_starts_with($extension, '.') ? substr($extension, 1) : $extension, $extensionsAndMimeTypesOfFormat[0]), $extensionsAndMimeTypesOfFormat[1]], $extensionsAndMimeTypes));
	}

	/**
	 * @return array
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
	 * Defaults to 'application/octet-stream', i.e.. arbitrary binary data.
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