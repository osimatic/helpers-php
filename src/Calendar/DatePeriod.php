<?php

namespace Osimatic\Helpers\Calendar;

/**
 * Class DatePeriod
 * @package Osimatic\Helpers\Calendar
 */
class DatePeriod
{

	/**
	 * @param string $groupBy
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return array
	 */
	public function getListOfPeriod(string $groupBy, \DateTime $periodStart, \DateTime $periodEnd): ?array
	{
		if ($groupBy === 'hour') {
			return ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
		}
		if ($groupBy === 'day_of_month') {
			return $this->getPeriod($periodStart, $periodEnd, 'P1D', 'Y-m-d');
		}
		if ($groupBy === 'week') {
			return $this->getPeriod($periodStart, $periodEnd, 'P1W', 'Y-W');
		}
		if ($groupBy === 'month') {
			return $this->getPeriod($periodStart, $periodEnd, 'P1M', 'Y-n');
		}
		if ($groupBy === 'day_of_week') {
			return ['0', '1', '2', '3', '4', '5', '6'];
		}

		return null;
	}

	/**
	 * @param $startDate
	 * @param $endDate
	 * @param $dateInterval
	 * @param $dateFormat
	 * @return array|null
	 */
	private function getPeriod(\DateTime $startDate, \DateTime $endDate, $dateInterval, $dateFormat): ?array
	{
		if ($dateInterval === 'P1D') {
			$startIntervalDate = $startDate;
			$endIntervalDate = $endDate->modify('+1 day');
		}
		elseif ($dateInterval === 'P1W') {
			$startIntervalDate = $startDate->modify('Monday this week');
			$endIntervalDate = $endDate->modify('this Sunday');
		}
		elseif ($dateInterval === 'P1M') {
			$startIntervalDate = $startDate->modify('first day of this month');
			$endIntervalDate = $endDate->modify('last day of this month');
		}
		else {
			return null;
		}

		try {
			$interval = new \DateInterval($dateInterval);
		} catch (\Exception $e) {
			return null;
		}

		//var_dump($startIntervalDate->format('Y-m-d'));
		//var_dump($endIntervalDate->format('Y-m-d'));

		$dateRange = new \DatePeriod($startIntervalDate, $interval, $endIntervalDate);
		$periodList = [];
		foreach ($dateRange as $date) {
			$periodList[] = $date->format($dateFormat);
		}

		//var_dump($periodList);
		return $periodList;
	}

}