<?php

namespace Osimatic\Bank;

/**
 * Enumeration of bank card transaction origin types.
 * Represents the channel through which a card payment was initiated.
 */
enum BankCardCallOrigin: string
{
	/** Origin not specified */
	case NOT_SPECIFIED = 'NOT_SPECIFIED';

	/** Payment initiated via telephone order */
	case TELEPHONE_ORDER = 'TELEPHONE_ORDER';

	/** Payment initiated via mail order */
	case MAIL_ORDER = 'MAIL_ORDER';

	/** Payment initiated via Minitel terminal */
	case MINITEL = 'MINITEL';

	/** Payment initiated via internet/online */
	case INTERNET_PAYMENT = 'INTERNET_PAYMENT';

	/** Recurring/subscription-based payment */
	case RECURRING_PAYMENT = 'RECURRING_PAYMENT';
}