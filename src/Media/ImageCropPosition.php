<?php

namespace Osimatic\Media;

/**
 * Image crop positioning for ratio-based cropping operations.
 * Determines which part of the image to keep when cropping to a specific aspect ratio.
 */
enum ImageCropPosition: string
{
	/**
	 * Center crop - keeps the center of the image (default)
	 */
	case CENTER = 'center';

	/**
	 * Top crop - keeps the top portion of the image
	 */
	case TOP = 'top';

	/**
	 * Bottom crop - keeps the bottom portion of the image
	 */
	case BOTTOM = 'bottom';

	/**
	 * Left crop - keeps the left portion of the image
	 */
	case LEFT = 'left';

	/**
	 * Right crop - keeps the right portion of the image
	 */
	case RIGHT = 'right';

	/**
	 * Top-left corner crop - keeps the top-left portion
	 */
	case TOP_LEFT = 'top-left';

	/**
	 * Top-right corner crop - keeps the top-right portion
	 */
	case TOP_RIGHT = 'top-right';

	/**
	 * Bottom-left corner crop - keeps the bottom-left portion
	 */
	case BOTTOM_LEFT = 'bottom-left';

	/**
	 * Bottom-right corner crop - keeps the bottom-right portion
	 */
	case BOTTOM_RIGHT = 'bottom-right';

	/**
	 * Get a human-readable description of the crop position.
	 * @return string Description of the position
	 */
	public function getDescription(): string
	{
		return match($this) {
			self::CENTER => 'Center Crop',
			self::TOP => 'Top Crop',
			self::BOTTOM => 'Bottom Crop',
			self::LEFT => 'Left Crop',
			self::RIGHT => 'Right Crop',
			self::TOP_LEFT => 'Top-Left Corner Crop',
			self::TOP_RIGHT => 'Top-Right Corner Crop',
			self::BOTTOM_LEFT => 'Bottom-Left Corner Crop',
			self::BOTTOM_RIGHT => 'Bottom-Right Corner Crop',
		};
	}

	/**
	 * Check if this position affects vertical offset (Y axis).
	 * @return bool True if vertical positioning is involved
	 */
	public function affectsVerticalOffset(): bool
	{
		return in_array($this, [
			self::TOP,
			self::BOTTOM,
			self::TOP_LEFT,
			self::TOP_RIGHT,
			self::BOTTOM_LEFT,
			self::BOTTOM_RIGHT,
		], true);
	}

	/**
	 * Check if this position affects horizontal offset (X axis).
	 * @return bool True if horizontal positioning is involved
	 */
	public function affectsHorizontalOffset(): bool
	{
		return in_array($this, [
			self::LEFT,
			self::RIGHT,
			self::TOP_LEFT,
			self::TOP_RIGHT,
			self::BOTTOM_LEFT,
			self::BOTTOM_RIGHT,
		], true);
	}
}