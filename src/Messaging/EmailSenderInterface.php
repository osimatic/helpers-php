<?php

namespace Osimatic\Messaging;

interface EmailSenderInterface
{
	/**
	 * @param Email $email
	 * @return void
	 * @throw \Exception
	 */
	public function send(Email $email): void;
}