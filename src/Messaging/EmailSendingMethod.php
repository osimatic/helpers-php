<?php

namespace Osimatic\Helpers\Messaging;

enum EmailSendingMethod: string
{
	case PHP_MAIL = 'mail';
	case SMTP = 'smtp';
	case SENDMAIL = 'sendmail';
	case QMAIL = 'qmail';
}