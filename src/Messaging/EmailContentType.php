<?php

namespace Osimatic\Messaging;

/**
 * Defines the MIME content types available for email messages.
 * This enum represents the different content type formats that can be used in email headers to specify the type and structure of the message body.
 */
enum EmailContentType: string
{
	/**
	 * Plain text content type without formatting.
	 */
	case PLAINTEXT = 'text/plain';

	/**
	 * Calendar content type (iCalendar format) for event invitations.
	 */
	case TEXT_CALENDAR = 'text/calendar';

	/**
	 * HTML content type for formatted messages.
	 */
	case TEXT_HTML = 'text/html';

	/**
	 * Multipart alternative content type, typically used for emails with both plain text and HTML versions.
	 */
	case MULTIPART_ALTERNATIVE = 'multipart/alternative';

	/**
	 * Multipart mixed content type, used for emails with attachments.
	 */
	case MULTIPART_MIXED = 'multipart/mixed';

	/**
	 * Multipart related content type, used for emails with embedded resources like images.
	 */
	case MULTIPART_RELATED = 'multipart/related';
}