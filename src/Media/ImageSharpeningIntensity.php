<?php

namespace Osimatic\Media;

/**
 * Image sharpening intensity levels for controlling sharpness after resizing.
 * Higher intensities produce sharper images but may introduce artifacts.
 */
enum ImageSharpeningIntensity: string
{
	/**
	 * No sharpening applied - softer edges
	 */
	case NONE = 'none';

	/**
	 * Low sharpening intensity - subtle enhancement
	 */
	case LOW = 'low';

	/**
	 * Medium sharpening intensity - balanced sharpness (default)
	 */
	case MEDIUM = 'medium';

	/**
	 * High sharpening intensity - noticeable enhancement
	 */
	case HIGH = 'high';

	/**
	 * Very high sharpening intensity - maximum sharpness
	 */
	case VERY_HIGH = 'very_high';

	/**
	 * Get the numeric multiplier value for this intensity level.
	 * @return float The intensity multiplier value
	 */
	public function getMultiplier(): float
	{
		return match($this) {
			self::NONE => 0.0,
			self::LOW => 0.5,
			self::MEDIUM => 1.0,
			self::HIGH => 1.5,
			self::VERY_HIGH => 2.0,
		};
	}

	/**
	 * Get a human-readable description of the sharpening intensity.
	 * @return string Description of the intensity level
	 */
	public function getDescription(): string
	{
		return match($this) {
			self::NONE => 'No Sharpening',
			self::LOW => 'Low Sharpening',
			self::MEDIUM => 'Medium Sharpening (Default)',
			self::HIGH => 'High Sharpening',
			self::VERY_HIGH => 'Very High Sharpening',
		};
	}

	/**
	 * Check if sharpening should be applied.
	 * @return bool True if sharpening should be applied, false otherwise
	 */
	public function shouldApply(): bool
	{
		return $this !== self::NONE;
	}
}