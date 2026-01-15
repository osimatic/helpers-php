<?php

namespace Osimatic\Messaging;

/**
 * Represents the different types of email address recipients.
 * This enum defines the various recipient categories that can be used when composing an email message.
 */
enum EmailAddressKind: string
{
	/**
	 * Primary recipient (To).
	 */
	case to = 'to';

	/**
	 * Carbon copy recipient (Cc).
	 */
	case cc = 'cc';

	/**
	 * Blind carbon copy recipient (Bcc).
	 */
	case bcc = 'bcc';

	/**
	 * Reply-to address.
	 */
	case replyTo = 'replyTo';
}