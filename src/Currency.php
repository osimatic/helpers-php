<?php

namespace Helpers;

class Currency
{
	public static function getCurrencyOfCountry(string $countryCode): string
	{
		return (new \NumberFormatter(
			CountryHelper::getLocaleByCountryCode($countryCode),
			\NumberFormatter::CURRENCY
		))->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
	}
}
