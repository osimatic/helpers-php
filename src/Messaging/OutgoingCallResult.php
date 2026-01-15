<?php

namespace Osimatic\Messaging;

/**
 * Represents the possible results of an outgoing phone call.
 * This enum defines the different statuses that can occur when attempting to make an outgoing phone call through a telephony service.
 */
enum OutgoingCallResult: string
{
	/**
	 * The call was successfully completed.
	 */
	case OK = 'OK';

	/**
	 * The called party's line was busy.
	 */
	case BUSY = 'BUSY';

	/**
	 * The called party did not answer.
	 */
	case NO_RESPONSE = 'NO_RESPONSE';

	/**
	 * The call failed for technical or other reasons.
	 */
	case FAILED = 'FAILED';
}