<?php

namespace Osimatic\Helpers\Text;

/**
 * Class Encoding
 * @package Osimatic\Helpers\Text
 */
class Encoding
{
	/**
	 * @param mixed $input
	 * @return mixed
	 */
	public static function utf8Encode($input)
	{
		if (is_string($input)) {
			// if (mb_detect_encoding($input) != "UTF-8") {
			$input = utf8_encode($input);
			// }
		}
		else if (is_array($input)) {
			foreach ($input as $key => $value) {
				$input[self::utf8Encode($key)] = self::utf8Encode($value);
			}
		}
		else if (is_object($input)) {
			$vars = array_keys(get_object_vars($input));
			foreach ($vars as $var) {
				$input->$var = self::utf8Encode($input->$var);
			}
		}
		return $input;
	}

	/**
	 * @param mixed $input
	 * @return mixed
	 */
	public static function utf8Decode($input)
	{
		if (is_string($input)) {
			// if (mb_detect_encoding($input) == "UTF-8") {
			$input = utf8_decode($input);
			// }
		}
		else if (is_array($input)) {
			foreach ($input as $key => $value) {
				$input[self::utf8Decode($key)] = self::utf8Decode($value);
			}
		}
		else if (is_object($input)) {
			$vars = array_keys(get_object_vars($input));
			foreach ($vars as $var) {
				$input->$var = self::utf8Decode($input->$var);
			}
		}
		return $input;
	}

}