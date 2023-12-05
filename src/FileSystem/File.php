<?php

namespace Osimatic\Helpers\FileSystem;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
		if (!str_contains($data, 'base64,')) {
			return null;
		}
		if (empty($fileInfos = explode('base64,', $data)[0] ?? '')) {
			return null;
		}
		if (str_starts_with($fileInfos, 'data:') && str_ends_with($fileInfos, ';')) {
			return substr($fileInfos, 5, -1);
		}
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
		\Osimatic\Helpers\FileSystem\FileSystem::createDirectories($filePath);

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
	 * @param string $filePath
	 * @param string|null $fileName
	 * @param string|null $transferEncoding
	 */
	public static function download(string $filePath, ?string $fileName=null, ?string $transferEncoding=null): void
	{
		self::_output($filePath, $fileName, true, null, $transferEncoding, true);
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
			self::formatExtensionsAndMimeTypes(\Osimatic\Helpers\Media\Image::getExtensionsAndMimeTypes()),
			self::formatExtensionsAndMimeTypes(\Osimatic\Helpers\Media\Audio::getExtensionsAndMimeTypes()),
			self::formatExtensionsAndMimeTypes(\Osimatic\Helpers\Media\Video::getExtensionsAndMimeTypes()),
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



	/**
	 * @deprecated
	 */
	public static function getMimeTypesForFile(string $filename): string
	{
		return self::getMimeTypeForFile($filename);
	}

	/**
	 * @deprecated
	 */
	public static function getMimeTypesForFileExtension(string $ext = ''): string
	{
		return self::getMimeTypeFromExtension($ext) ?? 'application/octet-stream';
	}


}