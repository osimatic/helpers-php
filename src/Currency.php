<?php

namespace Osimatic\Helpers;

class Currency
{
	public static function getCurrencyOfCountry(string $countryCode): string
	{
		return (new \NumberFormatter(
			Country::getLocaleByCountryCode($countryCode),
			\NumberFormatter::CURRENCY
		))->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
	}
}
