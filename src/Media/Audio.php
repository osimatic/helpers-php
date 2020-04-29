<?php

namespace Osimatic\Helpers\Media;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use getID3;
use getid3_exception;

class Audio
{
	const MP3_EXTENSION 		= '.mp3';
	const MP3_EXTENSIONS 		= ['.mpga', '.mp2', '.mp2a', '.mp3', '.m2a', '.m3a'];
	const MP3_MIME_TYPES 		= ['audio/mpeg',];

	const WAV_EXTENSION 		= '.wav';
	const WAV_MIME_TYPES 		= ['audio/x-wav',];

	const OGG_EXTENSION 		= '.ogg';
	const OGG_EXTENSIONS 		= ['.ogg', '.oga', '.spx'];
	const OGG_MIME_TYPES 		= ['audio/ogg',];

	const AAC_EXTENSION 		= '.aac';
	const AAC_MIME_TYPES 		= ['audio/x-aac',];

	const AIFF_EXTENSION 		= '.aiff';
	const AIFF_EXTENSIONS 		= ['.aif', '.aiff', '.aifc'];
	const AIFF_MIME_TYPES 		= ['audio/x-aiff',];

	const WMA_EXTENSION 		= '.wma';
	const WMA_MIME_TYPES 		= ['audio/x-ms-wma',];

	private $logger;
	private $soxBinaryPath;

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
	 * @return int
	 */
	public static function getDuration(string $audioFilePath): int
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
		return self::checkFileByType($filePath, $clientOriginalName, array_merge(self::MP3_EXTENSIONS, [self::WAV_EXTENSION]), null, ['mp3', 'wav']);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkMp3File(string $filePath, string $clientOriginalName): bool
	{
		return self::checkFileByType($filePath, $clientOriginalName, self::MP3_EXTENSIONS, null, ['mp3']);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkWavFile(string $filePath, string $clientOriginalName): bool
	{
		return self::checkFileByType($filePath, $clientOriginalName, [self::WAV_EXTENSION], null, ['wav']);
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

		if (!empty($formatsAllowed)) {
			$fileInfos = self::getInfos($filePath);
			if (!in_array($fileInfos['audio']['dataformat'] ?? null, $formatsAllowed, true)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Envoi au navigateur du client un fichier audio.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param string $audioFilePath le chemin complet vers le fichier audio
	 * @param string|null $fileName le nom que prendra le fichier audio lorsque le client le téléchargera, ou null pour utiliser le nom actuel du fichier audio (null par défaut)
	 */
	public static function output(string $audioFilePath, ?string $fileName=null): void
	{
		$extension = self::getExtension($audioFilePath);
		$mimeType = 'audio/'.$extension;
		if ('wav' === $extension) {
			$mimeType = 'audio/x-wav';
		}
		// todo : mime-type pour mp3 et ogg

		header('Content-Disposition: attachment; filename="'.($fileName ?? basename($audioFilePath)).'"');
		header('Content-Type: application/force-download');
		header('Content-Transfer-Encoding: '.$mimeType);
		header('Content-Length: '.filesize($audioFilePath));
		header('Pragma: no-cache');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0, public');
		header('Expires: 0');
		readfile($audioFilePath);
	}

	/**
	 * @param $audioFilePath
	 * @return string
	 */
	private static function getExtension($audioFilePath): string
	{
		$infosFile = new \SplFileInfo($audioFilePath);
		return strtolower($infosFile->getExtension());
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

		$extension = self::getExtension($audioFilePath);
		if (in_array($extension, array('mp3', 'wav'))) {
			header('Content-Type: audio/' . $extension);
		}
		if (in_array($extension, array('avi', 'mp4'))) {
			header('Content-Type: vidéo/' . $extension);
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
		/*
		if (!isset($_SERVER['PATH_INFO'])) {
			$_SERVER['PATH_INFO'] = substr($_SERVER["ORIG_SCRIPT_FILENAME"], strlen($_SERVER["SCRIPT_FILENAME"]));
		}
		$request = substr($_SERVER['PATH_INFO'], 1);
		$file = $request;
		*/

		$fp = @fopen($audioFilePath, 'rb');
		$size 	= filesize($audioFilePath); 	// File size
		$length = $size;						// Content length
		$start 	= 0;							// Start byte
		$end 	= $size - 1;					// End byte

		$extension = self::getExtension($audioFilePath);
		if (in_array($extension, array('mp3', 'wav'))) {
			header('Content-Type: audio/' . $extension);
		}
		if (in_array($extension, array('avi', 'mp4'))) {
			header('Content-Type: vidéo/' . $extension);
		}

		header('Accept-Ranges: bytes');
		//header("Accept-Ranges: 0-$length");

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
			else {
				$range = explode('-', $range);
				$c_start = $range[0];
				$c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
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
			fseek($fp, $start);
			header('HTTP/1.1 206 Partial Content');
		}

		header('Content-Range: bytes '.($start-$end/$size));
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

	/**
	 * @param string $srcAudioFilePath
	 * @param string|null $destAudioFilePath
	 * @return bool|string
	 */
	public function convertToWavCcittALaw(string $srcAudioFilePath, ?string $destAudioFilePath=null): bool
	{
		// Vérification que le fichier soit un fichier wav ou mp3
		$wavFileInfos = self::getInfos($srcAudioFilePath);
		if (empty($wavFileInfos['audio']['dataformat']) || !in_array($wavFileInfos['audio']['dataformat'], ['mp3', 'wav'])) {
			$this->logger->error('Message audio pas au format mp3 ou WAV');
			return false;
		}

		// Vérif si fichier audio destination renseigné. Si non renseigné, on replace le fichier audio source (en mettant l'extension wav si ce n'est pas le cas)
		if (empty($destAudioFilePath)) {
			$destAudioFilePath = substr($srcAudioFilePath, 0, strrpos($srcAudioFilePath, '.')).'_converted'.substr($srcAudioFilePath, strrpos($srcAudioFilePath, '.'));
		}
		if ($wavFileInfos['audio']['dataformat'] != 'wav') {
			$destAudioFilePath = substr($destAudioFilePath, 0, strrpos($destAudioFilePath, '.')+1).'wav';
		}

		$params = ($wavFileInfos['audio']['dataformat'] == 'mp3'?'-t mp3 ':'').'"'.$srcAudioFilePath.'" -e a-law -c 1 -r 8000 "'.$destAudioFilePath.'"';
		$commandLine = $this->soxBinaryPath.' '.$params;

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