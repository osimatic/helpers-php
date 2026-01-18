<?php

namespace Osimatic\Bank;

/**
 * Enumeration of Revolut order statuses.
 * Represents the possible states of a Revolut payment order.
 */
enum RevolutOrderStatus: string
{
	/** Order has been authorized but not yet completed */
	case ORDER_AUTHORISED   = 'ORDER_AUTHORISED';

	/** Order has been successfully completed */
	case ORDER_COMPLETED    = 'ORDER_COMPLETED';
}