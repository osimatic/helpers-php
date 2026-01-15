<?php

namespace Osimatic\Messaging;

/**
 * Defines the available methods for sending emails.
 * This enum represents the different transport methods that can be used to send email messages through various mail systems.
 */
enum EmailSendingMethod: string
{
	/**
	 * Use PHP's built-in mail() function for sending emails.
	 */
	case PHP_MAIL = 'mail';

	/**
	 * Use SMTP (Simple Mail Transfer Protocol) for sending emails.
	 */
	case SMTP = 'smtp';

	/**
	 * Use the sendmail binary for sending emails.
	 */
	case SENDMAIL = 'sendmail';

	/**
	 * Use the qmail mail transfer agent for sending emails.
	 */
	case QMAIL = 'qmail';
}