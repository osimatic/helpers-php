<?php

namespace Osimatic\Helpers\Messaging;

interface EmailSenderInterface
{
	/**
	 * @param Email $email
	 * @return void
	 * @throw \Exception
	 */
	public function send(Email $email): void;
}