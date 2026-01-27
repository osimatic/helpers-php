<?php

namespace Osimatic\Calendar;

/**
 * Utility class for Google Calendar integration.
 * This class provides methods to generate URLs for Google Calendar operations such as creating events, viewing calendars, and managing subscriptions directly from your application.
 */
class GoogleCalendar
{
	/**
	 * Base URL for Google Calendar event edit endpoint.
	 */
	private const string URL_EVENT_EDIT = 'https://calendar.google.com/calendar/r/eventedit';

	/**
	 * Base URL for Google Calendar render endpoint.
	 */
	private const string URL_CALENDAR_RENDER = 'https://calendar.google.com/calendar/r';

	/**
	 * Base URL for Google Calendar embed endpoint.
	 */
	private const string URL_CALENDAR_EMBED = 'https://calendar.google.com/calendar/embed';

	/**
	 * Date format for Google Calendar API (ISO 8601 basic format with UTC timezone).
	 */
	private const string DATE_FORMAT = 'Ymd\THis\Z';

	// ========== Event Creation ==========

	/**
	 * Generates a Google Calendar URL to add a new event.
	 * Creates a pre-filled event creation link that opens in Google Calendar with all event details (title, dates, description, location). The URL uses Google Calendar's event edit endpoint and formats dates according to ISO 8601 standard. The timezone is automatically extracted from the start date DateTime object. All parameters are properly URL-encoded using http_build_query for maximum compatibility.
	 * @param string $eventTitle The title/name of the event to be created
	 * @param \DateTime $eventDateStart The start date and time of the event (timezone is extracted from this object)
	 * @param \DateTime $eventDateEnd The end date and time of the event
	 * @param string|null $eventDetails Optional event description/details (supports plain text and limited HTML)
	 * @param string|null $eventLocation Optional event location/address
	 * @return string The complete Google Calendar URL to create the event (format: https://calendar.google.com/calendar/r/eventedit?...)
	 */
	public static function getAddEventUrl(string $eventTitle, \DateTime $eventDateStart, \DateTime $eventDateEnd, ?string $eventDetails = null, ?string $eventLocation = null): string
	{
		$params = [
			'text' => $eventTitle,
			'dates' => self::formatDateRange($eventDateStart, $eventDateEnd),
			'ctz' => $eventDateStart->getTimezone()->getName(),
			'details' => $eventDetails ?? '',
			'location' => $eventLocation ?? '',
		];

		return self::URL_EVENT_EDIT . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
	}

	/**
	 * Generates a Google Calendar URL to add a new all-day event.
	 * Creates a pre-filled all-day event creation link. All-day events span entire days without specific times. The dates are formatted in YYYYMMDD format (without time component) as required by Google Calendar for all-day events.
	 * @param string $eventTitle The title/name of the all-day event
	 * @param \DateTime $eventDateStart The start date of the event (time component is ignored)
	 * @param \DateTime $eventDateEnd The end date of the event (time component is ignored, should be the day after the last day of the event)
	 * @param string|null $eventDetails Optional event description/details
	 * @param string|null $eventLocation Optional event location/address
	 * @return string The complete Google Calendar URL to create the all-day event
	 */
	public static function getAddAllDayEventUrl(string $eventTitle, \DateTime $eventDateStart, \DateTime $eventDateEnd, ?string $eventDetails = null, ?string $eventLocation = null): string
	{
		$params = [
			'text' => $eventTitle,
			'dates' => $eventDateStart->format('Ymd') . '/' . $eventDateEnd->format('Ymd'),
			'details' => $eventDetails ?? '',
			'location' => $eventLocation ?? '',
		];

		return self::URL_EVENT_EDIT . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
	}

	// ========== Calendar Viewing ==========

	/**
	 * Generates a URL to open Google Calendar at a specific date.
	 * Creates a link that opens Google Calendar and navigates to the specified date, allowing users to view their calendar at that particular day.
	 * @param \DateTime $date The date to navigate to in the calendar
	 * @param string $view The calendar view mode ('day', 'week', 'month', 'year', 'agenda'). Default is 'month'.
	 * @return string The Google Calendar URL for the specified date and view
	 */
	public static function getCalendarViewUrl(\DateTime $date, string $view = 'month'): string
	{
		$params = [
			'mode' => strtoupper($view),
			'dates' => $date->format('Ymd') . '/' . $date->format('Ymd'),
		];

		return self::URL_CALENDAR_RENDER . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
	}

	/**
	 * Generates an embeddable Google Calendar iframe URL.
	 * Creates a URL that can be used in an iframe to embed a Google Calendar on a website. The calendar ID is typically an email address (for personal calendars) or a calendar-specific identifier (for public/shared calendars).
	 * @param string $calendarId The Google Calendar ID (usually an email address like 'user@gmail.com' or a public calendar ID)
	 * @param array<string, mixed> $options Optional configuration options for the embedded calendar (supported keys: 'showTitle', 'showNav', 'showDate', 'showPrint', 'showTabs', 'showCalendars', 'showTz', 'mode', 'height', 'wkst', 'bgcolor', 'color')
	 * @return string The embeddable Google Calendar URL for use in an iframe
	 */
	public static function getEmbedCalendarUrl(string $calendarId, array $options = []): string
	{
		$defaultOptions = [
			'src' => $calendarId,
			'ctz' => date_default_timezone_get(),
			'showTitle' => 1,
			'showNav' => 1,
			'showDate' => 1,
			'showPrint' => 0,
			'showTabs' => 1,
			'showCalendars' => 0,
			'showTz' => 0,
			'mode' => 'MONTH',
			'height' => 600,
			'wkst' => 1, // Week starts on Sunday (1=Sunday, 2=Monday)
		];

		$params = array_merge($defaultOptions, $options);
		$params['src'] = $calendarId; // Ensure src is always the calendar ID

		return self::URL_CALENDAR_EMBED . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
	}

	/**
	 * Generates a URL to subscribe to a public Google Calendar.
	 * Creates a link that allows users to add a public calendar to their own Google Calendar. The calendar must be publicly accessible for this to work.
	 * @param string $calendarId The public Google Calendar ID to subscribe to
	 * @return string The subscription URL for the public calendar
	 */
	public static function getSubscribeCalendarUrl(string $calendarId): string
	{
		$params = [
			'cid' => $calendarId,
		];

		return self::URL_CALENDAR_RENDER . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
	}

	// ========== Helper Methods ==========

	/**
	 * Formats a date range for Google Calendar URLs.
	 * Converts two DateTime objects into the format required by Google Calendar API (YYYYMMDDTHHMMSSZ/YYYYMMDDTHHMMSSZ). Both dates are formatted in UTC timezone using ISO 8601 basic format.
	 * @param \DateTime $start The start date and time
	 * @param \DateTime $end The end date and time
	 * @return string The formatted date range string (format: YYYYMMDDTHHMMSSZ/YYYYMMDDTHHMMSSZ)
	 */
	private static function formatDateRange(\DateTime $start, \DateTime $end): string
	{
		return $start->format(self::DATE_FORMAT) . '/' . $end->format(self::DATE_FORMAT);
	}

	/**
	 * Validates if a string is a valid Google Calendar ID format.
	 * Checks if the provided string matches the expected format for a Google Calendar ID, which is typically an email address or a specific calendar identifier string ending with '@group.calendar.google.com'.
	 * @param string $calendarId The calendar ID to validate
	 * @return bool True if the calendar ID appears to be valid, false otherwise
	 */
	public static function isValidCalendarId(string $calendarId): bool
	{
		// Calendar ID is typically an email address or ends with @group.calendar.google.com
		return filter_var($calendarId, FILTER_VALIDATE_EMAIL) !== false ||
		       str_ends_with($calendarId, '@group.calendar.google.com');
	}

	/**
	 * Extracts the calendar ID from a Google Calendar URL.
	 * Parses a Google Calendar URL and extracts the calendar identifier from it. This is useful when you have a calendar URL and need to get its ID for API operations or embedding.
	 * @param string $calendarUrl The full Google Calendar URL
	 * @return string|null The extracted calendar ID, or null if the URL is invalid or doesn't contain a calendar ID
	 */
	public static function extractCalendarIdFromUrl(string $calendarUrl): ?string
	{
		// Parse URL and extract src or cid parameter
		$parsedUrl = parse_url($calendarUrl);
		if (!isset($parsedUrl['query'])) {
			return null;
		}

		parse_str($parsedUrl['query'], $params);
		return $params['src'] ?? $params['cid'] ?? null;
	}
}