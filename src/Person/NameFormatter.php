<?php

namespace Osimatic\Person;

/**
 * Class NameFormatter
 * @package Osimatic\Person
 */
class NameFormatter
{
	public function __construct()
	{

	}

	/**
	 * @param int|null $title
	 * @return string
	 */
	public function formatTitle(?int $title): string
	{
		//if ($title == 1) return $this->translator->trans('title_male', [], null, $locale);
		//if ($title == 2) return $this->translator->trans('title_female', [], null, $locale);
		return '';
	}

	/**
	 * @param string|null $firstName
	 * @param bool $ucName
	 * @return string
	 */
	public function formatFirstName(?string $firstName, bool $ucName=true): string
	{
		if ($firstName !== null) {
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
	public function formatLastName(?string $lastName, bool $upperCase=true): string
	{
		if ($lastName !== null) {
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
	 * @return string
	 */
	public function format(Name $name, bool $editCase=true): string
	{
		//$nameDisplay = $this->formatTitle($name->getTitle(), $locale);
		$nameDisplay = '';
		$nameDisplay = trim($nameDisplay.' '.$this->formatFirstName($name->getFirstName(), $editCase));
		$nameDisplay = trim($nameDisplay.' '.$this->formatLastName($name->getLastName(), $editCase));
		return $nameDisplay;
	}

	/**
	 * @param string|null $string
	 * @return string
	 */
	public static function ucname(?string $string): string
	{
		$string = mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
		foreach (['-', '\''] as $delimiter) {
			if (str_contains($string, $delimiter)) {
				$string = implode($delimiter, array_map(fn($value) => mb_convert_case($value, MB_CASE_TITLE, 'UTF-8'), explode($delimiter, $string)));
			}
		}
		return $string;
	}

}