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

	// ========== Modification de taille d'image ==========

	/**
	 * Resizes images, intelligently sharpens, crops based on width:height ratios, color fills transparent GIFs and PNGs, and caches variations for optimal performance
	 * Le code de cette fonction provient de Smart Image Resizer 1.4.1
	 * @author Joe Lencioni (http://shiftingpixel.com)
	 * @see http://veryraw.com/history/2005/03/image-resizing-with-php/
	 * @param string $cheminImage Le chemin vers l'image à redimensionner
	 * @param int $maxWidth maximum width of final image in pixels, 0 pour la même largeur que l'original (false par défaut)
	 * @param int $maxHeight maximum height of final image in pixels, 0 pour la même hauteur que l'original (false par défaut)
	 * @param string $color background hex color for filling transparent PNGs, without # (default null)
	 * @param int $quality quality of output image, between 0 to 100 (default null)
	 * @param string $ratio ratio of width to height to crop final image (e.g. 1:1 or 3:2), false pour ne pas faire de recadrage (false par défaut)
	 * @return bool
	 */
	public static function resize(string $cheminImage, int $maxWidth=0, int $maxHeight=0, ?int $quality=null, ?string $color=null, ?string $ratio=null): bool
	{
		//trace("Chemin vers l'image à redimensionner : ".$cheminImage."");

		//trace("Chemin vers l'image de destination : ".$cheminImageResizee."");

		$size = getimagesize($cheminImage);
		if ($size === false) {
			//trace("Erreur : L'image n'existe pas.");
			return false;
		}

		$mime = self::getMimeType($cheminImage);

		if (substr($mime, 0, 6) != 'image/') {
			//trace("Erreur : Ce format d'image n'est pas pris en charge.");
			return false;
		}

		[$width, $height] = $size;

		// If either a max width or max height are not specified, we default to something large so the unspecified dimension isn't a constraint on our resized image.
		// If neither are specified but the color is, we aren't going to be resizing at all, just coloring.
		if (0 === $maxWidth && 0 !== $maxHeight) {
			$maxWidth	= 99999999999999;
		}
		elseif (0 !== $maxWidth && 0 === $maxHeight) {
			$maxHeight	= 99999999999999;
		}
		// elseif ($color && !$maxWidth && !$maxHeight) {
		elseif (0 === $maxWidth && 0 === $maxHeight) {
			$maxWidth	= $width;
			$maxHeight	= $height;
		}

		// If we don't have a max width or max height, OR the image is smaller than both we do not want to resize it, so we simply output the original image and exit
		// if ((!$maxWidth && !$maxHeight) || (!$color && $maxWidth >= $width && $maxHeight >= $height)) {
		if (!$color && !$ratio && $maxWidth >= $width && $maxHeight >= $height) {
			//trace("Pas de modification à faire sur l'image.");
			// afficher éventuellement l'image
			return true;
		}

		// Ratio cropping
		$offsetX	= 0;
		$offsetY	= 0;

		if ($ratio != false) {
			$cropRatio = explode(':', (string) $ratio);
			if (count($cropRatio) != 2) {
				//trace("Erreur : Ratio incorrect.");
				return false;
			}

			$ratioComputed		= $width / $height;
			$cropRatioComputed	= (float) $cropRatio[0] / (float) $cropRatio[1];

			if ($ratioComputed < $cropRatioComputed) {
				// Image is too tall so we will crop the top and bottom
				$origHeight	= $height;
				$height		= $width / $cropRatioComputed;
				$offsetY	= ($origHeight - $height) / 2;
			}
			else if ($ratioComputed > $cropRatioComputed) {
				// Image is too wide so we will crop off the left and right sides
				$origWidth	= $width;
				$width		= $height * $cropRatioComputed;
				$offsetX	= ($origWidth - $width) / 2;
			}
		}

		// Setting up the ratios needed for resizing. We will compare these below to determine how to resize the image (based on height or based on width)
		$xRatio		= $maxWidth / $width;
		$yRatio		= $maxHeight / $height;

		if ($xRatio * $height < $maxHeight) {
			// Resize the image based on width
			$tnHeight	= ceil($xRatio * $height);
			$tnWidth	= $maxWidth;
		}
		else {
			// Resize the image based on height
			$tnWidth	= ceil($yRatio * $width);
			$tnHeight	= $maxHeight;
		}

		// Determine the quality of the output image
		if ($quality === null) {
			$quality = 90;
		}

		// Set up a blank canvas for our resized image (destination)
		$dst = imagecreatetruecolor($tnWidth, $tnHeight);

		// Set up the appropriate image handling functions based on the original image's mime type
		switch ($size['mime']) {
			case 'image/gif':
				// We will be converting GIFs to PNGs to avoid transparency issues when resizing GIFs
				// This is maybe not the ideal solution, but IE6 can suck it
				$creationFunction	= 'ImageCreateFromGif';
				$outputFunction		= 'ImagePng';
				// We need to convert GIFs to PNGs
				$mime				= 'image/png';
				$doSharpen			= false;
				// We are converting the GIF to a PNG and PNG needs a compression level of 0 (no compression) through 9
				$quality			= (int) round(10 - ($quality / 10));
				break;

			case 'image/x-png':
			case 'image/png':
				$creationFunction	= 'ImageCreateFromPng';
				$outputFunction		= 'ImagePng';
				$doSharpen			= false;
				// PNG needs a compression level of 0 (no compression) through 9
				$quality			= (int) round(10 - ($quality / 10));
				break;

			default:
				$creationFunction	= 'ImageCreateFromJpeg';
				$outputFunction	 	= 'ImageJpeg';
				$doSharpen			= true;
				break;
		}

		// Read in the original image
		$src = $creationFunction($cheminImage);

		if (in_array($size['mime'], array('image/gif', 'image/png'))) {
			if (!$color) {
				// If this is a GIF or a PNG, we need to set up transparency
				imagealphablending($dst, false);
				imagesavealpha($dst, true);
			}
			else {
				// Fill the background with the specified color for matting purposes
				if ($color[0] == '#') {
					$color = substr($color, 1);
				}

				$background	= false;

				if (strlen($color) == 6) {
					$background	= imagecolorallocate($dst, hexdec($color[0].$color[1]), hexdec($color[2].$color[3]), hexdec($color[4].$color[5]));
				}
				else if (strlen($color) == 3) {
					$background	= imagecolorallocate($dst, hexdec($color[0].$color[0]), hexdec($color[1].$color[1]), hexdec($color[2].$color[2]));
				}

				if ($background) {
					imagefill($dst, 0, 0, $background);
				}
			}
		}

		// Resample the original image into the resized canvas we set up earlier
		ImageCopyResampled($dst, $src, 0, 0, $offsetX, $offsetY, $tnWidth, $tnHeight, $width, $height);

		if ($doSharpen) {
			// Sharpen the image based on two things:
			//	(1) the difference between the original size and the final size
			//	(2) the final size

			// Code from Ryan Rud (http://adryrun.com)
			$final	= $tnWidth * (750.0 / $width);
			$a		= 52;
			$b		= -0.27810650887573124;
			$c		= .00047337278106508946;
			$result = $a + $b * $final + $c * $final * $final;
			$sharpness	= max(round($result), 0);

			$sharpenMatrix	= array(
				array(-1, -2, -1),
				array(-2, $sharpness + 12, -2),
				array(-1, -2, -1)
			);
			$divisor		= $sharpness;
			$offset			= 0;
			imageconvolution($dst, $sharpenMatrix, $divisor, $offset);
		}

		// Write the resized image to the dest path
		$outputFunction($dst, $cheminImage, $quality);

		// Clean up the memory
		ImageDestroy($src);
		ImageDestroy($dst);

		// afficher éventuellement l'image
		return true;

		/*
		$return = array('width' => $width, 'height' => $height);

		// If the ratio > goal ratio and the width > goal width resize down to goal width
		if ($width/$height > $goal_width/$goal_height && $width > $goal_width) {
			$return['width'] = $goal_width;
			$return['height'] = $goal_width/$width * $height;
		}
		// Otherwise, if the height > goal, resize down to goal height
		else if ($height > $goal_height) {
			$return['width'] = $goal_height/$height * $width;
			$return['height'] = $goal_height;
		}

		return $return;
		*/
	}

}