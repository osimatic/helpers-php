<?php

namespace Osimatic\Calendar;

/**
 * Enumeration of time period types.
 * Defines various granularities for date and time operations:
 * - MINUTE: Minute-level periods
 * - HOUR: Hour-level periods
 * - DAY: Day-level periods
 * - DAY_OF_WEEK: Specific day of the week (e.g., every Monday)
 * - DAY_OF_MONTH: Specific day of the month (e.g., the 15th)
 * - WEEK: Week-level periods
 * - MONTH: Month-level periods
 * - YEAR: Year-level periods
 */
enum PeriodType: string
{
	case MINUTE = 'minute';
	case HOUR = 'hour';
	case DAY = 'day';
	case DAY_OF_WEEK = 'day_of_week';
	case DAY_OF_MONTH = 'day_of_month';
	case WEEK = 'week';
	case MONTH = 'month';
	case YEAR = 'year';
}