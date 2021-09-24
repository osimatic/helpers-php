<?php

namespace Osimatic\Helpers\Text;

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
				['localized_number', [\Osimatic\Helpers\Number\Number::class, 'format']],
				['localized_currency', [\Osimatic\Helpers\Bank\Currency::class, 'format']],
				['localized_currency_with_code', [\Osimatic\Helpers\Bank\Currency::class, 'formatWithCode']],
				['country_name', [\Osimatic\Helpers\Location\Country::class, 'getCountryNameByCountryCode']],
				['name', [\Osimatic\Helpers\Person\Name::class, 'formatFromTwig']],
				['address', [\Osimatic\Helpers\Location\PostalAddress::class, 'formatFromTwig']],
				['address_inline', [\Osimatic\Helpers\Location\PostalAddress::class, 'formatInlineFromTwig']],
				['phone_number_national', [\Osimatic\Helpers\Messaging\PhoneNumber::class, 'formatNational']],
				['phone_number_international', [\Osimatic\Helpers\Messaging\PhoneNumber::class, 'formatInternational']],
				['duration_chrono', [\Osimatic\Helpers\Number\Duration::class, 'formatHourChrono']],
				['localized_date_time', [\Osimatic\Helpers\Calendar\DateTime::class, 'formatFromTwig']],
				['localized_date', [\Osimatic\Helpers\Calendar\DateTime::class, 'formatDateFromTwig']],
				['localized_time', [\Osimatic\Helpers\Calendar\DateTime::class, 'formatTimeFromTwig']],
				['day_name', [\Osimatic\Helpers\Calendar\Date::class, 'getDayName']],
				['month_name', [\Osimatic\Helpers\Calendar\Date::class, 'getMonthName']],
				['hour', [\Osimatic\Helpers\Calendar\Time::class, 'formatHour']],
				['url', [\Osimatic\Helpers\Network\URL::class, 'format']],
				['file_size', [\Osimatic\Helpers\FileSystem\File::class, 'formatSize']],
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