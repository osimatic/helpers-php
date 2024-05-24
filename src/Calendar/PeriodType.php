<?php

namespace Osimatic\Calendar;

enum PeriodType: string
{
	case HOUR = 'hour';
	case DAY_OF_WEEK = 'day_of_week';
	case DAY_OF_MONTH = 'day_of_month';
	case WEEK = 'week';
	case MONTH = 'month';
	case YEAR = 'year';
}