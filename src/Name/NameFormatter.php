<?php

namespace Osimatic\Helpers\Name;

use Symfony\Contracts\Translation\TranslatorInterface;

class NameFormatter
{
	private $translator;

	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @param int|null $title
	 * @param string|null $locale
	 * @return string
	 */
	public function displayTitle(?int $title, ?string $locale=null): string
	{
		if ($title == 1) return $this->translator->trans('title_male', [], null, $locale);
		if ($title == 2) return $this->translator->trans('title_female', [], null, $locale);
		return '';
	}

	/**
	 * @param string|null $firstName
	 * @param bool $ucName
	 * @return string
	 */
	public function displayFirstName(?string $firstName, bool $ucName=true): string
	{
		if ($firstName != null) {
			$firstName = trim($firstName);
			if ($ucName) {
				$firstName = self::ucname($firstName);
			}
			return $firstName;
		}
		return '';
	}

	/**
	 * @param string|null $lastName
	 * @param bool $upperCase
	 * @return string
	 */
	public function displayLastName(?string $lastName, bool $upperCase=true): string
	{
		if ($lastName != null) {
			$lastName = trim($lastName);
			if ($upperCase) {
				$lastName = mb_strtoupper($lastName);
			}
			return $lastName;
		}
		return '';
	}

	/**
	 * @param Name $name
	 * @param bool $editCase
	 * @param string|null $locale
	 * @return string
	 */
	public function display(Name $name, bool $editCase=true, ?string $locale=null)
	{
		$nameDisplay = $this->displayTitle($name->getTitle(), $locale);
		$nameDisplay = trim($nameDisplay.' '.$this->displayFirstName($name->getFirstName(), $editCase));
		$nameDisplay = trim($nameDisplay.' '.$this->displayLastName($name->getLastName(), $editCase));
		return $nameDisplay;
	}

	/**
	 * @param int|null $title
	 * @param string|null $firstName
	 * @param string|null $lastName
	 * @param bool $editCase
	 * @param string|null $locale
	 * @return string
	 */
	public function displayFromData(?int $title, ?string $firstName, ?string $lastName, bool $editCase=true, ?string $locale=null): string
	{
		$name = (new Name())
			->setTitle($title)
			->setFirstName($firstName)
			->setLastName($lastName)
		;
		return $this->display($name, $editCase, $locale);
	}

	private static function ucname($string)
	{
		$string = ucwords(mb_strtolower($string));
		foreach (array('-', '\'') as $delimiter) {
			if (strpos($string, $delimiter) !== false) {
				$string = implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
			}
		}
		return $string;
	}

}