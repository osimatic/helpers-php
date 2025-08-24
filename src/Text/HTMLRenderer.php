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

	/**
	 * @param string $templateFile
	 * @param array $templateData
	 * @return string|null
	 */
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

	/**
	 * @return \Twig\TwigFilter[]
	 */
	public static function getTwigFilters(): array
	{
		return [
			new \Twig\TwigFilter('pluralize', \Osimatic\Text\Str::pluralize(...)),
			new \Twig\TwigFilter('localized_number', \Osimatic\Number\Number::format(...)),
			new \Twig\TwigFilter('localized_currency', \Osimatic\Bank\Currency::format(...)),
			new \Twig\TwigFilter('localized_currency_with_code', \Osimatic\Bank\Currency::formatWithCode(...)),
			new \Twig\TwigFilter('country_name', \Osimatic\Location\Country::formatCountryNameFromTwig(...)),
			new \Twig\TwigFilter('name', \Osimatic\Person\Name::formatFromTwig(...)),
			new \Twig\TwigFilter('address', \Osimatic\Location\PostalAddress::format(...)),
			new \Twig\TwigFilter('address_inline', \Osimatic\Location\PostalAddress::formatInline(...)),
			new \Twig\TwigFilter('phone_number_national', \Osimatic\Messaging\PhoneNumber::formatNational(...)),
			new \Twig\TwigFilter('phone_number_international', \Osimatic\Messaging\PhoneNumber::formatInternational(...)),
			new \Twig\TwigFilter('phone_number_country_iso_code', \Osimatic\Messaging\PhoneNumber::getCountryIsoCode(...)),
			new \Twig\TwigFilter('duration_chrono', \Osimatic\Number\Duration::formatNbHours(...)),
			new \Twig\TwigFilter('localized_date_time', \Osimatic\Calendar\DateTime::formatFromTwig(...)),
			new \Twig\TwigFilter('localized_date', \Osimatic\Calendar\DateTime::formatDateFromTwig(...)),
			new \Twig\TwigFilter('localized_time', \Osimatic\Calendar\DateTime::formatTimeFromTwig(...)),
			new \Twig\TwigFilter('day_name', \Osimatic\Calendar\Date::getDayName(...)),
			new \Twig\TwigFilter('month_name', \Osimatic\Calendar\Date::getMonthName(...)),
			new \Twig\TwigFilter('hour', \Osimatic\Calendar\Time::formatHour(...)),
			new \Twig\TwigFilter('url', \Osimatic\Network\URL::format(...)),
			new \Twig\TwigFilter('file_size', \Osimatic\FileSystem\File::formatSize(...)),
			new \Twig\TwigFilter('iban', \Osimatic\Bank\BankAccount::formatIban(...)),
			new \Twig\TwigFilter('currency_symbol', \Symfony\Component\Intl\Currencies::getSymbol(...)),
			new \Twig\TwigFilter('vat_number', \Osimatic\Organization\VatNumber::format(...)),
			new \Twig\TwigFilter('bank_card_number', \Osimatic\Bank\BankCard::formatCardNumber(...)),
			new \Twig\TwigFilter('bank_card_expiration_date', \Osimatic\Bank\BankCard::formatCardExpirationDate(...)),
		];
	}

	public static function getTwigFunctions(): array
	{
		return [
			new \Twig\TwigFunction('enum', self::enum(...)),
		];
	}

	public static function getTwigTests(): array
	{
		return [
			new \Twig\TwigTest('array', is_array(...)),
		];
	}

	public static function enum(string $fullClassName): object
	{
		$parts = explode('::', $fullClassName);
		$className = $parts[0];
		$constant = $parts[1] ?? null;

		if (!enum_exists($className)) {
			throw new \InvalidArgumentException(sprintf('"%s" is not an enum.', $className));
		}

		if ($constant) {
			return constant($fullClassName);
		}

		return new readonly class($fullClassName) {
			public function __construct(private string $fullClassName) {}

			public function __call(string $caseName, array $arguments): mixed
			{
				return call_user_func_array([$this->fullClassName, $caseName], $arguments);
			}
		};
	}
}