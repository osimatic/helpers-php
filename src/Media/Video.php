<?php

namespace Osimatic\Helpers\Media;

class Video
{
	const MP4_EXTENSION 		= '.mp4';
	const MP4_EXTENSIONS 		= ['.mp4', '.mp4v', '.mpg4'];
	const MP4_MIME_TYPES 		= ['video/mp4',];

	const MPG_EXTENSION 		= '.mpg';
	const MPG_EXTENSIONS 		= ['.mpeg', '.mpg', '.mpe', '.m1v', '.m2v'];
	const MPG_MIME_TYPES 		= ['video/mpeg',];

	const AVI_EXTENSION 		= '.avi';
	const AVI_MIME_TYPES 		= ['video/x-msvideo',];

	const WMV_EXTENSION 		= '.wmv';
	const WMV_MIME_TYPES 		= ['video/x-ms-wmv',];

	const FLV_EXTENSION 		= '.flv';
	const FLV_MIME_TYPES 		= ['video/x-flv',];


	// ========== Vérification ==========

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, array_merge(self::MP4_EXTENSIONS, self::MPG_EXTENSIONS, [self::AVI_EXTENSION], [self::WMV_EXTENSION]), array_merge(self::MP4_MIME_TYPES, self::MPG_MIME_TYPES, self::AVI_MIME_TYPES, self::WMV_MIME_TYPES));
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkMp4File(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, self::MP4_EXTENSIONS, self::MP4_MIME_TYPES);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkMpgFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, self::MPG_EXTENSIONS, self::MPG_MIME_TYPES);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkAviFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, [self::AVI_EXTENSION], self::AVI_MIME_TYPES);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkWmvFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, [self::WMV_EXTENSION], self::WMV_MIME_TYPES);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkFlvFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, [self::FLV_EXTENSION], self::FLV_MIME_TYPES);
	}

}