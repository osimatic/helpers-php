<?php

namespace Osimatic\Bank;

/**
 * Enumeration of PayBox API versions.
 * Represents the different PayBox payment gateway API versions available.
 */
enum PayBoxVersion: string
{
	/** PayBox Direct API version 00103 */
	case PAYBOX_DIRECT = '00103';

	/** PayBox Direct Plus API version 00104 (enhanced version) */
	case PAYBOX_DIRECT_PLUS = '00104';
}