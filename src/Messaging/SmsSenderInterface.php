<?php

namespace Osimatic\Helpers\Messaging;

interface SmsSenderInterface
{
	/**
	 * @param SMS $sms
	 * @return void
	 * @throw \Exception
	 */
	public function send(SMS $sms): void;
}