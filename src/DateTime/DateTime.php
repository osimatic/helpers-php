<?php

namespace Osimatic\Helpers\DateTime;

class DateTime
{
	/**
	 * @return \DateTime|null
	 */
	public static function getCurrentDateTime(): ?\DateTime
	{
		try {
			return new \DateTime('now');
		} catch (\Exception $e) {}
		return null;
	}

	/**
	 * @param \DateTime $dateTime
	 * @param int $dateFormatter
	 * @param int $timeFormatter
	 * @param string|null $locale
	 * @return string
	 */
	public static function format(\DateTime $dateTime, int $dateFormatter, int $timeFormatter, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, $dateFormatter, $timeFormatter)->format($dateTime->getTimestamp());
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatDateTime(\DateTime $dateTime, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT)->format($dateTime->getTimestamp());
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatDate(\DateTime $dateTime, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE)->format($dateTime->getTimestamp());
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatTime(\DateTime $dateTime, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::SHORT)->format($dateTime->getTimestamp());
	}

	/**
	 * @param string $str
	 * @return null|\DateTime
	 */
	public static function parse(string $str): ?\DateTime
	{
		try {
			return new \DateTime($str);
		}
		catch (\Exception $e) { }
		return null;
	}

	/**
	 * @param string $str
	 * @return null|\DateTime
	 */
	public static function parseDate(string $str): ?\DateTime
	{
		if (empty($str) || false === SqlDate::check($sqlDate = SqlDate::parse($str))) {
			return null;
		}
		try {
			return new \DateTime($sqlDate.' 00:00:00');
		}
		catch (\Exception $e) { }
		return null;
	}

}