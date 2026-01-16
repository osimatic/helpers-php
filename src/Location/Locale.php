<?php

namespace Osimatic\Location;

use Symfony\Component\HttpFoundation\Request;

/**
 * Utility class for detecting and managing locale settings from HTTP requests and headers.
 * Provides methods to extract locale information from Symfony Request objects or raw HTTP headers, and apply them to PHP's setlocale() for localization.
 */
class Locale
{
	/**
	 * Extract the locale from a Symfony HTTP Request object.
	 * Checks the Accept-Language header to determine the user's preferred locale.
	 * @param Request $request The Symfony Request object
	 * @param string|null $defaultLocale The fallback locale if none is detected (defaults to system locale)
	 * @return string The detected locale string in language_COUNTRY format (e.g., 'fr_FR', 'en_US')
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
	 * Extract locale from a Symfony Request and apply it to PHP's setlocale().
	 * Calls getFromRequest() to detect the locale, then sets it system-wide using setlocale().
	 * @param Request $request The Symfony Request object
	 * @param string|null $defaultLocale The fallback locale if none is detected
	 * @return string The locale that was set
	 */
	public static function setFromRequest(Request $request, ?string $defaultLocale = null): string
	{
		$locale = self::getFromRequest($request, $defaultLocale);
		setlocale(LC_ALL, $locale, str_replace('_', '-', $locale));
		$request->setLocale($locale);
		return $locale;
	}

	/**
	 * Extract the locale from an HTTP header (typically Accept-Language).
	 * Reads directly from $_SERVER superglobal instead of a Symfony Request object.
	 * @param string|null $defaultLocale The fallback locale if header is not present (defaults to system locale)
	 * @param string $headerKey The HTTP header name to check (default: 'Accept-Language')
	 * @return string The detected locale string in language_COUNTRY format
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
	 * Extract locale from an HTTP header and apply it to PHP's setlocale().
	 * Calls getFromHttpHeader() to detect the locale, then sets it system-wide using setlocale().
	 * @param string|null $defaultLocale The fallback locale if header is not present
	 * @param string $headerKey The HTTP header name to check (default: 'Accept-Language')
	 * @return string The locale that was set
	 */
	public static function setFromHttpHeader(?string $defaultLocale = null, string $headerKey = 'Accept-Language'): string
	{
		$locale = self::getFromHttpHeader($defaultLocale, $headerKey);
		setlocale(LC_ALL, $locale, str_replace('_', '-', $locale));
		return $locale;
	}

	/**
	 * Get the current locale set in PHP.
	 * Returns the locale currently configured via setlocale().
	 * @return string The current locale string
	 */
	public static function get(): string
	{
		return setlocale(LC_ALL, 0);
	}
}