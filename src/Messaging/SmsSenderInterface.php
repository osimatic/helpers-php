<?php

namespace Osimatic\Messaging;

interface SmsSenderInterface
{
	/**
	 * @param SMS $sms
	 * @return void
	 * @throw \Exception
	 */
	public function send(SMS $sms): void;
}