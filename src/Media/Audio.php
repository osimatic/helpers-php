<?php

namespace Osimatic\Helpers\Media;

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

}