<?php

namespace Osimatic\Media;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use InvalidArgumentException;

/**
 * Class ImageOptimizer
 * Optimizes images to reduce file size without changing dimensions.
 * Features:
 * - Progressive optimization (tests multiple quality levels)
 * - Automatic detection of best compression rate
 * - EXIF metadata stripping
 * - Support for JPG, PNG, and WebP formats
 */
class ImageOptimizer
{
	private const int MIN_QUALITY = 10;
	private const int MAX_QUALITY = 100;
	private const int DEFAULT_TARGET_QUALITY = 85;
	private const float DEFAULT_MAX_SIZE_RATIO = 0.8; // 80% of original size
	private const int PROGRESSIVE_STEPS = 5; // Number of quality levels to test

	/**
	 * Constructor.
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 */
	public function __construct(
		private LoggerInterface $logger = new NullLogger(),
	) {}

	/**
	 * Sets the logger for error and debugging information.
	 * @param LoggerInterface $logger The PSR-3 logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;
		return $this;
	}

	/**
	 * Optimize an image with a specific quality level.
	 * Reduces file size without changing dimensions.
	 *
	 * @param string $imagePath Path to the image to optimize
	 * @param int $quality Quality level (10-100, default 85). Lower = smaller file
	 * @param string|null $outputPath Output path, null to replace original (default null)
	 * @param bool $stripMetadata Strip EXIF/metadata to reduce size (default true)
	 * @return bool True if optimization succeeded, false otherwise
	 * @throws InvalidArgumentException If quality is not between 10 and 100
	 */
	public function optimize(
		string $imagePath,
		int $quality = self::DEFAULT_TARGET_QUALITY,
		?string $outputPath = null,
		bool $stripMetadata = true
	): bool
	{
		// Validate quality
		if ($quality < self::MIN_QUALITY || $quality > self::MAX_QUALITY) {
			throw new InvalidArgumentException("Quality must be between " . self::MIN_QUALITY . " and " . self::MAX_QUALITY . ", got: $quality");
		}

		// Check if file exists
		if (!file_exists($imagePath)) {
			$this->logger->error('Image file does not exist: ' . $imagePath);
			return false;
		}

		$originalSize = filesize($imagePath);
		$size = getimagesize($imagePath);
		if ($size === false) {
			$this->logger->error('Cannot get image size: ' . $imagePath);
			return false;
		}

		[$width, $height] = $size;
		$mime = $size['mime'] ?? Image::getMimeType($imagePath);

		// Determine output path
		if ($outputPath === null) {
			$outputPath = $imagePath;
		}

		// Process based on format
		$result = match($mime) {
			'image/jpeg', 'image/jpg' => $this->optimizeJpeg($imagePath, $outputPath, $width, $height, $quality, $stripMetadata),
			'image/png', 'image/x-png' => $this->optimizePng($imagePath, $outputPath, $width, $height, $quality, $stripMetadata),
			'image/webp' => $this->optimizeWebp($imagePath, $outputPath, $width, $height, $quality, $stripMetadata),
			default => false
		};

		if ($result && file_exists($outputPath)) {
			$newSize = filesize($outputPath);
			$reduction = round((($originalSize - $newSize) / $originalSize) * 100, 2);
			$this->logger->info("Image optimized: {$imagePath} - Size reduced by {$reduction}% ({$originalSize} -> {$newSize} bytes)");
		}

		return $result;
	}

	/**
	 * Automatically find and apply the best compression quality.
	 * Tests multiple quality levels progressively and selects the best one.
	 *
	 * @param string $imagePath Path to the image to optimize
	 * @param string|null $outputPath Output path, null to replace original (default null)
	 * @param float $maxSizeRatio Maximum size as ratio of original (0.0-1.0, default 0.8 = 80%)
	 * @param bool $stripMetadata Strip EXIF/metadata to reduce size (default true)
	 * @return array{success: bool, quality: int, originalSize: int, optimizedSize: int, reduction: float} Optimization result with stats
	 */
	public function optimizeAuto(
		string $imagePath,
		?string $outputPath = null,
		float $maxSizeRatio = self::DEFAULT_MAX_SIZE_RATIO,
		bool $stripMetadata = true
	): array
	{
		// Validate parameters
		if ($maxSizeRatio <= 0 || $maxSizeRatio > 1) {
			throw new InvalidArgumentException("maxSizeRatio must be between 0 and 1, got: $maxSizeRatio");
		}

		if (!file_exists($imagePath)) {
			$this->logger->error('Image file does not exist: ' . $imagePath);
			return [
				'success' => false,
				'quality' => 0,
				'originalSize' => 0,
				'optimizedSize' => 0,
				'reduction' => 0.0
			];
		}

		$originalSize = filesize($imagePath);
		$targetSize = (int)($originalSize * $maxSizeRatio);

		$this->logger->info("Auto-optimization: Original size = {$originalSize} bytes, Target size <= {$targetSize} bytes");

		// Progressive quality testing
		$bestQuality = $this->findBestQuality($imagePath, $targetSize, $stripMetadata);

		// Apply the best quality found
		$tempOutput = $outputPath ?? $imagePath;
		$success = $this->optimize($imagePath, $bestQuality, $tempOutput, $stripMetadata);

		$optimizedSize = file_exists($tempOutput) ? filesize($tempOutput) : 0;
		$reduction = $originalSize > 0 ? round((($originalSize - $optimizedSize) / $originalSize) * 100, 2) : 0.0;

		return [
			'success' => $success,
			'quality' => $bestQuality,
			'originalSize' => $originalSize,
			'optimizedSize' => $optimizedSize,
			'reduction' => $reduction
		];
	}

	/**
	 * Find the best quality level that meets the target file size.
	 * Uses binary search for efficient quality detection.
	 *
	 * @param string $imagePath Path to the image
	 * @param int $targetSize Target file size in bytes
	 * @param bool $stripMetadata Whether to strip metadata
	 * @return int Best quality level found
	 */
	private function findBestQuality(string $imagePath, int $targetSize, bool $stripMetadata): int
	{
		$minQuality = self::MIN_QUALITY;
		$maxQuality = self::MAX_QUALITY;
		$bestQuality = $minQuality; // Start with minimum quality (smallest file size)

		// Binary search for optimal quality
		while ($minQuality <= $maxQuality) {
			$testQuality = (int)(($minQuality + $maxQuality) / 2);

			// Test this quality level
			$testFile = sys_get_temp_dir() . '/imgopt_test_' . uniqid() . '.tmp';
			$this->optimize($imagePath, $testQuality, $testFile, $stripMetadata);

			if (!file_exists($testFile)) {
				$this->logger->warning("Failed to create test file for quality {$testQuality}");
				break;
			}

			$testSize = filesize($testFile);
			unlink($testFile);

			$this->logger->debug("Tested quality {$testQuality}: {$testSize} bytes (target: {$targetSize})");

			if ($testSize <= $targetSize) {
				// This quality meets the target, try higher quality
				$bestQuality = $testQuality;
				$minQuality = $testQuality + 1;
			} else {
				// File too large, need lower quality
				$maxQuality = $testQuality - 1;
			}
		}

		$this->logger->info("Best quality found: {$bestQuality}");
		return $bestQuality;
	}

	/**
	 * Optimize a JPEG image.
	 *
	 * @param string $imagePath Source image path
	 * @param string $outputPath Output image path
	 * @param int $width Image width
	 * @param int $height Image height
	 * @param int $quality Quality level (10-100)
	 * @param bool $stripMetadata Strip EXIF metadata
	 * @return bool True if successful
	 */
	private function optimizeJpeg(
		string $imagePath,
		string $outputPath,
		int $width,
		int $height,
		int $quality,
		bool $stripMetadata
	): bool
	{
		$source = @imagecreatefromjpeg($imagePath);
		if ($source === false) {
			$this->logger->error('Cannot create image from JPEG: ' . $imagePath);
			return false;
		}

		// Create output image
		$output = imagecreatetruecolor($width, $height);
		imagecopy($output, $source, 0, 0, 0, 0, $width, $height);

		// Save with specified quality
		$result = imagejpeg($output, $outputPath, $quality);

		// Cleanup
		imagedestroy($source);
		imagedestroy($output);

		// Note: PHP's imagejpeg() automatically strips EXIF when creating new image
		// So stripMetadata parameter is implicitly handled

		return $result;
	}

	/**
	 * Optimize a PNG image.
	 *
	 * @param string $imagePath Source image path
	 * @param string $outputPath Output image path
	 * @param int $width Image width
	 * @param int $height Image height
	 * @param int $quality Quality level (10-100, converted to PNG compression 0-9)
	 * @param bool $stripMetadata Strip metadata
	 * @return bool True if successful
	 */
	private function optimizePng(
		string $imagePath,
		string $outputPath,
		int $width,
		int $height,
		int $quality,
		bool $stripMetadata
	): bool
	{
		$source = @imagecreatefrompng($imagePath);
		if ($source === false) {
			$this->logger->error('Cannot create image from PNG: ' . $imagePath);
			return false;
		}

		// Create output image with transparency support
		$output = imagecreatetruecolor($width, $height);
		imagealphablending($output, false);
		imagesavealpha($output, true);

		// Copy with transparency
		imagecopy($output, $source, 0, 0, 0, 0, $width, $height);

		// Convert quality (0-100) to PNG compression level (0-9)
		// Higher quality = lower compression in PNG
		$compression = (int)round(9 - ($quality / 100 * 9));
		$compression = max(0, min(9, $compression));

		// Save with compression
		$result = imagepng($output, $outputPath, $compression);

		// Cleanup
		imagedestroy($source);
		imagedestroy($output);

		return $result;
	}

	/**
	 * Optimize a WebP image.
	 *
	 * @param string $imagePath Source image path
	 * @param string $outputPath Output image path
	 * @param int $width Image width
	 * @param int $height Image height
	 * @param int $quality Quality level (10-100)
	 * @param bool $stripMetadata Strip metadata
	 * @return bool True if successful
	 */
	private function optimizeWebp(
		string $imagePath,
		string $outputPath,
		int $width,
		int $height,
		int $quality,
		bool $stripMetadata
	): bool
	{
		$source = @imagecreatefromwebp($imagePath);
		if ($source === false) {
			$this->logger->error('Cannot create image from WebP: ' . $imagePath);
			return false;
		}

		// Create output image with transparency support
		$output = imagecreatetruecolor($width, $height);
		imagealphablending($output, false);
		imagesavealpha($output, true);

		// Copy
		imagecopy($output, $source, 0, 0, 0, 0, $width, $height);

		// Save with quality
		$result = imagewebp($output, $outputPath, $quality);

		// Cleanup
		imagedestroy($source);
		imagedestroy($output);

		return $result;
	}

	/**
	 * Get information about an image file.
	 *
	 * @param string $imagePath Path to the image
	 * @return array{width: int, height: int, size: int, mime: string, format: string}|null Image info or null on failure
	 */
	public function getImageInfo(string $imagePath): ?array
	{
		if (!file_exists($imagePath)) {
			return null;
		}

		$size = @getimagesize($imagePath);
		if ($size === false) {
			return null;
		}

		return [
			'width' => $size[0],
			'height' => $size[1],
			'size' => filesize($imagePath),
			'mime' => $size['mime'] ?? '',
			'format' => match($size['mime'] ?? '') {
				'image/jpeg', 'image/jpg' => 'JPEG',
				'image/png', 'image/x-png' => 'PNG',
				'image/webp' => 'WebP',
				'image/gif' => 'GIF',
				default => 'Unknown'
			}
		];
	}
}