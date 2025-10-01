<?php

namespace Osimatic\Media;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ImageResizer
{
	/**
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		private LoggerInterface $logger=new NullLogger(),
	) {}

	/**
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Resizes images, intelligently sharpens, crops based on width:height ratios, color fills transparent GIFs and PNGs, and caches variations for optimal performance
	 * code from Smart Image Resizer 1.4.1
	 * @author Joe Lencioni (http://shiftingpixel.com)
	 * @see http://veryraw.com/history/2005/03/image-resizing-with-php/
	 * @param string $imagePath Le chemin vers l'image à redimensionner
	 * @param int $maxWidth maximum width of final image in pixels, 0 pour la même largeur que l'original (false par défaut)
	 * @param int $maxHeight maximum height of final image in pixels, 0 pour la même hauteur que l'original (false par défaut)
	 * @param string|null $color background hex color for filling transparent PNGs, without # (default null)
	 * @param int|null $quality quality of output image, between 0 and 100 (default null)
	 * @param string|null $ratio ratio of width to height to crop final image (e.g. 1:1 or 3:2), false pour ne pas faire de recadrage (false par défaut)
	 * @return bool
	 */
	public function resize(string $imagePath, int $maxWidth=0, int $maxHeight=0, ?int $quality=null, ?string $color=null, ?string $ratio=null): bool
	{
		$this->logger->info('Image resized: '.$imagePath);

		$size = getimagesize($imagePath);
		if ($size === false) {
			$this->logger->error('Image does not exist.');
			return false;
		}

		$mime = Image::getMimeType($imagePath);

		if (!str_starts_with($mime, 'image/')) {
			$this->logger->error('Image format not supported.');
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
			$this->logger->error('No resize needed.');
			// afficher éventuellement l'image
			return true;
		}

		// Ratio cropping
		$offsetX	= 0;
		$offsetY	= 0;

		if ($ratio !== null) {
			$cropRatio = explode(':', $ratio);
			if (count($cropRatio) !== 2) {
				$this->logger->error('Invalid ratio.');
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
		$src = $creationFunction($imagePath);

		if (in_array($size['mime'], ['image/gif', 'image/png'])) {
			if (!$color) {
				// If this is a GIF or a PNG, we need to set up transparency
				imagealphablending($dst, false);
				imagesavealpha($dst, true);
			}
			else {
				// Fill the background with the specified color for matting purposes
				if ($color[0] === '#') {
					$color = substr($color, 1);
				}

				$background	= false;

				if (strlen($color) === 6) {
					$background	= imagecolorallocate($dst, hexdec($color[0].$color[1]), hexdec($color[2].$color[3]), hexdec($color[4].$color[5]));
				}
				else if (strlen($color) === 3) {
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
		$outputFunction($dst, $imagePath, $quality);

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