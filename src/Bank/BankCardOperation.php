<?php

namespace Osimatic\Bank;

/**
 * Enumeration of bank card operations.
 * Represents the various operations that can be performed on bank card transactions.
 */
enum BankCardOperation: string
{
	/** Authorize the transaction without capturing funds */
	case AUTHORIZATION_ONLY = 'AUTHORIZATION_ONLY';

	/** Capture funds from an authorized transaction */
	case DEBIT = 'DEBIT';

	/** Authorize and immediately capture funds */
	case AUTHORIZATION_AND_DEBIT = 'AUTHORIZATION_AND_DEBIT';

	/** Credit funds to the card (reverse transaction) */
	case CREDIT = 'CREDIT';

	/** Cancel a pending transaction */
	case CANCEL = 'CANCEL';

	/** Refund a completed transaction */
	case REFUND = 'REFUND';

	/** Register a new subscriber for recurring payments */
	case REGISTER_SUBSCRIBER = 'REGISTER_SUBSCRIBER';

	/** Update an existing subscriber's information */
	case UPDATE_SUBSCRIBER = 'UPDATE_SUBSCRIBER';

	/** Remove a subscriber from recurring payments */
	case DELETE_SUBSCRIBER = 'DELETE_SUBSCRIBER';
}