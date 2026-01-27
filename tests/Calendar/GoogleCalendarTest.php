<?php

declare(strict_types=1);

namespace Tests\Calendar;

use Osimatic\Calendar\GoogleCalendar;
use PHPUnit\Framework\TestCase;

final class GoogleCalendarTest extends TestCase
{
	// ========== getAddEventUrl() ==========

	public function testGetAddEventUrlWithAllParameters(): void
	{
		$title = 'Team Meeting';
		$start = new \DateTime('2024-03-15 14:00:00', new \DateTimeZone('Europe/Paris'));
		$end = new \DateTime('2024-03-15 15:30:00', new \DateTimeZone('Europe/Paris'));
		$details = 'Quarterly review meeting';
		$location = 'Conference Room A';

		$url = GoogleCalendar::getAddEventUrl($title, $start, $end, $details, $location);

		$this->assertIsString($url);
		$this->assertStringStartsWith('https://calendar.google.com/calendar/r/eventedit', $url);
		$this->assertStringContainsString('text=Team%20Meeting', $url);
		$this->assertStringContainsString('dates=20240315T140000Z%2F20240315T153000Z', $url);
		$this->assertStringContainsString('ctz=Europe%2FParis', $url);
		$this->assertStringContainsString('details=Quarterly%20review%20meeting', $url);
		$this->assertStringContainsString('location=Conference%20Room%20A', $url);
	}

	public function testGetAddEventUrlWithoutOptionalParameters(): void
	{
		$title = 'Simple Event';
		$start = new \DateTime('2024-06-20 10:00:00', new \DateTimeZone('UTC'));
		$end = new \DateTime('2024-06-20 11:00:00', new \DateTimeZone('UTC'));

		$url = GoogleCalendar::getAddEventUrl($title, $start, $end);

		$this->assertIsString($url);
		$this->assertStringStartsWith('https://calendar.google.com/calendar/r/eventedit', $url);
		$this->assertStringContainsString('text=Simple%20Event', $url);
		$this->assertStringContainsString('dates=20240620T100000Z%2F20240620T110000Z', $url);
		$this->assertStringContainsString('ctz=UTC', $url);
	}

	public function testGetAddEventUrlWithNullDetails(): void
	{
		$title = 'Event Without Details';
		$start = new \DateTime('2024-01-01 00:00:00', new \DateTimeZone('America/New_York'));
		$end = new \DateTime('2024-01-01 01:00:00', new \DateTimeZone('America/New_York'));
		$location = 'New York Office';

		$url = GoogleCalendar::getAddEventUrl($title, $start, $end, null, $location);

		$this->assertIsString($url);
		$this->assertStringContainsString('text=Event%20Without%20Details', $url);
		$this->assertStringContainsString('location=New%20York%20Office', $url);
		$this->assertStringContainsString('details=', $url);
	}

	public function testGetAddEventUrlWithNullLocation(): void
	{
		$title = 'Virtual Meeting';
		$start = new \DateTime('2024-07-10 16:00:00', new \DateTimeZone('Asia/Tokyo'));
		$end = new \DateTime('2024-07-10 17:00:00', new \DateTimeZone('Asia/Tokyo'));
		$details = 'Online meeting via Zoom';

		$url = GoogleCalendar::getAddEventUrl($title, $start, $end, $details, null);

		$this->assertIsString($url);
		$this->assertStringContainsString('text=Virtual%20Meeting', $url);
		$this->assertStringContainsString('details=Online%20meeting%20via%20Zoom', $url);
		$this->assertStringContainsString('location=', $url);
	}

	public function testGetAddEventUrlWithSpecialCharacters(): void
	{
		$title = 'Meeting: Q&A Session (Important!)';
		$start = new \DateTime('2024-04-05 09:00:00', new \DateTimeZone('Europe/London'));
		$end = new \DateTime('2024-04-05 10:00:00', new \DateTimeZone('Europe/London'));
		$details = 'Topics: Performance & Security';
		$location = 'Room #42 - Building A';

		$url = GoogleCalendar::getAddEventUrl($title, $start, $end, $details, $location);

		$this->assertIsString($url);
		// Check that special characters are URL encoded (RFC3986 format)
		$this->assertStringContainsString('text=Meeting%3A%20Q%26A%20Session%20%28Important%21%29', $url);
		$this->assertStringContainsString('details=Topics%3A%20Performance%20%26%20Security', $url);
		$this->assertStringContainsString('location=Room%20%2342%20-%20Building%20A', $url);
	}

	public function testGetAddEventUrlWithUnicodeCharacters(): void
	{
		$title = 'Réunion café ☕';
		$start = new \DateTime('2024-05-12 15:00:00', new \DateTimeZone('Europe/Paris'));
		$end = new \DateTime('2024-05-12 16:00:00', new \DateTimeZone('Europe/Paris'));
		$details = 'Discuter des projets été';
		$location = 'Café français';

		$url = GoogleCalendar::getAddEventUrl($title, $start, $end, $details, $location);

		$this->assertIsString($url);
		$this->assertStringStartsWith('https://calendar.google.com/calendar/r/eventedit', $url);
		// Unicode characters should be properly encoded
		$this->assertStringContainsString('R%C3%A9union', $url);
		$this->assertStringContainsString('caf%C3%A9', $url);
		$this->assertStringContainsString('%E2%98%95', $url);
	}

	public function testGetAddEventUrlWithEmptyTitle(): void
	{
		$title = '';
		$start = new \DateTime('2024-08-20 12:00:00', new \DateTimeZone('UTC'));
		$end = new \DateTime('2024-08-20 13:00:00', new \DateTimeZone('UTC'));

		$url = GoogleCalendar::getAddEventUrl($title, $start, $end);

		$this->assertIsString($url);
		$this->assertStringContainsString('text=', $url);
		$this->assertStringStartsWith('https://calendar.google.com/calendar/r/eventedit', $url);
	}

	public function testGetAddEventUrlWithDifferentTimezones(): void
	{
		$timezones = [
			'Europe/Paris',
			'America/New_York',
			'Asia/Tokyo',
			'Australia/Sydney',
			'Pacific/Auckland',
			'America/Los_Angeles',
		];

		foreach ($timezones as $timezoneName) {
			$start = new \DateTime('2024-09-15 10:00:00', new \DateTimeZone($timezoneName));
			$end = new \DateTime('2024-09-15 11:00:00', new \DateTimeZone($timezoneName));

			$url = GoogleCalendar::getAddEventUrl('Test Event', $start, $end);

			$this->assertIsString($url);
			$this->assertStringContainsString('ctz=' . urlencode($timezoneName), $url);
		}
	}

	public function testGetAddEventUrlDateFormat(): void
	{
		$start = new \DateTime('2024-12-25 23:30:45', new \DateTimeZone('UTC'));
		$end = new \DateTime('2024-12-26 01:15:30', new \DateTimeZone('UTC'));

		$url = GoogleCalendar::getAddEventUrl('Test', $start, $end);

		// Check ISO 8601 format: YYYYMMDDTHHmmssZ (with %2F encoding for slash)
		$this->assertStringContainsString('dates=20241225T233045Z%2F20241226T011530Z', $url);
	}

	public function testGetAddEventUrlWithMultiDayEvent(): void
	{
		$start = new \DateTime('2024-07-01 09:00:00', new \DateTimeZone('Europe/Paris'));
		$end = new \DateTime('2024-07-05 18:00:00', new \DateTimeZone('Europe/Paris'));

		$url = GoogleCalendar::getAddEventUrl('Conference', $start, $end);

		$this->assertIsString($url);
		$this->assertStringContainsString('dates=20240701T090000Z%2F20240705T180000Z', $url);
	}

	public function testGetAddEventUrlWithAllDayEvent(): void
	{
		// All-day events typically use midnight to midnight
		$start = new \DateTime('2024-11-15 00:00:00', new \DateTimeZone('UTC'));
		$end = new \DateTime('2024-11-16 00:00:00', new \DateTimeZone('UTC'));

		$url = GoogleCalendar::getAddEventUrl('Holiday', $start, $end);

		$this->assertIsString($url);
		$this->assertStringContainsString('dates=20241115T000000Z%2F20241116T000000Z', $url);
	}

	public function testGetAddEventUrlStructure(): void
	{
		$start = new \DateTime('2024-03-01 10:00:00', new \DateTimeZone('UTC'));
		$end = new \DateTime('2024-03-01 11:00:00', new \DateTimeZone('UTC'));

		$url = GoogleCalendar::getAddEventUrl('Test', $start, $end);

		// Verify URL structure and query parameters
		$this->assertMatchesRegularExpression(
			'/^https:\/\/calendar\.google\.com\/calendar\/r\/eventedit\?text=.+&dates=.+&ctz=.+&details=.*&location=.*$/',
			$url
		);
	}

	public function testGetAddEventUrlWithLongTexts(): void
	{
		$title = str_repeat('A very long event title ', 20);
		$details = str_repeat('This is a very long description with many details. ', 50);
		$location = str_repeat('123 Long Street Name, ', 10);

		$start = new \DateTime('2024-10-10 14:00:00', new \DateTimeZone('UTC'));
		$end = new \DateTime('2024-10-10 15:00:00', new \DateTimeZone('UTC'));

		$url = GoogleCalendar::getAddEventUrl($title, $start, $end, $details, $location);

		$this->assertIsString($url);
		$this->assertStringStartsWith('https://calendar.google.com/calendar/r/eventedit', $url);
		// URL should be properly encoded even with long texts
		$this->assertStringContainsString('text=', $url);
		$this->assertStringContainsString('details=', $url);
		$this->assertStringContainsString('location=', $url);
	}

	public function testGetAddEventUrlWithLineBreaksInDetails(): void
	{
		$title = 'Event with multiline description';
		$details = "First line\nSecond line\r\nThird line";
		$start = new \DateTime('2024-02-14 10:00:00', new \DateTimeZone('UTC'));
		$end = new \DateTime('2024-02-14 11:00:00', new \DateTimeZone('UTC'));

		$url = GoogleCalendar::getAddEventUrl($title, $start, $end, $details);

		$this->assertIsString($url);
		// Line breaks should be URL encoded
		$this->assertStringContainsString('details=First%20line%0ASecond%20line%0D%0AThird%20line', $url);
	}

	public function testGetAddEventUrlReturnsValidUrl(): void
	{
		$start = new \DateTime('2024-06-01 12:00:00', new \DateTimeZone('Europe/Berlin'));
		$end = new \DateTime('2024-06-01 13:00:00', new \DateTimeZone('Europe/Berlin'));

		$url = GoogleCalendar::getAddEventUrl('Test Event', $start, $end, 'Details', 'Location');

		// URL should be a valid URL format
		$this->assertTrue(filter_var($url, FILTER_VALIDATE_URL) !== false);
	}

	public function testGetAddEventUrlWithSameStartAndEndTime(): void
	{
		$dateTime = new \DateTime('2024-04-15 15:00:00', new \DateTimeZone('UTC'));

		$url = GoogleCalendar::getAddEventUrl('Instant Event', $dateTime, $dateTime);

		$this->assertIsString($url);
		$this->assertStringContainsString('dates=20240415T150000Z%2F20240415T150000Z', $url);
	}

	public function testGetAddEventUrlWithVeryShortEvent(): void
	{
		$start = new \DateTime('2024-05-20 09:00:00', new \DateTimeZone('UTC'));
		$end = new \DateTime('2024-05-20 09:01:00', new \DateTimeZone('UTC'));

		$url = GoogleCalendar::getAddEventUrl('Quick Meeting', $start, $end);

		$this->assertIsString($url);
		$this->assertStringContainsString('dates=20240520T090000Z%2F20240520T090100Z', $url);
	}

	public function testGetAddEventUrlConsistency(): void
	{
		$start = new \DateTime('2024-08-10 14:30:00', new \DateTimeZone('America/Chicago'));
		$end = new \DateTime('2024-08-10 16:00:00', new \DateTimeZone('America/Chicago'));

		// Generate URL multiple times with same parameters
		$url1 = GoogleCalendar::getAddEventUrl('Consistency Test', $start, $end, 'Details', 'Location');
		$url2 = GoogleCalendar::getAddEventUrl('Consistency Test', $start, $end, 'Details', 'Location');
		$url3 = GoogleCalendar::getAddEventUrl('Consistency Test', $start, $end, 'Details', 'Location');

		// All URLs should be identical
		$this->assertSame($url1, $url2);
		$this->assertSame($url2, $url3);
	}

	// ========== getAddAllDayEventUrl() ==========

	public function testGetAddAllDayEventUrl(): void
	{
		$title = 'Company Holiday';
		$start = new \DateTime('2024-12-25 00:00:00', new \DateTimeZone('Europe/Paris'));
		$end = new \DateTime('2024-12-26 00:00:00', new \DateTimeZone('Europe/Paris'));

		$url = GoogleCalendar::getAddAllDayEventUrl($title, $start, $end);

		$this->assertIsString($url);
		$this->assertStringStartsWith('https://calendar.google.com/calendar/r/eventedit', $url);
		$this->assertStringContainsString('text=Company%20Holiday', $url);
		// All-day events use YYYYMMDD format without time
		$this->assertStringContainsString('dates=20241225%2F20241226', $url);
		// Should not have timezone parameter for all-day events
		$this->assertStringNotContainsString('ctz=', $url);
	}

	public function testGetAddAllDayEventUrlMultipleDays(): void
	{
		$title = 'Conference';
		$start = new \DateTime('2024-06-10');
		$end = new \DateTime('2024-06-13'); // 3-day conference (10, 11, 12)

		$url = GoogleCalendar::getAddAllDayEventUrl($title, $start, $end, 'Annual tech conference', 'Convention Center');

		$this->assertIsString($url);
		$this->assertStringContainsString('dates=20240610%2F20240613', $url);
		$this->assertStringContainsString('details=Annual%20tech%20conference', $url);
		$this->assertStringContainsString('location=Convention%20Center', $url);
	}

	// ========== getCalendarViewUrl() ==========

	public function testGetCalendarViewUrl(): void
	{
		$date = new \DateTime('2024-07-15');

		$url = GoogleCalendar::getCalendarViewUrl($date);

		$this->assertIsString($url);
		$this->assertStringStartsWith('https://calendar.google.com/calendar/r', $url);
		$this->assertStringContainsString('mode=MONTH', $url);
		$this->assertStringContainsString('dates=20240715%2F20240715', $url);
	}

	public function testGetCalendarViewUrlWithDifferentViews(): void
	{
		$date = new \DateTime('2024-03-20');
		$views = ['day', 'week', 'month', 'year', 'agenda'];

		foreach ($views as $view) {
			$url = GoogleCalendar::getCalendarViewUrl($date, $view);

			$this->assertIsString($url);
			$this->assertStringContainsString('mode=' . strtoupper($view), $url);
		}
	}

	// ========== getEmbedCalendarUrl() ==========

	public function testGetEmbedCalendarUrl(): void
	{
		$calendarId = 'test@example.com';

		$url = GoogleCalendar::getEmbedCalendarUrl($calendarId);

		$this->assertIsString($url);
		$this->assertStringStartsWith('https://calendar.google.com/calendar/embed', $url);
		$this->assertStringContainsString('src=test%40example.com', $url);
		$this->assertStringContainsString('showTitle=1', $url);
		$this->assertStringContainsString('mode=MONTH', $url);
	}

	public function testGetEmbedCalendarUrlWithOptions(): void
	{
		$calendarId = 'team@company.com';
		$options = [
			'mode' => 'WEEK',
			'height' => 800,
			'showTitle' => 0,
			'showPrint' => 1,
		];

		$url = GoogleCalendar::getEmbedCalendarUrl($calendarId, $options);

		$this->assertIsString($url);
		$this->assertStringContainsString('mode=WEEK', $url);
		$this->assertStringContainsString('height=800', $url);
		$this->assertStringContainsString('showTitle=0', $url);
		$this->assertStringContainsString('showPrint=1', $url);
		$this->assertStringContainsString('src=team%40company.com', $url);
	}

	// ========== getSubscribeCalendarUrl() ==========

	public function testGetSubscribeCalendarUrl(): void
	{
		$calendarId = 'public@group.calendar.google.com';

		$url = GoogleCalendar::getSubscribeCalendarUrl($calendarId);

		$this->assertIsString($url);
		$this->assertStringStartsWith('https://calendar.google.com/calendar/r', $url);
		$this->assertStringContainsString('cid=public%40group.calendar.google.com', $url);
	}

	// ========== isValidCalendarId() ==========

	public function testIsValidCalendarIdWithEmail(): void
	{
		$this->assertTrue(GoogleCalendar::isValidCalendarId('user@gmail.com'));
		$this->assertTrue(GoogleCalendar::isValidCalendarId('team@company.com'));
		$this->assertTrue(GoogleCalendar::isValidCalendarId('test.user@example.org'));
	}

	public function testIsValidCalendarIdWithGroupCalendar(): void
	{
		$this->assertTrue(GoogleCalendar::isValidCalendarId('holiday@group.calendar.google.com'));
		$this->assertTrue(GoogleCalendar::isValidCalendarId('team.calendar@group.calendar.google.com'));
	}

	public function testIsValidCalendarIdWithInvalidFormats(): void
	{
		$this->assertFalse(GoogleCalendar::isValidCalendarId('not-an-email'));
		$this->assertFalse(GoogleCalendar::isValidCalendarId(''));
		$this->assertFalse(GoogleCalendar::isValidCalendarId('invalid@'));
		$this->assertFalse(GoogleCalendar::isValidCalendarId('@invalid.com'));
	}

	// ========== extractCalendarIdFromUrl() ==========

	public function testExtractCalendarIdFromUrlWithSrcParameter(): void
	{
		$url = 'https://calendar.google.com/calendar/embed?src=test%40example.com';

		$calendarId = GoogleCalendar::extractCalendarIdFromUrl($url);

		$this->assertEquals('test@example.com', $calendarId);
	}

	public function testExtractCalendarIdFromUrlWithCidParameter(): void
	{
		$url = 'https://calendar.google.com/calendar/r?cid=team%40group.calendar.google.com';

		$calendarId = GoogleCalendar::extractCalendarIdFromUrl($url);

		$this->assertEquals('team@group.calendar.google.com', $calendarId);
	}

	public function testExtractCalendarIdFromUrlWithInvalidUrl(): void
	{
		$url = 'https://calendar.google.com/calendar/';

		$calendarId = GoogleCalendar::extractCalendarIdFromUrl($url);

		$this->assertNull($calendarId);
	}

	public function testExtractCalendarIdFromUrlWithNoQueryString(): void
	{
		$url = 'https://calendar.google.com/calendar/embed';

		$calendarId = GoogleCalendar::extractCalendarIdFromUrl($url);

		$this->assertNull($calendarId);
	}
}