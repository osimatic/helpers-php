<?php

namespace Osimatic\Helpers\Media;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use getID3;
use getid3_exception;

class Audio
{
	public const MP3_FORMAT 			= 'mp3';
	public const MP3_EXTENSION 			= '.mp3';
	public const MP3_EXTENSIONS 		= ['.mpga', '.mp2', '.mp2a', '.mp3', '.m2a', '.m3a'];
	public const MP3_MIME_TYPES 		= ['audio/mpeg'];

	public const WAV_FORMAT 			= 'wav';
	public const WAV_EXTENSION 			= '.wav';
	public const WAV_MIME_TYPES 		= ['audio/x-wav'];

	public const OGG_FORMAT 			= 'ogg';
	public const OGG_EXTENSION 			= '.ogg';
	public const OGG_EXTENSIONS 		= ['.ogg', '.oga', '.spx'];
	public const OGG_MIME_TYPES 		= ['audio/ogg'];

	public const AAC_FORMAT 			= 'aac';
	public const AAC_EXTENSION 			= '.aac';
	public const AAC_MIME_TYPES 		= ['audio/x-aac', 'audio/aac'];

	public const AIFF_FORMAT 			= 'aiff';
	public const AIFF_EXTENSION 		= '.aiff';
	public const AIFF_EXTENSIONS 		= ['.aif', '.aiff', '.aifc'];
	public const AIFF_MIME_TYPES 		= ['audio/x-aiff'];

	public const WMA_FORMAT 			= 'wma';
	public const WMA_EXTENSION 			= '.wma';
	public const WMA_MIME_TYPES 		= ['audio/x-ms-wma'];

	public const WEBM_FORMAT 			= 'webm';
	public const WEBM_EXTENSION 		= '.weba';
	public const WEBM_MIME_TYPES 		= ['audio/webm'];

	/**
	 * @return array
	 */
	public static function getExtensionsAndMimeTypes(): array
	{
		return [
			'mp3' => [self::MP3_EXTENSIONS, self::MP3_MIME_TYPES],
			'wav' => [[self::WAV_EXTENSION], self::WAV_MIME_TYPES],
			'ogg' => [self::OGG_EXTENSIONS, self::OGG_MIME_TYPES],
			'aac' => [[self::AAC_EXTENSION], self::AAC_MIME_TYPES],
			'aiff' => [self::AIFF_EXTENSIONS, self::AIFF_MIME_TYPES],
			'wma' => [[self::WMA_EXTENSION], self::WMA_MIME_TYPES],
			'weba' => [[self::WEBM_EXTENSION], self::WEBM_MIME_TYPES],
		];
	}

	/**
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * @var string|null
	 */
	private ?string $soxBinaryPath = null;

	public function __construct()
	{
		$this->logger = new NullLogger();
	}

	/**
	 * Set the logger to use to log debugging data.
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	/**
	 * @param string $audioFilePath
	 * @return array|null
	 */
	public static function getInfos(string $audioFilePath): ?array
	{
		try {
			$getID3 = new getID3();
			return $getID3->analyze($audioFilePath);
		} catch (getid3_exception $e) {
			//var_dump($e->getMessage());
		}
		return null;
	}

	/**
	 * @param string $audioFilePath
	 * @return string|null
	 */
	public static function getFormat(string $audioFilePath): ?string
	{
		$fileInfos = self::getInfos($audioFilePath);

		if (!empty($fileInfos['audio']['dataformat']) && mb_strtolower($fileInfos['audio']['dataformat']) === 'wav') {
			return self::WAV_FORMAT;
		}

		if (!empty($fileInfos['audio']['dataformat']) && mb_strtolower($fileInfos['audio']['dataformat']) === 'mp3') {
			return self::MP3_FORMAT;
		}

		if (!empty($fileInfos['fileformat']) && mb_strtolower($fileInfos['fileformat']) === 'webm') {
			return self::WEBM_FORMAT;
		}

		// todo : autres format

		return null;
	}

	/**
	 * @param string $audioFilePath
	 * @return float
	 */
	public static function getDuration(string $audioFilePath): float
	{
		$infos = self::getInfos($audioFilePath);
		return $infos['playtime_seconds'] ?? 0;
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return self::checkFileByType($filePath, $clientOriginalName, array_merge(self::MP3_EXTENSIONS, [self::WAV_EXTENSION]), null, [self::MP3_FORMAT, self::WAV_FORMAT]);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkMp3File(string $filePath, string $clientOriginalName): bool
	{
		return self::checkFileByType($filePath, $clientOriginalName, self::MP3_EXTENSIONS, null, [self::MP3_FORMAT]);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkWavFile(string $filePath, string $clientOriginalName): bool
	{
		return self::checkFileByType($filePath, $clientOriginalName, [self::WAV_EXTENSION], null, [self::WAV_FORMAT]);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @param array|null $extensionsAllowed
	 * @param array|null $mimeTypesAllowed
	 * @param array|null $formatsAllowed
	 * @return bool
	 */
	private static function checkFileByType(string $filePath, string $clientOriginalName, ?array $extensionsAllowed, ?array $mimeTypesAllowed=null, ?array $formatsAllowed=null): bool
	{
		if (empty($filePath) || !\Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, $extensionsAllowed, $mimeTypesAllowed)) {
			return false;
		}

		if (!empty($formatsAllowed) && !in_array(self::getFormat($filePath), $formatsAllowed, true)) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $ismn
	 * @return bool
	 * @link https://en.wikipedia.org/wiki/International_Standard_Music_Number
	 */
	public static function checkIsmn(string $ismn): bool
	{
		// todo
		return true;
	}

	/**
	 * @param string $extension
	 * @return string|null
	 */
	public static function getMimeTypeFromExtension(string $extension): ?string
	{
		return \Osimatic\Helpers\FileSystem\File::getMimeTypeFromExtension($extension, self::getExtensionsAndMimeTypes());
	}

	/**
	 * @param string $mimeType
	 * @return string|null
	 */
	public static function getExtensionFromMimeType(string $mimeType): ?string
	{
		return \Osimatic\Helpers\FileSystem\File::getExtensionFromMimeType($mimeType, self::getExtensionsAndMimeTypes());
	}

	/**
	 * Envoi au navigateur du client un fichier audio.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param string $filePath le chemin complet vers le fichier audio
	 * @param string|null $fileName le nom que prendra le fichier audio lorsque le client le téléchargera, ou null pour utiliser le nom actuel du fichier audio (null par défaut)
	 */
	public static function output(string $filePath, ?string $fileName=null): void
	{
		//$mimeType = self::getMimeTypeFromExtension(self::getExtension($filePath));
		//\Osimatic\Helpers\FileSystem\File::output($filePath, $fileName, $mimeType);
		\Osimatic\Helpers\FileSystem\File::output($filePath, $fileName);
	}

	/**
	 * @param string $audioFilePath
	 * @return string
	 */
	private static function getExtension(string $audioFilePath): string
	{
		$infosFile = new \SplFileInfo($audioFilePath);
		return mb_strtolower($infosFile->getExtension());
	}

	/**
	 * @param string $audioFilePath
	 * @param bool $asStream
	 */
	public static function play(string $audioFilePath, bool $asStream=false): void
	{
		if ($asStream) {
			self::playStream($audioFilePath);
			return;
		}

		if (!file_exists($audioFilePath)) {
			return;
		}

		$extension = self::getExtension($audioFilePath);
		if (null !== ($mimeType = self::getMimeTypeFromExtension($extension))) {
			header('Content-Type: ' . $mimeType);
		}
		if (null !== ($mimeType = Video::getMimeTypeFromExtension($extension))) {
			header('Content-Type: ' . $mimeType);
		}

		header('Content-Disposition: filename='.basename($audioFilePath));
		header('Content-length: '.filesize($audioFilePath));
		header('X-Pad: avoid browser bug');
		header('Cache-Control: no-cache');
		readfile($audioFilePath);
		exit();
	}

	/**
	 * @param string $audioFilePath
	 */
	public static function playStream(string $audioFilePath): void
	{
		if (!file_exists($audioFilePath)) {
			return;
		}

		$fp = @fopen($audioFilePath, 'rb');
		$size 	= filesize($audioFilePath); 	// File size
		$length = $size;						// Content length
		$start 	= 0;							// Start byte
		$end 	= $size - 1;					// End byte

		$extension = self::getExtension($audioFilePath);
		if (null !== ($mimeType = self::getMimeTypeFromExtension($extension))) {
			header('Content-Type: ' . $mimeType);
		}
		else if (null !== ($mimeType = Video::getMimeTypeFromExtension($extension))) {
			header('Content-Type: ' . $mimeType);
		}

		//header('Accept-Ranges: bytes');
		header('Accept-Ranges: 0-'.$length);

		if (isset($_SERVER['HTTP_RANGE'])) {
			$c_start = $start;
			$c_end = $end;
			[, $range] = explode('=', $_SERVER['HTTP_RANGE'], 2);
			if (strpos($range, ',') !== false) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header('Content-Range: bytes '.$start.'-'.$end.'/'.$size);
				exit;
			}
			if ($range == '-') {
				$c_start = $size - substr($range, 1);
			}
			else {
				$range = explode('-', $range);
				$c_start = $range[0];
				$c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
			}
			$c_end = ($c_end > $end) ? $end : $c_end;
			if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header('Content-Range: bytes '.$start.'-'.$end.'/'.$size);
				exit;
			}
			$start = $c_start;
			$end = $c_end;
			$length = $end - $start + 1;
			fseek($fp, $start);
			header('HTTP/1.1 206 Partial Content');
			header('Content-Range: bytes '.$start.'-'.$end.'/'.$size);
		}

		header('Content-Length: '.$length);

		$buffer = 1024 * 8;
		while(!feof($fp) && ($p = ftell($fp)) <= $end) {
			if ($p + $buffer > $end) {
				$buffer = $end - $p + 1;
			}
			set_time_limit(0);
			echo fread($fp, $buffer);
			flush();
		}
		fclose($fp);
		exit();
	}

	/*
	public static function zcast_stream($file, $content_type = 'application/octet-stream') {
		if (!($stream = fopen($file, 'rb'))) {
			die('Could not open stream for reading');
		}

		ob_get_clean();
		header("Content-Type: ".$content_type);
		header("Cache-Control: max-age=2592000, public");
		header("Expires: ".gmdate('D, d M Y H:i:s', time()+2592000) . ' GMT');
		header("Last-Modified: ".gmdate('D, d M Y H:i:s', @filemtime($file)) . ' GMT' );
		$start = 0;
		$size  = filesize($file);
		$length = $size;
		$end   = $size - 1;
		header("Accept-Ranges: 0-".$end);

		if (isset($_SERVER['HTTP_RANGE'])) {
			$c_start = $start;
			$c_end = $end;

			list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
			if (strpos($range, ',') !== false) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header("Content-Range: bytes $start-$end/$size");
				exit;
			}
			if ($range == '-') {
				$c_start = $size - substr($range, 1);
			}
			else{
				$range = explode('-', $range);
				$c_start = $range[0];

				$c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
			}
			$c_end = ($c_end > $end) ? $end : $c_end;
			if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header("Content-Range: bytes $start-$end/$size");
				exit;
			}
			$start = $c_start;
			$end = $c_end;
			$length = $end - $start + 1;
			fseek($stream, $start);
			header('HTTP/1.1 206 Partial Content');
			header("Content-Range: bytes $start-$end/".$size);
		}

		header("Content-Length: ".$length);

		$buffer = 102400;

		$i = $start;
		set_time_limit(0);
		while(!feof($stream) && $i <= $end) {
			$bytesToRead = $buffer;
			if(($i+$bytesToRead) > $end) {
				$bytesToRead = $end - $i + 1;
			}
			echo fread($stream, $bytesToRead);
			flush();
			$i += $bytesToRead;
		}

		fclose($stream);
		exit;
	}
	*/

	/**
	 * @param string $srcAudioFilePath
	 * @param string|null $destAudioFilePath
	 * @return bool
	 */
	public function convertToWavCcittALaw(string $srcAudioFilePath, ?string $destAudioFilePath=null): bool
	{
		// Vérification que le fichier soit un fichier wav ou mp3
		$fileFormat = self::getFormat($srcAudioFilePath);
		if (!in_array($fileFormat, [self::MP3_FORMAT, self::WAV_FORMAT], true)) {
			$this->logger->error('Message audio pas au format mp3 ou WAV');
			return false;
		}

		// Vérif si fichier audio destination renseigné. Si non renseigné, on utilise le nom de fichier audio source (en mettant l'extension wav si ce n'est pas le cas)
		if (empty($destAudioFilePath)) {
			$destAudioFilePath = substr($srcAudioFilePath, 0, strrpos($srcAudioFilePath, '.')).'_converted'.substr($srcAudioFilePath, strrpos($srcAudioFilePath, '.'));
		}
		if ($fileFormat !== self::WAV_FORMAT) {
			$destAudioFilePath = substr($destAudioFilePath, 0, strrpos($destAudioFilePath, '.')+1).'wav';
		}

		$params = ($fileFormat === self::MP3_FORMAT?'-t mp3 ':'').'"'.$srcAudioFilePath.'" -e a-law -c 1 -r 8000 "'.$destAudioFilePath.'"';
		$commandLine = $this->soxBinaryPath.' '.$params;

		// Envoi de la commande
		$this->logger->info('Ligne de commande exécutée : '.$commandLine);
		$lastLine = system($commandLine);
		//$lastLine = exec($commandLine, $output, $returnVar);
		//var_dump($output, $lastLine);

		return true;
	}

	/**
	 * Exemple de commande : sox -t wav -r 8000 -c 1 file.wav -t mp3 file.mp3
	 * @param string $srcAudioFilePath
	 * @param string|null $destAudioFilePath
	 * @return bool
	 */
	public function convertWavToMp3(string $srcAudioFilePath, ?string $destAudioFilePath=null) : bool
	{
		// Vérification que le fichier soit un fichier wav
		if (self::getFormat($srcAudioFilePath) !== self::WAV_FORMAT) {
			$this->logger->error('Message audio pas au format WAV');
			return false;
		}

		// Vérif si fichier audio destination renseigné. Si non renseigné, on utilise le nom de fichier audio source (en mettant l'extension mp3)
		if (empty($destAudioFilePath)) {
			$destAudioFilePath = substr($srcAudioFilePath, 0, strrpos($srcAudioFilePath, '.')).'_converted.mp3';
		}

		$commandLine = $this->soxBinaryPath.' -t wav -r 8000 -c 1 "'.$srcAudioFilePath.'" -t mp3 "'.$destAudioFilePath.'"';

		// Envoi de la commande
		$this->logger->info('Ligne de commande exécutée : '.$commandLine);
		$lastLine = system($commandLine);
		//$lastLine = exec($commandLine, $output, $returnVar);
		//var_dump($output, $lastLine);

		return true;
	}

	/**
	 * @param string $srcAudioFilePath
	 * @param string|null $destAudioFilePath
	 * @return bool
	 */
	public function convertWebMToMp3(string $srcAudioFilePath, ?string $destAudioFilePath=null) : bool
	{
		// Vérification que le fichier soit au format WebM
		if (self::getFormat($srcAudioFilePath) !== self::WEBM_FORMAT) {
			$this->logger->error('Message audio pas au format WebM');
			return false;
		}

		// Vérif si fichier audio destination renseigné. Si non renseigné, on utilise le nom de fichier audio source (en mettant l'extension mp3)
		if (empty($destAudioFilePath)) {
			$destAudioFilePath = substr($srcAudioFilePath, 0, strrpos($srcAudioFilePath, '.')).'_converted.mp3';
		}

		if (file_exists($destAudioFilePath)) {
			unlink($destAudioFilePath);
		}

		$commandLine = 'ffmpeg -i "'.$srcAudioFilePath.'" -ab 160k -ar 44100 "'.$destAudioFilePath.'"';

		// Envoi de la commande
		$this->logger->info('Ligne de commande exécutée : '.$commandLine);
		$lastLine = system($commandLine);
		//$lastLine = exec($commandLine, $output, $returnVar);
		//var_dump($output, $lastLine);

		return true;
	}

	/**
	 * @param string $soxBinaryPath
	 */
	public function setSoxBinaryPath(string $soxBinaryPath): void
	{
		$this->soxBinaryPath = $soxBinaryPath;
	}


}