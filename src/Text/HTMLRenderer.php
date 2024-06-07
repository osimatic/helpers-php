<?php

namespace Osimatic\Text;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HTMLRenderer
{
	/**
	 * @var \Twig\Environment
	 */
	private \Twig\Environment $twig;

	public function __construct(
		string $templateDir = __DIR__.'/../templates/',
		private LoggerInterface $logger = new NullLogger(),
	) {
		$loader = new \Twig\Loader\FilesystemLoader($templateDir);
		$this->twig = new \Twig\Environment($loader);
		foreach (self::getTwigFilters() as $filter) {
			$this->twig->addFilter($filter);
		}
	}

	/**
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	public function render(string $templateFile, array $templateData=[]): ?string
	{
		try {
			return $this->twig->render($templateFile, $templateData);
		}
		catch (\Twig\Error\LoaderError | \Twig\Error\RuntimeError | \Twig\Error\SyntaxError $e) {
			$this->logger->error($e->getMessage());
		}
		return null;
	}

	public static function getTwigFilters(): array
	{
		return [
			new \Twig\TwigFilter('localized_number', \Osimatic\Number\Number::format(...)),
			new \Twig\TwigFilter('localized_currency', \Osimatic\Bank\Currency::format(...)),
			new \Twig\TwigFilter('localized_currency_with_code', \Osimatic\Bank\Currency::formatWithCode(...)),
			new \Twig\TwigFilter('country_name', \Osimatic\Location\Country::getCountryNameFromCountryCode(...)),
			new \Twig\TwigFilter('name', \Osimatic\Person\Name::formatFromTwig(...)),
			new \Twig\TwigFilter('address', \Osimatic\Location\PostalAddress::format(...)),
			new \Twig\TwigFilter('address_inline', \Osimatic\Location\PostalAddress::formatInline(...)),
			new \Twig\TwigFilter('phone_number_national', \Osimatic\Messaging\PhoneNumber::formatNational(...)),
			new \Twig\TwigFilter('phone_number_international', \Osimatic\Messaging\PhoneNumber::formatInternational(...)),
			new \Twig\TwigFilter('duration_chrono', \Osimatic\Number\Duration::formatHourChrono(...)),
			new \Twig\TwigFilter('localized_date_time', \Osimatic\Calendar\DateTime::formatFromTwig(...)),
			new \Twig\TwigFilter('localized_date', \Osimatic\Calendar\DateTime::formatDateFromTwig(...)),
			new \Twig\TwigFilter('localized_time', \Osimatic\Calendar\DateTime::formatTimeFromTwig(...)),
			new \Twig\TwigFilter('day_name', \Osimatic\Calendar\Date::getDayName(...)),
			new \Twig\TwigFilter('month_name', \Osimatic\Calendar\Date::getMonthName(...)),
			new \Twig\TwigFilter('hour', \Osimatic\Calendar\Time::formatHour(...)),
			new \Twig\TwigFilter('url', \Osimatic\Network\URL::format(...)),
			new \Twig\TwigFilter('file_size', \Osimatic\FileSystem\File::formatSize(...)),
		];
	}

}