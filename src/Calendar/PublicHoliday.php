<?php

namespace Osimatic\Calendar;

if (!defined('CAL_HIJRI')) {
	define('CAL_HIJRI', 101);
}

class PublicHoliday
{
	private string $key;
	private PublicHolidayCalendar $calendar = PublicHolidayCalendar::GREGORIAN;
	private int $timestamp;
	private string $name;
	private ?string $fullName = null;
	private bool $isFixedDate = true;

	/**
	 * @param string $name
	 * @param int $timestamp
	 * @param string|null $key
	 * @param string|null $fullName
	 * @param bool $isFixedDate
	 * @param PublicHolidayCalendar $calendar
	 */
	public function __construct(string $name, int $timestamp, ?string $key=null, ?string $fullName=null, bool $isFixedDate=true, PublicHolidayCalendar $calendar=PublicHolidayCalendar::GREGORIAN)
	{
		$key ??= date('m-d', $timestamp);

		// ajout jour de l'année dans le nom
		if (preg_match('/((0[0-9])|(1[1-2]))-(([0-2][0-9])|(3[0-1]))/', $key) !== 0) {
			if ($calendar === PublicHolidayCalendar::HIJRI) {
				[, $hijriMonth, $hijriDay] = IslamicCalendar::convertTimestampToIslamicDate($timestamp);
				$name .= ' ('.$hijriDay.(1 === $hijriDay ? 'er' : '').' '.\Osimatic\Calendar\IslamicCalendar::getMonthName($hijriMonth).')';
			}
			elseif ($calendar === PublicHolidayCalendar::INDIAN) {
				[, $indianMonth, $indianDay] = IndianCalendar::convertTimestampToIndianDate($timestamp);
				$name .= ' ('.$indianDay.(1 === $indianDay ? 'er' : '').' '.\Osimatic\Calendar\IndianCalendar::getMonthName($indianMonth).')';
			}
			else {
				$name .= ' ('.date('d', $timestamp). (1 === ((int)date('d', $timestamp)) ? 'er' : '').' '.\Osimatic\Calendar\Date::getMonthName(date('m', $timestamp)).')';
			}
		}

		$this->key = $key;
		$this->timestamp = $timestamp;
		$this->name = $name;
		$this->fullName = $fullName;
		$this->isFixedDate = $isFixedDate;
		$this->calendar = $calendar;
	}

	public function getMonth(): int
	{
		if ($this->calendar === PublicHolidayCalendar::HIJRI) {
			[, $hijriMonth] = IslamicCalendar::convertTimestampToIslamicDate($this->timestamp);
			return $hijriMonth;
		}
		if ($this->calendar === PublicHolidayCalendar::INDIAN) {
			[, $indianMonth] = IndianCalendar::convertTimestampToIndianDate($this->timestamp);
			return $indianMonth;
		}
		return date('m', $this->timestamp);
	}

	public function getDay(): int
	{
		if ($this->calendar === PublicHolidayCalendar::HIJRI) {
			[,,$hijriDay] = IslamicCalendar::convertTimestampToIslamicDate($this->timestamp);
			return $hijriDay;
		}
		if ($this->calendar === PublicHolidayCalendar::INDIAN) {
			[,,$indianDay] = IndianCalendar::convertTimestampToIndianDate($this->timestamp);
			return $indianDay;
		}
		return date('d', $this->timestamp);
	}




	public function getKey(): string
	{
		return $this->key;
	}

	public function setKey(string $key): void
	{
		$this->key = $key;
	}

	public function getCalendar(): PublicHolidayCalendar
	{
		return $this->calendar;
	}

	public function setCalendar(PublicHolidayCalendar $calendar): void
	{
		$this->calendar = $calendar;
	}

	public function getTimestamp(): int
	{
		return $this->timestamp;
	}

	public function setTimestamp(int $timestamp): void
	{
		$this->timestamp = $timestamp;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getFullName(): ?string
	{
		return $this->fullName;
	}

	public function setFullName(?string $fullName): void
	{
		$this->fullName = $fullName;
	}

	public function isFixedDate(): bool
	{
		return $this->isFixedDate;
	}

	public function setIsFixedDate(bool $isFixedDate): void
	{
		$this->isFixedDate = $isFixedDate;
	}
}