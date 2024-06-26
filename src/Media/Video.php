<?php

namespace Osimatic\Media;

class Video
{
	public const string MP4_EXTENSION 			= '.mp4';
	public const array MP4_EXTENSIONS 			= [self::MP4_EXTENSION, '.mp4v', '.mpg4'];
	public const array MP4_MIME_TYPES 			= ['video/mp4'];

	public const string MPG_EXTENSION 			= '.mpg';
	public const array MPG_EXTENSIONS 			= [self::MPG_EXTENSION, '.mpeg', '.mpe', '.m1v', '.m2v'];
	public const array MPG_MIME_TYPES 			= ['video/mpeg'];

	public const string AVI_EXTENSION 			= '.avi';
	public const array AVI_MIME_TYPES 			= ['video/x-msvideo'];

	public const string WMV_EXTENSION 			= '.wmv';
	public const array WMV_MIME_TYPES 			= ['video/x-ms-wmv'];

	public const string FLV_EXTENSION 			= '.flv';
	public const array FLV_MIME_TYPES 			= ['video/x-flv'];

	public const string OGG_EXTENSION 			= '.ogv';
	public const array OGG_MIME_TYPES 			= ['video/ogg'];

	public const string WEBM_EXTENSION 			= '.webm';
	public const array WEBM_MIME_TYPES 			= ['video/webm'];

	public const string _3GPP_EXTENSION 		= '.3gp';
	public const array _3GPP_MIME_TYPES 		= ['video/3gpp'];

	public const string QUICKTIME_EXTENSION 	= '.mov';
	public const array QUICKTIME_EXTENSIONS 	= [self::QUICKTIME_EXTENSION, '.qt'];
	public const array QUICKTIME_MIME_TYPES 	= ['video/quicktime'];

	/**
	 * @return array
	 */
	public static function getExtensionsAndMimeTypes(): array
	{
		return [
			'mp4' => [self::MP4_EXTENSIONS, self::MP4_MIME_TYPES],
			'mpg' => [self::MPG_EXTENSIONS, self::MPG_MIME_TYPES],
			'avi' => [[self::AVI_EXTENSION], self::AVI_MIME_TYPES],
			'wmv' => [[self::WMV_EXTENSION], self::WMV_MIME_TYPES],
			'flv' => [[self::FLV_EXTENSION], self::FLV_MIME_TYPES],
			'ogg' => [[self::OGG_EXTENSION], self::OGG_MIME_TYPES],
			'webm' => [[self::WEBM_EXTENSION], self::WEBM_MIME_TYPES],
			'3gpp' => [[self::_3GPP_EXTENSION], self::_3GPP_MIME_TYPES],
			'quicktime' => [self::QUICKTIME_EXTENSIONS, self::QUICKTIME_MIME_TYPES],
		];
	}


	// ========== Vérification ==========

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, array_merge(self::MP4_EXTENSIONS, self::MPG_EXTENSIONS, [self::AVI_EXTENSION], [self::WMV_EXTENSION]), array_merge(self::MP4_MIME_TYPES, self::MPG_MIME_TYPES, self::AVI_MIME_TYPES, self::WMV_MIME_TYPES));
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkMp4File(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, self::MP4_EXTENSIONS, self::MP4_MIME_TYPES);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkMpgFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, self::MPG_EXTENSIONS, self::MPG_MIME_TYPES);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkAviFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, [self::AVI_EXTENSION], self::AVI_MIME_TYPES);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkWmvFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, [self::WMV_EXTENSION], self::WMV_MIME_TYPES);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkFlvFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, [self::FLV_EXTENSION], self::FLV_MIME_TYPES);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function check3GppFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, [self::_3GPP_EXTENSION], self::_3GPP_MIME_TYPES);
	}

	/**
	 * @param string $extension
	 * @return string|null
	 */
	public static function getMimeTypeFromExtension(string $extension): ?string
	{
		return \Osimatic\FileSystem\File::getMimeTypeFromExtension($extension, self::getExtensionsAndMimeTypes());
	}

	/**
	 * @param string $mimeType
	 * @return string|null
	 */
	public static function getExtensionFromMimeType(string $mimeType): ?string
	{
		return \Osimatic\FileSystem\File::getExtensionFromMimeType($mimeType, self::getExtensionsAndMimeTypes());
	}

}