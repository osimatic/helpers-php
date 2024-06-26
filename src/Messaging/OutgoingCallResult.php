<?php

namespace Osimatic\Messaging;

enum OutgoingCallResult: string
{
	case OK = 'OK';
	case BUSY = 'BUSY';
	case NO_RESPONSE = 'NO_RESPONSE';
	case FAILED = 'FAILED';
}