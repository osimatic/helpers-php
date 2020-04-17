<?php

namespace Osimatic\Helpers\ContactDetails;

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
	public function formatLastName(?string $lastName, bool $upperCase=true): string
	{
		if ($lastName != null) {
			$lastName = trim($lastName);
			if ($upperCase) {
				$lastName = mb_strtoupper($lastName);
				//$lastName = mb_strtoupper($lastName, 'UTF-8');
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
	public function format(Name $name, bool $editCase=true)
	{
		//$nameDisplay = $this->formatTitle($name->getTitle(), $locale);
		$nameDisplay = '';
		$nameDisplay = trim($nameDisplay.' '.$this->formatFirstName($name->getFirstName(), $editCase));
		$nameDisplay = trim($nameDisplay.' '.$this->formatLastName($name->getLastName(), $editCase));
		return $nameDisplay;
	}

	private static function ucname($string)
	{
		$string = ucwords(mb_strtolower($string));
		//$string = mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
		foreach (['-', '\''] as $delimiter) {
			if (strpos($string, $delimiter) !== false) {
				$string = implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
				//$string = implode($delimiter, array_map(function($value) {
				//	return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
				//}, explode($delimiter, $string)));
			}
		}
		return $string;
	}

}