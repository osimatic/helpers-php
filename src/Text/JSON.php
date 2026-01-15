<?php

namespace Osimatic\Text;

/**
 * Utility class for working with JSON (JavaScript Object Notation) data.
 * Provides methods for encoding, decoding, validating, and manipulating JSON strings.
 */
class JSON
{
	/**
	 * Reduces a JSON string by removing leading and trailing comments and whitespace.
	 * Useful for cleaning JSON-with-comments format (JSONC) before parsing.
	 * @param string $str The JSON string to clean
	 * @return string The cleaned JSON string
	 */
	public static function reduce(string $str): string
	{
		$str = preg_replace([
			// eliminate single line comments in '// ...' form
			'#^\s*//(.+)$#m',

			// eliminate multi-line comments in '/* ... */' form, at start of string
			'#^\s*/\*(.+)\*/#Us',

			// eliminate multi-line comments in '/* ... */' form, at end of string
			'#/\*(.+)\*/\s*$#Us'
		], '', $str);

		// eliminate empty lines left by removed comments
		$str = preg_replace('#^\s*\n#m', '', $str);

		// eliminate extraneous space
		return trim($str);
	}

	/**
	 * Validates whether a string is valid JSON.
	 * @param string $json The JSON string to validate
	 * @return bool True if the string is valid JSON, false otherwise
	 */
	public static function validate(string $json): bool
	{
		if (empty($json)) {
			return false;
		}

		json_decode($json);
		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Decodes a JSON string with error handling.
	 * @param string $json The JSON string to decode
	 * @param bool $assoc When true, returned objects will be converted into associative arrays
	 * @param int $depth Maximum nesting depth of the structure being decoded
	 * @param int $flags Bitmask of JSON decode options
	 * @return mixed The decoded value, or null on error
	 */
	public static function decode(string $json, bool $assoc = true, int $depth = 512, int $flags = 0): mixed
	{
		$result = json_decode($json, $assoc, $depth, $flags);
		return json_last_error() === JSON_ERROR_NONE ? $result : null;
	}

	/**
	 * Encodes a value to JSON with error handling.
	 * @param mixed $data The value to encode
	 * @param int $flags Bitmask of JSON encode options
	 * @param int $depth Maximum nesting depth
	 * @return string|null The JSON string, or null on error
	 */
	public static function encode(mixed $data, int $flags = 0, int $depth = 512): ?string
	{
		$result = json_encode($data, $flags, $depth);
		return json_last_error() === JSON_ERROR_NONE ? $result : null;
	}

	/**
	 * Formats a JSON string with proper indentation for readability.
	 * @param string $json The JSON string to format
	 * @return string|null The formatted JSON string, or null if input is invalid
	 */
	public static function prettify(string $json): ?string
	{
		$decoded = json_decode($json);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return null;
		}

		return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Minifies a JSON string by removing unnecessary whitespace.
	 * @param string $json The JSON string to minify
	 * @return string|null The minified JSON string, or null if input is invalid
	 */
	public static function minify(string $json): ?string
	{
		$decoded = json_decode($json);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return null;
		}

		return json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Gets the last JSON error message.
	 * @return string The error message, or empty string if no error
	 */
	public static function getLastError(): string
	{
		return json_last_error_msg();
	}

}