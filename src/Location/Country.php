<?php

namespace Osimatic\Location;

use Symfony\Component\Intl\Countries;

/**
 * Utility class for working with countries, locales, and geographic regions.
 * Provides methods for country code validation, name resolution, locale management, and European Union membership checks.
 */
class Country
{
	/**
	 * List of European Union member states (ISO 3166-1 alpha-2 country codes).
	 * @var string[]
	 */
	public const array EUROPEAN_UNION = [
		'DE', // Allemagne
		'AT', // Autriche
		'BE', // Belgique
		'BG', // Bulgarie
		'CY', // Chypre
		'HR', // Croatie
		'DK', // Danemark
		'ES', // Espagne
		'EE', // Estonie
		'FI', // Finlande
		'FR', // France
		'GR', // Grèce
		'HU', // Hongrie
		'IE', // Irlande
		'IT', // Italie
		'LV', // Lettonie
		'LT', // Lituanie
		'LU', // Luxembourg
		'MT', // Malte
		'NL', // Pays-Bas
		'PL', // Pologne
		'PT', // Portugal
		'RO', // Roumanie
		'SK', // Slovaquie
		'SI', // Slovénie
		'SE', // Suède
		'CZ', // République tchèque
	];

	// ========== Locale ==========

	/**
	 * Get a locale string (language_COUNTRY format) from a country code.
	 * Searches through a list of known locales to find one matching the given country.
	 * The list is shuffled to provide varied results when multiple locales exist for a country.
	 * @param string $countryCode The ISO 3166-1 alpha-2 country code (e.g., 'FR', 'US', 'GB')
	 * @return string|null The locale string (e.g., 'fr_FR', 'en_US') or null if not found
	 * @link https://stackoverflow.com/questions/3191664/list-of-all-locales-and-their-short-codes
	 * @link https://stackoverflow.com/questions/10175658/is-there-a-simple-way-to-get-the-language-code-from-a-country-code-in-php
	 */
	public static function getLocaleByCountryCode(string $countryCode): ?string
	{
		$countryCode = strtoupper($countryCode);
		$countryCode = $countryCode==='UK'?'GB':$countryCode;

		$locales = [
			'af_ZA',
			'am_ET',
			'ar_AE',
			'ar_BH',
			'ar_DZ',
			'ar_EG',
			'ar_IQ',
			'ar_JO',
			'ar_KW',
			'ar_LB',
			'ar_LY',
			'ar_MA',
			'ar_OM',
			'ar_QA',
			'ar_SA',
			'ar_SY',
			'ar_TN',
			'ar_YE',
			'az_Cyrl_AZ',
			'az_Latn_AZ',
			'be_BY',
			'bg_BG',
			'bn_BD',
			'bs_Cyrl_BA',
			'bs_Latn_BA',
			'cs_CZ',
			'da_DK',
			'de_AT',
			'de_CH',
			'de_DE',
			'de_LI',
			'de_LU',
			'dv_MV',
			'el_GR',
			'en_AU',
			'en_BZ',
			'en_CA',
			'en_GB',
			'en_IE',
			'en_JM',
			'en_MY',
			'en_NZ',
			'en_SG',
			'en_TT',
			'en_US',
			'en_ZA',
			'en_ZW',
			'es_AR',
			'es_BO',
			'es_CL',
			'es_CO',
			'es_CR',
			'es_DO',
			'es_EC',
			'es_ES',
			'es_GT',
			'es_HN',
			'es_MX',
			'es_NI',
			'es_PA',
			'es_PE',
			'es_PR',
			'es_PY',
			'es_SV',
			'es_US',
			'es_UY',
			'es_VE',
			'et_EE',
			'fa_IR',
			'fi_FI',
			'fil_PH',
			'fo_FO',
			'fr_BE',
			'fr_CA',
			'fr_CH',
			'fr_FR',
			'fr_LU',
			'fr_MC',
			'he_IL',
			'hi_IN',
			'hr_BA',
			'hr_HR',
			'hu_HU',
			'hy_AM',
			'id_ID',
			'ig_NG',
			'is_IS',
			'it_CH',
			'it_IT',
			'ja_JP',
			'ka_GE',
			'kk_KZ',
			'kl_GL',
			'km_KH',
			'ko_KR',
			'ky_KG',
			'lb_LU',
			'lo_LA',
			'lt_LT',
			'lv_LV',
			'mi_NZ',
			'mk_MK',
			'mn_MN',
			'ms_BN',
			'ms_MY',
			'mt_MT',
			'nb_NO',
			'ne_NP',
			'nl_BE',
			'nl_NL',
			'pl_PL',
			'prs_AF',
			'ps_AF',
			'pt_BR',
			'pt_PT',
			'ro_RO',
			'ru_RU',
			'rw_RW',
			'sv_SE',
			'si_LK',
			'sk_SK',
			'sl_SI',
			'sq_AL',
			'sr_Cyrl_BA',
			'sr_Cyrl_CS',
			'sr_Cyrl_ME',
			'sr_Cyrl_RS',
			'sr_Latn_BA',
			'sr_Latn_CS',
			'sr_Latn_ME',
			'sr_Latn_RS',
			'sw_KE',
			'tg_Cyrl_TJ',
			'th_TH',
			'tk_TM',
			'tr_TR',
			'uk_UA',
			'ur_PK',
			'uz_Cyrl_UZ',
			'uz_Latn_UZ',
			'vi_VN',
			'wo_SN',
			'yo_NG',
			'zh_CN',
			'zh_HK',
			'zh_MO',
			'zh_SG',
			'zh_TW'
		];

		shuffle($locales);

		foreach ($locales as $lc) {
			if ($countryCode === \Locale::getRegion($lc)) {
				return $lc;
			}
		}

		return null;
	}


	// ========== Country code ==========

	/**
	 * Parse a country identifier (code or name) and return the ISO country code.
	 * Accepts either a 2-letter ISO code or a full country name.
	 * @param string|null $country The country code (e.g., 'FR') or country name (e.g., 'France')
	 * @return string|null The ISO 3166-1 alpha-2 country code, or null if not found
	 */
	public static function parse(?string $country): ?string
	{
		if (self::isValidCountryCode($country)) {
			return $country;
		}
		if (null !== ($countryCode = self::getCountryCodeFromCountryName($country))) {
			return $countryCode;
		}
		return null;
	}

	/**
	 * Check if a string is a valid ISO 3166-1 alpha-2 country code.
	 * @param string|null $countryIsoCode The country code to validate (e.g., 'FR', 'US', 'GB')
	 * @return bool True if the code is valid, false otherwise
	 */
	public static function isValidCountryCode(?string $countryIsoCode): bool
	{
		if (null === $countryIsoCode || '' === $countryIsoCode) {
			return false;
		}
		return Countries::exists($countryIsoCode);
	}

	/**
	 * Extract the country code from a locale string.
	 * @param string|null $locale The locale string (e.g., 'fr_FR', 'en_US'), or null to use the default locale
	 * @return string|null The ISO 3166-1 alpha-2 country code (e.g., 'FR', 'US')
	 */
	public static function getCountryCodeFromLocale(?string $locale=null): ?string
	{
		if (null === $locale) {
			$locale = \Locale::getDefault();
		}
		return \Locale::getRegion($locale);
	}

	/**
	 * Get the ISO country code from a country name.
	 * Performs a case-insensitive search through all known country names.
	 * @param string|null $countryName The country name (e.g., 'France', 'United States')
	 * @return string|null The ISO 3166-1 alpha-2 country code, or null if not found
	 */
	public static function getCountryCodeFromCountryName(?string $countryName): ?string
	{
		if (null === $countryName) {
			return null;
		}

		$countryName = mb_strtolower($countryName);
		foreach (Countries::getNames() as $countryCode => $name) {
			if (mb_strtolower($name) === $countryName) {
				return $countryCode;
			}
		}
		return null;
	}

	/**
	 * Check if a country is a French overseas territory.
	 * Checks both the country code (RE, GP, MQ, YT, GF) and the postal code (starts with '97').
	 * @param string|null $countryIsoCode The ISO 3166-1 alpha-2 country code
	 * @param string|null $zipCode Optional postal code to check (French overseas territories start with '97')
	 * @return bool True if the country is a French overseas territory
	 */
	public static function isCountryInFranceOverseas(?string $countryIsoCode, ?string $zipCode=null): bool
	{
		$FRANCE_OVERSEAS_COUNTRY_CODES = [
			'RE', // Réunion
			'GP', // Guadeloupe
			'MQ', // Martinique
			'YT', // Mayotte
			'GF', // Guyane
		];
		return in_array($countryIsoCode, $FRANCE_OVERSEAS_COUNTRY_CODES, true) || (!empty($zipCode) && str_starts_with($zipCode, '97'));
	}

	/**
	 * Check if a country is a member of the European Union.
	 * @param string|null $countryIsoCode The ISO 3166-1 alpha-2 country code
	 * @return bool True if the country is an EU member state
	 */
	public static function isCountryInEuropeanUnion(?string $countryIsoCode): bool
	{
		return in_array($countryIsoCode, self::EUROPEAN_UNION, true);
	}

	/**
	 * Get the ISO 3166-1 numeric country code from an alpha-2 country code.
	 * @param string|null $countryIsoCode The ISO 3166-1 alpha-2 country code (e.g., 'FR', 'US')
	 * @return int|null The ISO 3166-1 numeric code (e.g., 250 for France, 840 for USA), or null if not found
	 */
	public static function getCountryNumericCodeFromCountryCode(?string $countryIsoCode): ?int
	{
		if (null === $countryIsoCode || '' === $countryIsoCode) {
			return null;
		}
		try {
			$data = (new \League\ISO3166\ISO3166)->alpha2($countryIsoCode);
			return $data['numeric'];
		}
		catch (\Exception) {}
		return null;
	}


	// ========== Country name ==========

	/**
	 * Get the country name from its ISO code in the current locale.
	 * @param string $countryCode The ISO 3166-1 alpha-2 country code (e.g., 'FR', 'US')
	 * @return string|null The localized country name, or null if not found
	 */
	public static function getCountryNameFromCountryCode(string $countryCode): ?string
	{
		//$locale = self::getLocaleByCountryCode($countryCode);
		if (!empty($countryName = \Locale::getDisplayRegion('-'.$countryCode, \Locale::getDefault()))) {
			return $countryName;
		}
		return null;
	}

	/**
	 * Extract the country name from a locale string.
	 * @param string|null $locale The locale string (e.g., 'fr_FR', 'en_US'), or null to use the default locale
	 * @return string|null The localized country name
	 */
	public static function getCountryNameFromLocale(?string $locale=null): ?string
	{
		if (null === $locale) {
			$locale = \Locale::getDefault();
		}
		return \Locale::getDisplayRegion($locale, \Locale::getDefault());
	}

	/**
	 * Format a country name from a country code for use in Twig templates.
	 * @param string|null $countryIsoCode The ISO 3166-1 alpha-2 country code
	 * @return string The localized country name, or empty string if code is null
	 */
	public static function formatCountryNameFromTwig(?string $countryIsoCode): string
	{
		if (null === $countryIsoCode) {
			return '';
		}
		return Countries::getName($countryIsoCode);
	}

	// ========== Language ==========

	/**
	 * Get the primary language name for a country.
	 * @param string $countryCode The ISO 3166-1 alpha-2 country code (e.g., 'FR', 'US')
	 * @return string|null The localized language name (e.g., 'Français', 'English'), or null if not found
	 */
	public static function getLanguageFromCountryCode(string $countryCode): ?string
	{
		$locale = self::getLocaleByCountryCode($countryCode);
		if (!empty($locale)) {
			return ucfirst(\Locale::getDisplayLanguage($locale, \Locale::getDefault()));
		}
		return null;
	}

	/**
	 * Extract the language name from a locale string.
	 * @param string|null $locale The locale string (e.g., 'fr_FR', 'en_US'), or null to use the default locale
	 * @return string|null The localized language name
	 */
	public static function getLanguageFromLocale(?string $locale=null): ?string
	{
		if (null === $locale) {
			$locale = \Locale::getDefault();
		}
		return \Locale::getDisplayLanguage($locale, \Locale::getDefault());
	}

	// ========== Regional Groups ==========

	/**
	 * Schengen Area member and associated countries.
	 * @var string[]
	 */
	private const array SCHENGEN_AREA = [
		'AT', 'BE', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU',
		'IS', 'IT', 'LV', 'LI', 'LT', 'LU', 'MT', 'NL', 'NO', 'PL',
		'PT', 'SK', 'SI', 'ES', 'SE', 'CH',
	];

	/**
	 * Check if a country is in the Schengen Area.
	 * @param string $countryCode The ISO 3166-1 alpha-2 country code
	 * @return bool True if the country is in the Schengen Area
	 */
	public static function isSchengenArea(string $countryCode): bool
	{
		return in_array($countryCode, self::SCHENGEN_AREA, true);
	}

	/**
	 * Get all countries for a specific continent.
	 * @param Continent $continent The continent enum
	 * @return string[] Array of ISO 3166-1 alpha-2 country codes
	 */
	public static function getCountriesByContinent(Continent $continent): array
	{
		return match($continent) {
			Continent::EUROPE => [
				'AL', 'AD', 'AT', 'BY', 'BE', 'BA', 'BG', 'HR', 'CY', 'CZ',
				'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IS', 'IE', 'IT',
				'XK', 'LV', 'LI', 'LT', 'LU', 'MT', 'MD', 'MC', 'ME', 'NL',
				'MK', 'NO', 'PL', 'PT', 'RO', 'RU', 'SM', 'RS', 'SK', 'SI',
				'ES', 'SE', 'CH', 'UA', 'GB', 'VA',
			],
			Continent::ASIA => [
				'AF', 'AM', 'AZ', 'BD', 'BT', 'BN', 'KH', 'CN', 'GE',
				'HK', 'IN', 'ID', 'JP', 'KZ', 'KG', 'LA', 'MO', 'MY',
				'MV', 'MN', 'MM', 'NP', 'KP', 'PK', 'PH', 'SG', 'KR',
				'LK', 'TW', 'TJ', 'TH', 'TL', 'TM', 'UZ', 'VN',
			],
			Continent::AFRICA => [
				'DZ', 'AO', 'BJ', 'BW', 'BF', 'BI', 'CM', 'CV', 'CF', 'TD',
				'KM', 'CG', 'CD', 'CI', 'DJ', 'EG', 'GQ', 'ER', 'ET', 'GA',
				'GM', 'GH', 'GN', 'GW', 'KE', 'LS', 'LR', 'LY', 'MG', 'MW',
				'ML', 'MR', 'MU', 'MA', 'MZ', 'NA', 'NE', 'NG', 'RW', 'ST',
				'SN', 'SC', 'SL', 'SO', 'ZA', 'SS', 'SD', 'SZ', 'TZ', 'TG',
				'TN', 'UG', 'ZM', 'ZW',
			],
			Continent::NORTH_AMERICA => [
				'AG', 'BS', 'BB', 'BZ', 'CA', 'CR', 'CU', 'DM', 'DO', 'SV',
				'GD', 'GT', 'HT', 'HN', 'JM', 'MX', 'NI', 'PA', 'KN', 'LC',
				'VC', 'TT', 'US',
			],
			Continent::SOUTH_AMERICA => [
				'AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'GY', 'PY', 'PE', 'SR',
				'UY', 'VE',
			],
			Continent::OCEANIA => [
				'AU', 'FJ', 'KI', 'MH', 'FM', 'NR', 'NZ', 'PW', 'PG', 'WS',
				'SB', 'TO', 'TV', 'VU',
			],
			Continent::MIDDLE_EAST => [
				'BH', 'IR', 'IQ', 'IL', 'JO', 'KW', 'LB', 'OM',
				'PS', 'QA', 'SA', 'SY', 'TR', 'AE', 'YE',
			],
			Continent::ANTARCTICA => [
				// Antarctica has no permanent population or country codes
			],
		};
	}

	/**
	 * Get the continent for a specific country.
	 * @param string $countryCode The ISO 3166-1 alpha-2 country code
	 * @return Continent|null The continent enum, or null if not found
	 */
	public static function getContinentByCountry(string $countryCode): ?Continent
	{
		foreach (Continent::cases() as $continent) {
			if (in_array($countryCode, self::getCountriesByContinent($continent), true)) {
				return $continent;
			}
		}
		return null;
	}

	// ========== Flag ==========

	/**
	 * Get the country code to use for displaying a flag emoji or icon.
	 * Maps territories and dependencies to their parent country for flag display purposes.
	 * For example, French overseas territories use the French flag (FR).
	 * @param string|null $countryIsoCode The ISO 3166-1 alpha-2 country code
	 * @return string|null The country code to use for the flag display
	 */
	public static function getFlagCountryIsoCode(?string $countryIsoCode): ?string
	{
		// France
		if (in_array($countryIsoCode, ['YT', 'GF', 'GP', 'MQ', 'RE', 'MF', 'CP', 'WF'], true)) {
			return 'FR';
		}

		// Royaume-Uni
		if (in_array($countryIsoCode, ['SH', 'TA'], true)) {
			return 'GB';
		}

		// Espagne
		if (in_array($countryIsoCode, ['IC'], true)) {
			return 'ES';
		}

		// Australie
		//if (in_array($countryIsoCode, ['CC', 'CX', 'NF'])) {
		//	return 'AU';
		//}

		// Etats-Unis
		//if (in_array($countryIsoCode, ['MP'])) {
		//	return 'US';
		//}

		// Nouvelle-Zélande
		//if (in_array($countryIsoCode, ['TK'])) {
		//	return 'NZ';
		//}

		return $countryIsoCode;
	}


	// ========== DEPRECATED METHODS (Backward Compatibility) ==========

	/**
	 * @deprecated Use isValidCountryCode() instead
	 * @param string|null $countryIsoCode The country code to validate
	 * @return bool True if the code is valid, false otherwise
	 */
	public static function checkCountryCode(?string $countryIsoCode): bool
	{
		return self::isValidCountryCode($countryIsoCode);
	}

}