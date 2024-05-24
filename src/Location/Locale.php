<?php

namespace Osimatic\Location;

use Symfony\Component\HttpFoundation\Request;

class Locale
{
	/**
	 * @param Request $request
	 * @param string|null $defaultLocale
	 * @return string
	 */
	public static function getFromRequest(Request $request, ?string $defaultLocale = null): string
	{
		$locale = $defaultLocale ?? locale_get_default();
		if (!empty($acceptLanguage = $request->headers->get('Accept-Language'))) {
			$locale = $acceptLanguage;
		}
		/*if (!$request->attributes->has('locale')) {
			$locale = $request->getLocale();
		}*/
		return str_replace('-', '_', $locale);
	}

	/**
	 * @param Request $request
	 * @param string|null $defaultLocale
	 * @return string
	 */
	public static function setFromRequest(Request $request, ?string $defaultLocale = null): string
	{
		$locale = self::getFromRequest($request, $defaultLocale);
		setlocale(LC_ALL, $locale, str_replace('_', '-', $locale));
		$request->setLocale($locale);
		return $locale;
	}

	/**
	 * @param string|null $defaultLocale
	 * @param string $headerKey
	 * @return string
	 */
	public static function getFromHttpHeader(?string $defaultLocale = null, string $headerKey = 'Accept-Language'): string
	{
		$headerKey = mb_strtoupper($headerKey);
		$headerKey = str_replace('-', '_', $headerKey);

		$locale = $defaultLocale ?? locale_get_default();
		if (!empty($acceptLanguage = ($_SERVER['HTTP_'.$headerKey] ?? null))) {
			$locale = $acceptLanguage;
		}

		return str_replace('-', '_', $locale);
	}

	/**
	 * @param string|null $defaultLocale
	 * @param string $headerKey
	 * @return string
	 */
	public static function setFromHttpHeader(?string $defaultLocale = null, string $headerKey = 'Accept-Language'): string
	{
		$locale = self::getFromHttpHeader($defaultLocale, $headerKey);
		setlocale(LC_ALL, $locale, str_replace('_', '-', $locale));
		return $locale;
	}

	/**
	 * @return string
	 */
	public static function get(): string
	{
		return setlocale(LC_ALL, 0);
	}
}