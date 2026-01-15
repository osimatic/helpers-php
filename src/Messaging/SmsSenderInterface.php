<?php

namespace Osimatic\Messaging;

/**
 * Interface for SMS sending implementations.
 * This interface must be implemented by any class that provides SMS sending functionality through various SMS gateway providers.
 */
interface SmsSenderInterface
{
	/**
	 * Send an SMS message.
	 * @param SMS $sms The SMS message to send
	 * @return void
	 * @throws \Exception If the SMS fails to send
	 */
	public function send(SMS $sms): void;
}