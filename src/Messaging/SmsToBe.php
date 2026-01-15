<?php

namespace Osimatic\Messaging;

/**
 * SmsToBe SMS gateway client for sending SMS messages.
 * This class implements the SmsSenderInterface and provides a placeholder for sending SMS messages through the SmsToBe service.
 */
class SmsToBe implements SmsSenderInterface
{
	/**
	 * Send an SMS message through the SmsToBe service.
	 * @param SMS $sms The SMS message to send
	 */
	public function send(SMS $sms): void
	{
		// TODO: Implement send() method.
	}
}