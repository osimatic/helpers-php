<?php

namespace Osimatic\Helpers\Media;

use Symfony\Component\HttpFoundation\Response;

class Image
{
	public const JPG_EXTENSION 			= '.jpg';
	public const JPG_EXTENSIONS 		= [self::JPG_EXTENSION, '.jpeg', '.jpe'];
	public const JPG_MIME_TYPES 		= ['image/jpeg'];

	public const PNG_EXTENSION 			= '.png';
	public const PNG_MIME_TYPES 		= ['image/png'];

	public const GIF_EXTENSION 			= '.gif';
	public const GIF_MIME_TYPES 		= ['image/gif'];

	public const SVG_EXTENSION 			= '.svg';
	public const SVG_MIME_TYPES 		= ['image/svg+xml'];

	public const BMP_EXTENSION 			= '.bmp';
	public const BMP_MIME_TYPES 		= ['image/bmp'];

	public const WEBP_EXTENSION 		= '.webp';
	public const WEBP_MIME_TYPES 		= ['image/webp'];

	public const TIFF_EXTENSION 		= '.tiff';
	public const TIFF_EXTENSIONS 		= [self::TIFF_EXTENSION, '.tif'];
	public const TIFF_MIME_TYPES 		= ['image/tiff'];

	/**
	 * @return array
	 */
	public static function getExtensionsAndMimeTypes(): array
	{
		return [
			'jpg' => [self::JPG_EXTENSIONS, self::JPG_MIME_TYPES],
			'png' => [[self::PNG_EXTENSION], self::PNG_MIME_TYPES],
			'gif' => [[self::GIF_EXTENSION], self::GIF_MIME_TYPES],
			'svg' => [[self::SVG_EXTENSION], self::SVG_MIME_TYPES],
			'bmp' => [[self::BMP_EXTENSION], self::BMP_MIME_TYPES],
			'webp' => [[self::WEBP_EXTENSION], self::WEBP_MIME_TYPES],
			'tiff' => [self::TIFF_EXTENSIONS, self::TIFF_MIME_TYPES],
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
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, array_merge(self::JPG_EXTENSIONS, [self::PNG_EXTENSION], [self::GIF_EXTENSION]), array_merge(self::JPG_MIME_TYPES, self::PNG_MIME_TYPES, self::GIF_MIME_TYPES));
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkJpgFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, self::JPG_EXTENSIONS, self::JPG_MIME_TYPES);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkPngFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, [self::PNG_EXTENSION], self::PNG_MIME_TYPES);
	}

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkGifFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, [self::GIF_EXTENSION], self::GIF_MIME_TYPES);
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

	// ========== Récupération d'information ==========

	/**
	 * Retourne la largeur de l'image.
	 * @param string $imgPath le chemin complet vers l'image sur laquelle renvoyer la taille
	 * @return int|null la largeur de l'image, false si une erreur survient lors de la lecture du fichier
	 */
	public static function getWidth(string $imgPath): ?int
	{
		if (($size = getimagesize($imgPath)) === false) {
			return null;
		}
		return $size[0];
	}

	/**
	 * Retourne la hauteur de l'image.
	 * @param string $imgPath le chemin complet vers l'image sur laquelle renvoyer la taille
	 * @return int|null la hauteur de l'image, false si une erreur survient lors de la lecture du fichier
	 */
	public static function getHeight(string $imgPath): ?int
	{
		if (($size = getimagesize($imgPath)) === false) {
			return null;
		}
		return $size[1];
	}

	/**
	 * @param string $imgPath
	 * @return string|null
	 */
	public static function getMimeType(string $imgPath): ?string
	{
		if (($size = getimagesize($imgPath)) === false) {
			return null;
		}
		return $size['mime'];
	}

	/**
	 * @param string $imgPath
	 * @return string|null
	 */
	public static function getEtag(string $imgPath): ?string
	{
		if (($data = file_get_contents($imgPath)) === false) {
			return null;
		}
		return md5($data);
	}

	/**
	 * @param string $imgPath
	 * @return string|null
	 */
	public static function getLastModifiedString(string $imgPath): ?string
	{
		if (false === ($lastModifTimestamp = filemtime($imgPath))) {
			return null;
		}
		if (!($lastModifFormattedDateTime = gmdate('D, d M Y H:i:s', $lastModifTimestamp))) {
			return null;
		}
		return $lastModifFormattedDateTime . ' GMT';
	}

	/**
	 * @param string $photoPath
	 * @return int|null
	 */
	public static function getPhotoTimestamp(string $photoPath): ?int
	{
		if (($exifDataPhoto = self::readExifData($photoPath)) !== false) {
			if (isset($exifDataPhoto['EXIF']['DateTimeOriginal'])) {
				return strtotime($exifDataPhoto['EXIF']['DateTimeOriginal']);
			}
			if (isset($exifDataPhoto['IFD0']['DateTime'])) {
				return strtotime($exifDataPhoto['IFD0']['DateTime']);
			}
		}
		return null;
	}

	/**
	 * Retourne la marque de l'appareil photo avec lequel a été prise la photo.
	 * @param string $photoPath le chemin complet vers la photo à analyser.
	 * @return null|string la marque de l'appareil photo, null si une erreur survient lors de la lecture du fichier ou si la marque de l'appareil photo n'est pas renseigné.
	 */
	public static function getCameraMake(string $photoPath): ?string
	{
		if (($exifDataPhoto = self::readExifData($photoPath)) !== false) {
			return $exifDataPhoto['IFD0']['Make'] ?? null;
		}
		return null;
	}

	/**
	 * Retourne le modèle de l'appareil photo avec lequel a été prise la photo.
	 * @param string $photoPath le chemin complet vers la photo à analyser.
	 * @return null|string le modèle de l'appareil photo, null si une erreur survient lors de la lecture du fichier ou si le modèle de l'appareil photo n'est pas renseigné.
	 */
	public static function getCameraModel(string $photoPath): ?string
	{
		if (($exifDataPhoto = self::readExifData($photoPath)) !== false) {
			return $exifDataPhoto['IFD0']['Model'] ?? null;
		}
		return null;
	}

	/**
	 * @param string $photoPath
	 * @return array
	 */
	public static function readExifData(string $photoPath): ?array
	{
		$exif = false;
		if (function_exists('exif_read_data')) {
			$exif = @exif_read_data($photoPath, null, true);
			if ($exif !== false) {
				return $exif;
			}
		}
		return null;
	}

	// ========== Affichage d'image ==========

	/**
	 * @param string $file
	 * @param bool $withCache
	 */
	public static function output(string $file, bool $withCache=true): void
	{
		self::_output($file, $withCache, true);
	}

	/**
	 * @param string $file
	 * @param bool $withCache
	 * @return Response
	 */
	public static function getHttpResponse(string $file, bool $withCache=true): Response
	{
		return self::_output($file, $withCache, false);
	}

	/**
	 * @param string $file
	 * @param bool $withCache
	 * @param bool $sendResponse
	 * @return Response|null
	 */
	private static function _output(string $file, bool $withCache=true, bool $sendResponse=true): ?Response
	{
		if (($data = file_get_contents($file)) === false) {
			//trace("Erreur : L'image n'existe pas.");
			if (!$sendResponse) {
				return new Response('file_not_found', Response::HTTP_BAD_REQUEST);
			}
			return null;
		}

		$lastModifiedString = self::getLastModifiedString($file);
		$mime 				= self::getMimeType($file);
		$etag 				= self::getEtag($file);

		$headers = [
			'Last-Modified' => $lastModifiedString,
			'ETag' => '"'.$etag.'"'
		];

		$outputImage = true;

		if ($withCache) {
			$outputImage = false;

			$ifNoneMatch = false;
			if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
				$ifNoneMatch = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
			}

			$ifModifiedSince = false;
			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
				$ifModifiedSince = stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			}

			if (!$ifModifiedSince && !$ifNoneMatch) {
				$outputImage = true;
			}

			if ($ifNoneMatch && $ifNoneMatch != $etag && $ifNoneMatch != '"' . $etag . '"') {
				// etag is there but doesn't match
				$outputImage = true;
			}

			if ($ifModifiedSince && $ifModifiedSince != $lastModifiedString) {
				// if-modified-since is there but doesn't match
				$outputImage = true;
			}
		}

		if (!$outputImage) {
			// Nothing has changed since their last request - serve a 304 and exit
			if (!$sendResponse) {
				return new Response(null, Response::HTTP_NOT_MODIFIED);
			}

			header('HTTP/1.1 304 Not Modified');
			exit();
		}

		$headers['Content-Type'] = $mime;
		$headers['Content-Length'] = strlen($data);

		if (!$sendResponse) {
			return new Response($data, Response::HTTP_OK, $headers);
		}

		foreach ($headers as $key => $value) {
			header($key.': '.$value);
		}
		echo $data;
		exit();
	}
}