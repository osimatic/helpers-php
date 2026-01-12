<?php

namespace Osimatic\Text;

class JSON
{
	/**
	 * Reduce a JSON string by removing leading and trailing comments and whitespace
	 * @param $str string string value to strip of comments and whitespace
	 * @return string string value stripped of comments and whitespace
	 */
	public static function reduce(string $str): string
	{
		$str = preg_replace(array(
			// eliminate single line comments in '// ...' form
			'#^\s*//(.+)$#m',

			// eliminate multi-line comments in '/* ... */' form, at start of string
			'#^\s*/\*(.+)\*/#Us',

			// eliminate multi-line comments in '/* ... */' form, at end of string
			'#/\*(.+)\*/\s*$#Us'
		), '', $str);

		// eliminate empty lines left by removed comments
		$str = preg_replace('#^\s*\n#m', '', $str);

		// eliminate extraneous space
		return trim($str);
	}

}