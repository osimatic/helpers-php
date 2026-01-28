<?php

namespace Osimatic\Calendar;

use Symfony\Component\HttpFoundation\Response;

/**
 * Utility class for iCalendar (ICS) file operations.
 * This class provides comprehensive methods for generating, parsing, validating, and outputting iCalendar (ICS) files. It supports event creation, file generation, parsing of existing ICS files, recurrence rules, attendees, alarms, and advanced ICS properties. The class follows the iCalendar specification (RFC 5545) for calendar data exchange.
 * Organized categories:
 * - Constants: Standard file extensions and formatting constants
 * - Validation: File validation, content validation, and syntax checking
 * - Output: HTTP response and browser download methods
 * - Generation: Basic and extended ICS content creation and file generation
 * - Parsing: ICS file parsing and event extraction with error handling
 * - Utilities: Helper methods for event count, single events, and content manipulation
 * @author Benoit Guiraudou <guiraudou@osimatic.com>
 * @link https://en.wikipedia.org/wiki/ICalendar
 * @link https://datatracker.ietf.org/doc/html/rfc5545
 */
class ICS
{
	// ========== Constants ==========

	/**
	 * Standard ICS file extension
	 */
	public const string FILE_EXTENSION = '.ics';

	/**
	 * All supported iCalendar file extensions
	 * Includes .ics (iCalendar), .vcs (vCalendar), .ical (Apple iCal), .ifb (FreeBusy)
	 */
	public const array FILE_EXTENSIONS = ['.ics', '.vcs', '.ical', '.ifb'];

	/**
	 * Line break format for ICS files (CRLF as per RFC 5545)
	 */
	public const string LN = "\r\n";

	// ========== Validation Methods ==========

	/**
	 * Validates if a file is a valid iCalendar file based on extension.
	 * Checks if the file extension matches one of the supported iCalendar formats (.ics, .vcs, .ical, .ifb). This is a basic validation that only checks the file extension, not the file content or structure.
	 * @param string $filePath The full path to the file to validate
	 * @param string $clientOriginalName The original filename (typically from upload)
	 * @return bool True if file extension is valid for iCalendar, false otherwise
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, self::FILE_EXTENSIONS);
	}

	/**
	 * Validates ICS content structure and syntax.
	 * Performs detailed validation of ICS content structure following RFC 5545 specification. Checks for matching BEGIN/END blocks, required properties (VCALENDAR, VERSION, PRODID), and proper formatting. Returns true if valid, false otherwise. Optionally populates an errors array with detailed error messages for debugging.
	 * @param string $content ICS content string to validate
	 * @param string[]|null &$errors Optional array passed by reference to collect error messages (empty if content is valid)
	 * @return bool True if content has valid ICS structure, false otherwise
	 */
	public static function validateContent(string $content, ?array &$errors = null): bool
	{
		$errors = [];

		// Check for VCALENDAR wrapper
		if (!str_contains($content, 'BEGIN:VCALENDAR')) {
			$errors[] = 'Missing BEGIN:VCALENDAR';
		}
		if (!str_contains($content, 'END:VCALENDAR')) {
			$errors[] = 'Missing END:VCALENDAR';
		}

		// Check for VERSION
		if (!str_contains($content, 'VERSION:2.0')) {
			$errors[] = 'Missing or invalid VERSION (must be 2.0)';
		}

		// Check for PRODID
		if (!str_contains($content, 'PRODID:')) {
			$errors[] = 'Missing PRODID';
		}

		// Count BEGIN:VEVENT and END:VEVENT - they must match
		$beginCount = substr_count($content, 'BEGIN:VEVENT');
		$endCount = substr_count($content, 'END:VEVENT');
		if ($beginCount !== $endCount) {
			$errors[] = "Mismatched VEVENT blocks (BEGIN: $beginCount, END: $endCount)";
		}

		return empty($errors);
	}

	// ========== Output Methods ==========

	/**
	 * Outputs an ICS file directly to the browser for download.
	 * Forces a download of the ICS file with proper headers (Content-Type: text/calendar). No output should be performed before or after calling this function as it sends headers and file content directly to the browser.
	 * @param string $filePath The full path to the ICS file to output
	 * @param string|null $fileName Optional custom filename for download (default: uses original filename)
	 */
	public static function output(string $filePath, ?string $fileName=null): void
	{
		\Osimatic\FileSystem\File::output($filePath, $fileName, 'text/calendar');
	}

	/**
	 * Creates a Symfony HTTP response for downloading an ICS file.
	 * Generates a Symfony Response object configured for ICS file download. This is useful in Symfony applications where you need to return a response object rather than directly outputting content.
	 * @param string $filePath The full path to the ICS file
	 * @param string|null $fileName Optional custom filename for download (default: uses original filename)
	 * @return Response Symfony Response object configured for ICS file download with Content-Type: text/calendar
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): Response
	{
		return \Osimatic\FileSystem\File::getHttpResponse($filePath, $fileName, false, 'text/calendar');
	}

	// ========== Basic Generation Methods ==========

	/**
	 * Generates ICS content as a string from an array of events.
	 * Creates a complete iCalendar (ICS) format string following RFC 5545 specification. The generated content includes VCALENDAR header, all event details (VEVENT blocks), and proper formatting. Each event must implement EventInterface which provides title, dates, location, organizer, and description.
	 * @param EventInterface[] $events Array of event objects implementing EventInterface
	 * @param bool $includeTimezone Whether to include timezone suffix 'Z' for UTC times (default: false)
	 * @return string Complete ICS content ready to be written to file or sent as response
	 */
	public static function getContent(array $events, bool $includeTimezone = false): string
	{
		// Build ICS properties - add header
		$props = [
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'PRODID:-//hacksw/handcal//NONSGML v1.0//EN',
			'CALSCALE:GREGORIAN',
		];

		// Build ICS properties - add values
		foreach ($events as $event) {
			$props = array_merge($props, self::getEventProperties($event, $includeTimezone));
		}

		// Build ICS properties - add footer
		$props[] = 'END:VCALENDAR';

		return implode(self::LN, $props);
	}

	/**
	 * Generates and saves an ICS file from an array of events.
	 * Creates a physical ICS file on the filesystem with the provided events. The file is created with proper ICS formatting and can be imported by calendar applications. The directory is automatically created if it doesn't exist.
	 * @param EventInterface[] $events Array of event objects implementing EventInterface
	 * @param string $filePath The full path where the ICS file should be saved (including filename with .ics extension)
	 * @param bool $includeTimezone Whether to include timezone suffix in dates (default: false)
	 */
	public static function generateFile(array $events, string $filePath, bool $includeTimezone = false): void
	{
		\Osimatic\FileSystem\FileSystem::initializeFile($filePath);

		file_put_contents($filePath, self::getContent($events, $includeTimezone));
	}

	// ========== Extended Generation Methods ==========

	/**
	 * Generates ICS content with advanced properties (attendees, reminders, recurrence).
	 * Creates ICS content with extended features beyond basic events. Supports ATTENDEE properties, VALARM (reminders), RRULE (recurrence rules), and other advanced ICS features. Use this for complex calendar events requiring full RFC 5545 support.
	 * @param EventInterface[] $events Array of event objects implementing EventInterface
	 * @param array $options Optional settings: ['include_attendees' => bool, 'include_alarms' => bool, 'include_timezone' => bool, 'alarm_minutes' => int]
	 * @return string Complete ICS content with extended properties
	 */
	public static function getExtendedContent(array $events, array $options = []): string
	{
		$defaults = [
			'include_attendees' => false,
			'include_alarms' => false,
			'include_timezone' => false,
			'alarm_minutes' => 15, // Default reminder 15 minutes before
		];
		$options = array_merge($defaults, $options);

		// Build ICS properties - add header
		$props = [
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'PRODID:-//hacksw/handcal//NONSGML v1.0//EN',
			'CALSCALE:GREGORIAN',
			'METHOD:REQUEST',
		];

		// Build ICS properties - add events with extended properties
		foreach ($events as $event) {
			$props = array_merge($props, self::getEventProperties($event, $options['include_timezone'], $options));
		}

		// Build ICS properties - add footer
		$props[] = 'END:VCALENDAR';

		return implode(self::LN, $props);
	}

	/**
	 * Adds a single event to existing ICS content.
	 * Parses existing ICS content, adds a new event, and returns the updated content. The new event is inserted before the END:VCALENDAR closing tag. Useful for incrementally building ICS files or updating existing calendars.
	 * @param string $existingContent Existing ICS content (must be valid ICS format)
	 * @param EventInterface $event Event object to add
	 * @param bool $includeTimezone Whether to include timezone suffix in dates (default: false)
	 * @return string Updated ICS content with the new event added
	 */
	public static function addEventToContent(string $existingContent, EventInterface $event, bool $includeTimezone = false): string
	{
		// Remove END:VCALENDAR
		$content = str_replace('END:VCALENDAR', '', $existingContent);

		// Add new event
		$eventProps = self::getEventProperties($event, $includeTimezone);
		$content .= implode(self::LN, $eventProps) . self::LN;

		// Add closing tag
		$content .= 'END:VCALENDAR';

		return $content;
	}

	// ========== Parsing Methods ==========

	/**
	 * Parses an ICS file and extracts events into EventInterface objects.
	 * Reads an existing ICS file and converts each VEVENT entry into an EventInterface object. Uses the ICal library for parsing and handles timezone conversions. The baseEvent parameter is cloned for each parsed event to preserve object type and default values. Optionally validates file existence and content structure, populating an errors array with detailed error messages. Returns an empty array if parsing fails when errors array is not provided.
	 * @param string $filePath The full path to the ICS file to parse
	 * @param EventInterface $baseEvent Base event object to clone for each parsed event (preserves object type)
	 * @param string $timezone Timezone identifier for date parsing (default: 'UTC'). Examples: 'Europe/Paris', 'America/New_York'
	 * @param string[]|null &$errors Optional array passed by reference to collect error messages (file not found, validation errors, parsing errors)
	 * @return EventInterface[] Array of parsed event objects (empty array if parsing fails)
	 */
	public static function parseFile(string $filePath, EventInterface $baseEvent, string $timezone='UTC', ?array &$errors = null): array
	{
		$errors = [];

		// Validate file existence and readability
		if (!file_exists($filePath)) {
			$errors[] = "ICS file not found: $filePath";
			return [];
		}

		if (!is_readable($filePath)) {
			$errors[] = "ICS file is not readable: $filePath";
			return [];
		}

		// Read file content
		$content = file_get_contents($filePath);
		if ($content === false) {
			$errors[] = "Failed to read ICS file: $filePath";
			return [];
		}

		// Validate ICS content structure
		$validationErrors = [];
		if (!self::validateContent($content, $validationErrors)) {
			$errors = array_merge($errors, $validationErrors);
			return [];
		}

		// Parse ICS file
		$eventList = [];
		try {
			$ical = new \ICal\ICal($filePath, array(
				'defaultSpan'                 => 2,
				'defaultTimeZone'             => $timezone,
				'defaultWeekStart'            => 'MO',
				'disableCharacterReplacement' => false,
				'filterDaysAfter'             => null,
				'filterDaysBefore'            => null,
				'httpUserAgent'               => null,
				'skipRecurrence'              => false,
			));

			foreach ($ical->events() as $iCalEvent) {
				/** @var \ICal\Event $iCalEvent */
				$event = clone $baseEvent;
				$event->setSummary($iCalEvent->summary);
				$event->setDescription($iCalEvent->description);
				$event->setStartDate($ical->iCalDateToDateTime($iCalEvent->dtstart_tz));
				$event->setEndDate($ical->iCalDateToDateTime($iCalEvent->dtend_tz));
				$event->getOrganizer()?->setFamilyName($iCalEvent->organizer);
				$event->setLocation($iCalEvent->location);
				$eventList[] = $event;
			}
		} catch (\Exception $e) {
			$errors[] = "Failed to parse ICS events: " . $e->getMessage();
			return [];
		}

		return $eventList;
	}

	// ========== Utility Methods ==========

	/**
	 * Extracts event count from ICS content without full parsing.
	 * Counts the number of VEVENT blocks in ICS content. This is a fast way to get event count without the overhead of full parsing. Useful for previewing large ICS files or validating expected event counts.
	 * @param string $content ICS content string
	 * @return int Number of events (VEVENT blocks) found in the content
	 */
	public static function getEventCount(string $content): int
	{
		return substr_count($content, 'BEGIN:VEVENT');
	}

	/**
	 * Generates ICS content for a single event (quick shortcut).
	 * Convenience method for generating ICS content with just one event. Equivalent to calling getContent([$event]) but more explicit and readable when working with single events.
	 * @param EventInterface $event Single event object
	 * @param bool $includeTimezone Whether to include timezone suffix in dates (default: false)
	 * @return string ICS content containing one event
	 */
	public static function getSingleEventContent(EventInterface $event, bool $includeTimezone = false): string
	{
		return self::getContent([$event], $includeTimezone);
	}

	// ========== Private Helper Methods ==========

	/**
	 * Generates all ICS properties for a single event.
	 * Creates the complete VEVENT block with all properties (SUMMARY, DTSTART, DTEND, LOCATION, etc.). Supports extended properties like attendees, alarms, and recurrence rules when options are provided.
	 * @param EventInterface $event Event object
	 * @param bool $includeTimezone Whether to include timezone suffix
	 * @param array $options Extended options (include_attendees, include_alarms, alarm_minutes, recurrence_rule)
	 * @return string[] Array of ICS property lines for this event
	 */
	private static function getEventProperties(EventInterface $event, bool $includeTimezone = false, array $options = []): array
	{
		$props = [];
		$props[] = 'BEGIN:VEVENT';
		$props[] = 'DESCRIPTION:' . self::escapeString($event->getDescription());
		$props[] = 'DTSTART:' . self::getFormattedDateTime($event->getStartDate(), $includeTimezone);
		$props[] = 'DTEND:' . self::getFormattedDateTime($event->getEndDate(), $includeTimezone);
		$props[] = 'DTSTAMP:' . self::getFormattedDateTime(new \DateTime(), $includeTimezone);
		$props[] = 'LOCATION:' . self::escapeString(Event::getLocationName($event));
		$props[] = 'SUMMARY:' . self::escapeString($event->getSummary());

		// Organizer
		$organizerName = Event::getOrganizerName($event);
		$organizerEmail = Event::getOrganizerEmail($event);
		if (!empty($organizerEmail)) {
			$props[] = 'ORGANIZER;RSVP=TRUE;CN=' . self::escapeString($organizerName) . ';PARTSTAT=ACCEPTED;ROLE=CHAIR:mailto:' . self::escapeString($organizerEmail);
		}

		// URL
		$url = $event->getUrl();
		if (!empty($url)) {
			$props[] = 'URL;VALUE=URI:' . self::escapeString($url);
		}

		// UID
		$props[] = 'UID:' . uniqid('', true);

		// Extended properties: Attendees
		if (!empty($options['include_attendees']) && method_exists($event, 'getAttendees')) {
			foreach ($event->getAttendees() as $attendee) {
				$props[] = 'ATTENDEE;RSVP=TRUE;PARTSTAT=NEEDS-ACTION;ROLE=REQ-PARTICIPANT:mailto:' . self::escapeString($attendee);
			}
		}

		// Extended properties: Recurrence rule
		if (!empty($options['recurrence_rule'])) {
			$props[] = 'RRULE:' . $options['recurrence_rule'];
		}

		// Extended properties: Alarm/Reminder
		if (!empty($options['include_alarms'])) {
			$alarmMinutes = $options['alarm_minutes'] ?? 15;
			$props[] = 'BEGIN:VALARM';
			$props[] = 'ACTION:DISPLAY';
			$props[] = 'DESCRIPTION:' . self::escapeString($event->getSummary());
			$props[] = 'TRIGGER:-PT' . $alarmMinutes . 'M';
			$props[] = 'END:VALARM';
		}

		$props[] = 'END:VEVENT';

		return $props;
	}

	/**
	 * Escapes special characters in ICS text values.
	 * Escapes commas, semicolons, and newlines as required by RFC 5545 specification. These characters have special meaning in ICS format and must be escaped when used in text values. Newlines are replaced with \n literal.
	 * @param string|null $str The string to escape
	 * @return string|null The escaped string, or null if input is null
	 */
	private static function escapeString(?string $str): ?string
	{
		if ($str === null) {
			return null;
		}

		// Escape special characters according to RFC 5545 (backslash must be escaped first)
		return str_replace(
			['\\', ',', ';', "\n", "\r"],
			['\\\\', '\\,', '\\;', '\\n', ''],
			$str
		);
	}

	/**
	 * Formats a DateTime object for ICS date-time fields with timezone support.
	 * Converts a PHP DateTime object to ICS format (YYYYMMDDTHHMMSS or YYYYMMDDTHHMMSSZ). When includeTimezone is true, dates are converted to UTC and formatted with 'Z' suffix as required by RFC 5545 for UTC times.
	 * @param \DateTime $dateTime The DateTime object to format
	 * @param bool $includeTimezone Whether to include timezone 'Z' suffix for UTC times (default: false)
	 * @return string Formatted date-time string in ICS format (e.g., "20240115T143000" or "20240115T143000Z")
	 */
	private static function getFormattedDateTime(\DateTime $dateTime, bool $includeTimezone = false): string
	{
		if ($includeTimezone) {
			// Convert to UTC and add Z suffix
			$utcDateTime = clone $dateTime;
			$utcDateTime->setTimezone(new \DateTimeZone('UTC'));
			return $utcDateTime->format('Ymd\THis\Z');
		}

		// Format without timezone
		return $dateTime->format('Ymd\THis');
	}
}

/*
EXAMPLE ICS FILE STRUCTURE:

BEGIN:VCALENDAR
PRODID:-//Mozilla.org/NONSGML Mozilla Calendar V1.1//EN
VERSION:2.0
METHOD:REQUEST
BEGIN:VTIMEZONE
TZID:Europe/Paris
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
CREATED:20181228T111045Z
LAST-MODIFIED:20181228T111131Z
DTSTAMP:20181228T111131Z
UID:f19c98c7-b409-465c-b296-a5837d3b3a6e
SUMMARY:myConf Conference
ORGANIZER;RSVP=TRUE;CN=Benoit Guiraudou;PARTSTAT=ACCEPTED;ROLE=CHAIR:mailto:benoit.guiraudou@free.fr
ATTENDEE;RSVP=TRUE;PARTSTAT=NEEDS-ACTION;ROLE=REQ-PARTICIPANT:mailto:guiraudou@osimatic.com
DTSTART;TZID=Europe/Paris:20181123T130000
DTEND;TZID=Europe/Paris:20181123T140000
TRANSP:OPAQUE
BEGIN:VALARM
ACTION:DISPLAY
DESCRIPTION:myConf Conference
TRIGGER:-PT15M
END:VALARM
RRULE:FREQ=WEEKLY;BYDAY=MO,WE,FR;COUNT=10
END:VEVENT
END:VCALENDAR
*/
