<?php

namespace Osimatic\Text;

class HTMLRenderer
{
	/**
	 * @var \Twig\Environment
	 */
	private static \Twig\Environment $twig;

	public static function getInstance(): \Twig\Environment
	{
		if (!isset(self::$twig) || null === self::$twig) {
			$filters = [
				['localized_number', \Osimatic\Number\Number::format(...)],
				['localized_currency', [\Osimatic\Bank\Currency::class, 'format']],
				['localized_currency_with_code', [\Osimatic\Bank\Currency::class, 'formatWithCode']],
				['country_name', [\Osimatic\Location\Country::class, 'getCountryNameFromCountryCode']],
				['name', [\Osimatic\Person\Name::class, 'formatFromTwig']],
				['address', [\Osimatic\Location\PostalAddress::class, 'formatFromTwig']],
				['address_inline', [\Osimatic\Location\PostalAddress::class, 'formatInlineFromTwig']],
				['phone_number_national', [\Osimatic\Messaging\PhoneNumber::class, 'formatNational']],
				['phone_number_international', [\Osimatic\Messaging\PhoneNumber::class, 'formatInternational']],
				['duration_chrono', [\Osimatic\Number\Duration::class, 'formatHourChrono']],
				['localized_date_time', [\Osimatic\Calendar\DateTime::class, 'formatFromTwig']],
				['localized_date', [\Osimatic\Calendar\DateTime::class, 'formatDateFromTwig']],
				['localized_time', [\Osimatic\Calendar\DateTime::class, 'formatTimeFromTwig']],
				['day_name', [\Osimatic\Calendar\Date::class, 'getDayName']],
				['month_name', [\Osimatic\Calendar\Date::class, 'getMonthName']],
				['hour', [\Osimatic\Calendar\Time::class, 'formatHour']],
				['url', [\Osimatic\Network\URL::class, 'format']],
				['file_size', [\Osimatic\FileSystem\File::class, 'formatSize']],
			];

			$loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../templates/');
			self::$twig = new \Twig\Environment($loader);
			foreach ($filters as [$name, $callable]) {
				self::$twig->addFilter(new \Twig\TwigFilter($name, $callable));
			}
		}

		return self::$twig;
	}

	public static function render(string $templateFile, array $templateData=[]): ?string
	{
		try {
			return self::getInstance()->render($templateFile, $templateData);
		}
		catch (\Twig\Error\LoaderError | \Twig\Error\RuntimeError | \Twig\Error\SyntaxError $e) {
			//var_dump($e->getMessage());
			//$logger->error($e->getMessage(), $e->getCode(), $e->getTrace(), $e->getFile(), $e->getLine());
		}
		return null;
	}

}