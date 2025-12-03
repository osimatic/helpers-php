<?php

if (!function_exists('class_basename')) {
	/**
	 * Get the class "basename" of the given object / class.
	 * Credits: laravel/framework/src/Illuminate/Support/helpers.php
	 *
	 * @param  string|object  $class
	 * @return string
	 */
	function class_basename(string|object $class): string
	{
		$class = is_object($class) ? get_class($class) : $class;

		return basename(str_replace('\\', '/', $class));
	}
}
