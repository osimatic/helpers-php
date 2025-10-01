<?php

namespace Osimatic\Person;

/**
 * Class Name
 * @package Osimatic\Helpers\Person
 */
class Name
{
	private Gender $gender = Gender::UNKNOWN;
	private ?string $firstName = null;
	private ?string $lastName = null;

	// ========== Vérification ==========

	/**
	 * @param string|int|null $value
	 * @return bool
	 */
	public static function checkCivility(string|int|null $value): bool
	{
		return preg_match('/[0-2]/', $value);
	}

	/**
	 * @param string|null $value
	 * @param bool $numbersAllowed
	 * @return bool
	 */
	public static function checkFirstName(?string $value, bool $numbersAllowed=false): bool
	{
		return preg_match('/^(['.($numbersAllowed?'0-9':'').'a-zA-Z\'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]{3,120})$/u', $value);
	}

	/**
	 * @param string|null $value
	 * @param bool $numbersAllowed
	 * @return bool
	 */
	public static function checkGivenName(?string $value, bool $numbersAllowed=false): bool
	{
		return self::checkFirstName($value, $numbersAllowed);
	}

	/**
	 * @param string|null $value
	 * @param bool $numbersAllowed
	 * @return bool
	 */
	public static function checkLastName(?string $value, bool $numbersAllowed=false): bool
	{
		return preg_match('/^(['.($numbersAllowed?'0-9':'').'a-zA-Z\'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]{2,120})$/u', $value);
	}

	/**
	 * @param string|null $value
	 * @param bool $numbersAllowed
	 * @return bool
	 */
	public static function checkFamilyName(?string $value, bool $numbersAllowed=false): bool
	{
		return self::checkLastName($value, $numbersAllowed);
	}


	// ========== Affichage ==========

	/**
	 * @param Gender|null $gender
	 * @param string|null $firstName
	 * @param string|null $lastName
	 * @return string|null
	 */
	public static function getFormattedName(?Gender $gender, ?string $firstName, ?string $lastName): ?string
	{
		return (new NameFormatter())->format(
			(new self())
				->setGender($gender ?? Gender::UNKNOWN)
				->setFirstName($firstName)
				->setLastName($lastName)
		);
	}

	/**
	 * @param Name $name
	 * @return string|null
	 */
	public static function formatFromTwig(Name $name): ?string
	{
		return (new NameFormatter())->format($name);
	}

	/**
	 * @return string|null
	 */
	public function format(): ?string
	{
		return (new NameFormatter())->format($this);
	}

	public function __toString()
	{
		return $this->format() ?? '';
	}

	// ========== Fête ==========

	/**
	 * Retourne la fête du prénom d'un jour donné (prénom "principal" indiqué sur le calendrier officiel)
	 * @param int|null $month month of name day (default current month)
	 * @param int|null $day day of month of name day (default current day of month)
	 * @param string $country
	 * @param bool $rare
	 * @return string|null
	 */
	public static function getNameDay(?int $month=null, ?int $day=null, string $country='FR', bool $rare=false): ?string
	{
		return self::getNameDays($month, $day, $country, $rare)[0] ?? null;
	}

	/**
	 * Retourne la liste des fêtes du prénom d'un jour donné
	 * @param int|null $month month of name day (default current month)
	 * @param int|null $day day of month of name day (default current day of month)
	 * @param string $country
	 * @param bool $rare
	 * @return array
	 */
	public static function getNameDays(?int $month=null, ?int $day=null, string $country='FR', bool $rare=false): array
	{
		$month ??= date('m');
		$day ??= date('d');
		return self::getNameDaysList($country, $rare)[sprintf("%02d", $day).'/'.sprintf("%02d", $month)] ?? [];
	}

	private static array $nameDays = [];

	/**
	 * @param string $country
	 * @param bool $rare
	 * @param bool $special
	 * @return array
	 */
	public static function getNameDaysList(string $country='FR', bool $rare=false, bool $special=false): array
	{
		if ((self::$nameDays[$country] ?? null) === null) {
			// self::$nameDays[$country] = str_getcsv(file_get_contents(), ';');
			self::$nameDays[$country] = [];

			$filePath = __DIR__.'/conf/name_days_'.mb_strtolower($country).'.csv';

			if (!file_exists($filePath) || false === ($handle = fopen($filePath, 'r'))) {
				return [];
			}

			$mainNameDays = [];
			while (($firstNameData = fgetcsv($handle, 1000, ';')) !== false) {
				if (count($firstNameData) < 4) continue;

				if (!$rare && (bool)trim($firstNameData[3])) { // si prénom rare, on l'ignore
					continue;
				}

				if (!$special && trim($firstNameData[4] ?? 1) === '0') { // si fête particulière (exemple : Jour de l'an, fêtes chrétiennes…), on l'ignore
					continue;
				}

				$keyDate = trim($firstNameData[1]);
				$firstName = trim($firstNameData[0]);
				if ((bool)trim($firstNameData[2])) { // si prénom "principal"
					$mainNameDays[$keyDate] = $firstName;
					continue;
				}
				self::$nameDays[$country][$keyDate][] = $firstName;
			}

			// ajout des prénoms "principal" en début de chaque tableau quotidien
			foreach ($mainNameDays as $key => $firstName) {
				if (!isset(self::$nameDays[$country][$key])) {
					self::$nameDays[$country][$key] = [];
				}
				array_unshift(self::$nameDays[$country][$key], $firstName);
			}

			fclose($handle);
		}

		return self::$nameDays[$country];
	}

	// ========== Get / Set ==========

	/**
	 * @return Gender
	 */
	public function getGender(): Gender
	{
		return $this->gender;
	}

	/**
	 * @param Gender $gender
	 * @return Name
	 */
	public function setGender(Gender $gender): self
	{
		$this->gender = $gender;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getFirstName(): ?string
	{
		return $this->firstName;
	}

	/**
	 * @param string|null $firstName
	 * @return Name
	 */
	public function setFirstName(?string $firstName): self
	{
		$this->firstName = $firstName;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getLastName(): ?string
	{
		return $this->lastName;
	}

	/**
	 * @param string|null $lastName
	 * @return Name
	 */
	public function setLastName(?string $lastName): self
	{
		$this->lastName = $lastName;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getGivenName(): ?string
	{
		return $this->firstName;
	}

	/**
	 * @param string|null $firstName
	 * @return Name
	 */
	public function setGivenName(?string $firstName): self
	{
		$this->firstName = $firstName;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getFamilyName(): ?string
	{
		return $this->lastName;
	}

	/**
	 * @param string|null $lastName
	 * @return Name
	 */
	public function setFamilyName(?string $lastName): self
	{
		$this->lastName = $lastName;

		return $this;
	}
}
