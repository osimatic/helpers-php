<?php

namespace Osimatic\Bank;

/**
 * Enumeration of bank card types and payment providers.
 * Represents the various credit/debit card brands and digital payment providers supported.
 */
enum BankCardType: string
{
	/** American Express credit card */
	case AMERICAN_EXPRESS = 'AMERICAN_EXPRESS';

	/** Visa credit/debit card */
	case VISA = 'VISA';

	/** Diners Club credit card */
	case DINNER_CLUB = 'DINNER_CLUB';

	/** Discover Network credit card */
	case DISCOVER_NETWORK = 'DISCOVER_NETWORK';

	/** MasterCard credit/debit card */
	case MASTER_CARD = 'MASTER_CARD';

	/** Google Wallet digital payment provider */
	case GOOGLE_WALLET = 'GOOGLE_WALLET';

	/** Skrill digital payment provider */
	case SKRILL = 'SKRILL';
}