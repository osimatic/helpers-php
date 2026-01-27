<?php

namespace Osimatic\Media;

/**
 * Image resize modes for controlling how images are resized to fit target dimensions.
 * Each mode handles aspect ratio and dimension constraints differently.
 */
enum ImageResizeMode: string
{
	/**
	 * Fit mode - scales image to fit within dimensions while preserving aspect ratio.
	 * The entire image is visible, may result in empty space.
	 * This is the default behavior.
	 */
	case FIT = 'fit';

	/**
	 * Fill mode - scales image to fill dimensions while preserving aspect ratio.
	 * May crop parts of the image to fill the entire area.
	 */
	case FILL = 'fill';

	/**
	 * Stretch mode - scales image to exactly match dimensions.
	 * Does not preserve aspect ratio, may distort the image.
	 */
	case STRETCH = 'stretch';

	/**
	 * Cover mode - scales image to cover the entire area while preserving aspect ratio.
	 * Similar to FILL but ensures the entire target area is covered.
	 */
	case COVER = 'cover';

	/**
	 * Get a human-readable description of the resize mode.
	 * @return string Description of the mode
	 */
	public function getDescription(): string
	{
		return match($this) {
			self::FIT => 'Fit Within Dimensions (Preserve Aspect Ratio)',
			self::FILL => 'Fill Dimensions (Crop If Needed)',
			self::STRETCH => 'Stretch to Exact Dimensions (May Distort)',
			self::COVER => 'Cover Entire Area (Preserve Aspect Ratio)',
		};
	}

	/**
	 * Check if this mode preserves the aspect ratio.
	 * @return bool True if aspect ratio is preserved
	 */
	public function preservesAspectRatio(): bool
	{
		return $this !== self::STRETCH;
	}

	/**
	 * Check if this mode may result in cropping.
	 * @return bool True if cropping may occur
	 */
	public function mayCrop(): bool
	{
		return in_array($this, [self::FILL, self::COVER], true);
	}
}