<?php

namespace Osimatic\Helpers\DateTime;

class Time
{
	/**
	 * @param int $hour
	 * @return string
	 */
	public static function formatHour(int $hour): string
	{
		return sprintf('%02d', $hour).'h';
	}

}