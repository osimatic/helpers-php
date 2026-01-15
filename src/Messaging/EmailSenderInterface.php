<?php

namespace Osimatic\Messaging;

/**
 * Interface for email sending implementations.
 * This interface must be implemented by any class that provides email sending functionality through various transport methods.
 */
interface EmailSenderInterface
{
	/**
	 * Send an email message.
	 * @param Email $email The email message to send
	 * @return void
	 * @throws \Exception If the email fails to send
	 */
	public function send(Email $email): void;
}