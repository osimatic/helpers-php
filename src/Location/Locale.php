<?php

namespace Osimatic\Helpers\Location;

class Locale
{
	/**
	 * @param string|null $defaultLocale
	 * @param string $headerKey
	 */
	public static function setFromHttpHeader(?string $defaultLocale = null, string $headerKey = 'Accept-Language'): void
	{
		$headerKey = mb_strtoupper($headerKey);
		$headerKey = str_replace('-', '_', $headerKey);

		$locale = $defaultLocale ?? locale_get_default();
		if (!empty($acceptLanguage = ($_SERVER['HTTP_'.$headerKey] ?? null))) {
			$locale = $acceptLanguage;
		}

		$locale = str_replace('-', '_', $locale);

		setlocale(LC_ALL, $locale, str_replace('_', '-', $locale));
	}

	/**
	 * @return string
	 */
	public static function get(): string
	{
		return setlocale(LC_ALL, 0);
	}
}