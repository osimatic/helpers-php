<?php

namespace Osimatic\Messaging;

enum EmailAddressKind: string
{
	case to = 'to';
	case cc = 'cc';
	case bcc = 'bcc';
	case replyTo = 'replyTo';
}