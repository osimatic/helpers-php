<?php

namespace Osimatic\Helpers\FileSystem;

use Psr\Log\LoggerInterface;

class File
{
	/**
	 * @param string $data
	 * @return string
	 */
	public static function getDataFromBase64Data(string $data): ?string
	{
		if (strstr($data, 'base64,') !== false) {
			$data = explode('base64,', $data)[1] ?? '';
		}
		if (false === ($data = base64_decode($data)) || empty($data)) {
			return null;
		}
		return $data;
	}

	/**
	 * @param InputFile $uploadedFile
	 * @param string $filePath
	 * @param LoggerInterface|null $logger
	 * @return bool
	 */
	public static function moveUploadedFile(InputFile $uploadedFile, string $filePath, ?LoggerInterface $logger=null): bool
	{
		\Osimatic\Helpers\FileSystem\FileSystem::createDirectories($filePath);

		if (file_exists($filePath)) {
			unlink($filePath);
		}

		if (!empty($uploadedFile->getOriginalFileName())) {
			if (false === move_uploaded_file($uploadedFile->getUploadedFilePath(), $filePath)) {
				if (null !== $logger) {
					$logger->error('Erreur lors du téléchargement du fichier "'.$filePath.'"');
				}
				return false;
			}
			return true;
		}

		if (!empty($uploadedFile->getData())) {
			if (false === file_put_contents($filePath, $uploadedFile->getData())) {
				if (null !== $logger) {
					$logger->error('Erreur lors de l\'écriture du fichier "'.$filePath.'" (taille données : '.strlen($uploadedFile->getData()).')');
				}
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

		$extension = strtolower('.'.pathinfo($clientOriginalName, PATHINFO_EXTENSION));
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
	 * @param string|null $transferEncoding
	 */
	public static function output(string $filePath, ?string $fileName=null, ?string $transferEncoding='binary'): void
	{
		if (!file_exists($filePath)) {
			return;
		}

		if (!headers_sent()) {
			header('Content-Type: application/force-download');
			header('Content-Disposition: attachment; filename="'.($fileName ?? basename($filePath)).'"');
			header('Content-Transfer-Encoding: '.$transferEncoding);
			header('Content-Description: File Transfer');
			header('Content-Length: '.filesize($filePath));
			header('Pragma: no-cache'); //header('Pragma: public');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0, public');
			header('Expires: 0');

			//header("Content-type: application/force-download");
			//header("Content-Disposition: attachment; filename=$name");
			readfile($filePath);
		}
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
	 * Map a file name to a MIME type.
	 * Defaults to 'application/octet-stream', i.e.. arbitrary binary data.
	 * @param string $filename A file name or full path, does not need to exist as a file
	 * @return string
	 */
	public static function getMimeTypesForFile(string $filename): string
	{
		// In case the path is a Bitly, strip any query string before getting extension
		$qpos = strpos($filename, '?');
		if (false !== $qpos) {
			$filename = substr($filename, 0, $qpos);
		}

		return self::getMimeTypesForFileExtension(self::mb_pathinfo($filename, PATHINFO_EXTENSION));
	}

	/**
	 * Get the MIME type for a file extension.
	 * @param string $ext File extension
	 * @return string MIME type of file.
	 */
	public static function getMimeTypesForFileExtension(string $ext = ''): string
	{
		$mimes = [
			'xl' 	=> 'application/excel',
			'js' 	=> 'application/javascript',
			'hqx' 	=> 'application/mac-binhex40',
			'cpt' 	=> 'application/mac-compactpro',
			'bin' 	=> 'application/macbinary',
			'doc' 	=> 'application/msword',
			'word' 	=> 'application/msword',
			'xlsx' 	=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xltx' 	=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'potx' 	=> 'application/vnd.openxmlformats-officedocument.presentationml.template',
			'ppsx' 	=> 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'pptx' 	=> 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'sldx' 	=> 'application/vnd.openxmlformats-officedocument.presentationml.slide',
			'docx' 	=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'dotx' 	=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'xlam' 	=> 'application/vnd.ms-excel.addin.macroEnabled.12',
			'xlsb' 	=> 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'class' => 'application/octet-stream',
			'dll' 	=> 'application/octet-stream',
			'dms' 	=> 'application/octet-stream',
			'exe' 	=> 'application/octet-stream',
			'lha' 	=> 'application/octet-stream',
			'lzh' 	=> 'application/octet-stream',
			'psd' 	=> 'application/octet-stream',
			'sea' 	=> 'application/octet-stream',
			'so' 	=> 'application/octet-stream',
			'oda' 	=> 'application/oda',
			'pdf' 	=> 'application/pdf',
			'ai' 	=> 'application/postscript',
			'eps' 	=> 'application/postscript',
			'ps' 	=> 'application/postscript',
			'smi' 	=> 'application/smil',
			'smil' 	=> 'application/smil',
			'mif' 	=> 'application/vnd.mif',
			'xls' 	=> 'application/vnd.ms-excel',
			'ppt' 	=> 'application/vnd.ms-powerpoint',
			'wbxml' => 'application/vnd.wap.wbxml',
			'wmlc' 	=> 'application/vnd.wap.wmlc',
			'dcr' 	=> 'application/x-director',
			'dir' 	=> 'application/x-director',
			'dxr' 	=> 'application/x-director',
			'dvi' 	=> 'application/x-dvi',
			'gtar' 	=> 'application/x-gtar',
			'php3' 	=> 'application/x-httpd-php',
			'php4' 	=> 'application/x-httpd-php',
			'php' 	=> 'application/x-httpd-php',
			'phtml' => 'application/x-httpd-php',
			'phps' 	=> 'application/x-httpd-php-source',
			'swf' 	=> 'application/x-shockwave-flash',
			'sit' 	=> 'application/x-stuffit',
			'tar' 	=> 'application/x-tar',
			'tgz' 	=> 'application/x-tar',
			'xht' 	=> 'application/xhtml+xml',
			'xhtml' => 'application/xhtml+xml',
			'zip' 	=> 'application/zip',
			'mid' 	=> 'audio/midi',
			'midi' 	=> 'audio/midi',
			'mp2' 	=> 'audio/mpeg',
			'mp3' 	=> 'audio/mpeg',
			'mpga' 	=> 'audio/mpeg',
			'aif' 	=> 'audio/x-aiff',
			'aifc' 	=> 'audio/x-aiff',
			'aiff' 	=> 'audio/x-aiff',
			'ram' 	=> 'audio/x-pn-realaudio',
			'rm' 	=> 'audio/x-pn-realaudio',
			'rpm' 	=> 'audio/x-pn-realaudio-plugin',
			'ra' 	=> 'audio/x-realaudio',
			'wav' 	=> 'audio/x-wav',
			'bmp' 	=> 'image/bmp',
			'gif' 	=> 'image/gif',
			'jpeg' 	=> 'image/jpeg',
			'jpe' 	=> 'image/jpeg',
			'jpg' 	=> 'image/jpeg',
			'png' 	=> 'image/png',
			'tiff' 	=> 'image/tiff',
			'tif' 	=> 'image/tiff',
			'eml' 	=> 'message/rfc822',
			'css' 	=> 'text/css',
			'html' 	=> 'text/html',
			'htm' 	=> 'text/html',
			'shtml' => 'text/html',
			'log' 	=> 'text/plain',
			'text' 	=> 'text/plain',
			'txt' 	=> 'text/plain',
			'rtx' 	=> 'text/richtext',
			'rtf' 	=> 'text/rtf',
			'vcf' 	=> 'text/vcard',
			'vcard' => 'text/vcard',
			'xml' 	=> 'text/xml',
			'xsl' 	=> 'text/xml',
			'mpeg' 	=> 'video/mpeg',
			'mpe' 	=> 'video/mpeg',
			'mpg' 	=> 'video/mpeg',
			'mov' 	=> 'video/quicktime',
			'qt' 	=> 'video/quicktime',
			'rv' 	=> 'video/vnd.rn-realvideo',
			'avi' 	=> 'video/x-msvideo',
			'movie' => 'video/x-sgi-movie'
		];
		return $mimes[strtolower($ext)] ?? 'application/octet-stream';
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
	public static function mb_pathinfo(string $path, ?int $options = null)
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
		switch ($options) {
			case PATHINFO_DIRNAME:
				return $ret['dirname'];
			case PATHINFO_BASENAME:
				return $ret['basename'];
			case PATHINFO_EXTENSION:
				return $ret['extension'];
			case PATHINFO_FILENAME:
				return $ret['filename'];
			default:
				return $ret;
		}
	}

}