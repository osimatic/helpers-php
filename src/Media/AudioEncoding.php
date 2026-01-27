<?php

namespace Osimatic\Media;

/**
 * Audio encoding formats for PCM (Pulse Code Modulation) audio data.
 * These encodings are used by various audio formats (WAV, AIFF, AU, etc.)
 * and supported by audio processing tools like SoX.
 */
enum AudioEncoding: string
{
	/**
	 * Signed integer PCM encoding (most common for CD-quality audio)
	 * Used in WAV, AIFF, and most modern audio formats
	 */
	case SIGNED_INTEGER = 'signed-integer';

	/**
	 * Unsigned integer PCM encoding (legacy format)
	 * Used in older audio formats and 8-bit audio
	 */
	case UNSIGNED_INTEGER = 'unsigned-integer';

	/**
	 * A-Law compression (G.711 standard)
	 * Used in European telephony systems
	 * Provides 2:1 compression ratio with 8-bit samples
	 */
	case A_LAW = 'a-law';

	/**
	 * μ-Law (mu-law) compression (G.711 standard)
	 * Used in North American and Japanese telephony systems
	 * Provides 2:1 compression ratio with 8-bit samples
	 */
	case U_LAW = 'u-law';

	/**
	 * Floating-point PCM encoding
	 * Used for high-precision audio processing
	 * Common in professional audio applications
	 */
	case FLOATING_POINT = 'floating-point';

	/**
	 * GSM 06.10 compression
	 * Used in mobile telephony
	 * Provides higher compression ratio but lower quality
	 */
	case GSM = 'gsm';

	/**
	 * Get a human-readable description of the encoding format.
	 * @return string Description of the encoding
	 */
	public function getDescription(): string
	{
		return match($this) {
			self::SIGNED_INTEGER => 'Signed Integer PCM',
			self::UNSIGNED_INTEGER => 'Unsigned Integer PCM',
			self::A_LAW => 'A-Law Compression (G.711)',
			self::U_LAW => 'μ-Law Compression (G.711)',
			self::FLOATING_POINT => 'Floating Point PCM',
			self::GSM => 'GSM 06.10 Compression',
		};
	}

	/**
	 * Check if this encoding uses compression.
	 * @return bool True if the encoding is compressed, false for raw PCM
	 */
	public function isCompressed(): bool
	{
		return in_array($this, [self::A_LAW, self::U_LAW, self::GSM], true);
	}

	/**
	 * Check if this encoding is suitable for telephony applications.
	 * @return bool True if the encoding is optimized for telephony
	 */
	public function isTelephonyFormat(): bool
	{
		return in_array($this, [self::A_LAW, self::U_LAW, self::GSM], true);
	}

	/**
	 * Get the typical bit depth for this encoding.
	 * @return int|null Bit depth in bits, or null if variable
	 */
	public function getTypicalBitDepth(): ?int
	{
		return match($this) {
			self::SIGNED_INTEGER, self::UNSIGNED_INTEGER => null, // Variable (8, 16, 24, 32)
			self::A_LAW, self::U_LAW => 8,
			self::FLOATING_POINT => 32,
			self::GSM => null, // Compressed format
		};
	}

	/**
	 * Check if this encoding supports the specified bit depth.
	 * @param int $bitDepth The bit depth to check (e.g., 8, 16, 24, 32)
	 * @return bool True if the encoding supports this bit depth
	 */
	public function supportsBitDepth(int $bitDepth): bool
	{
		return match($this) {
			self::SIGNED_INTEGER, self::UNSIGNED_INTEGER => in_array($bitDepth, [8, 16, 24, 32], true),
			self::A_LAW, self::U_LAW => $bitDepth === 8,
			self::FLOATING_POINT => in_array($bitDepth, [32, 64], true),
			self::GSM => false, // Compressed format doesn't have bit depth
		};
	}
}
