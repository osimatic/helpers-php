<?php

namespace Osimatic\Bank;

/**
 * Enumeration of payment methods.
 * Represents the various payment methods accepted for transactions.
 */
enum PaymentMethod: string
{
	/** Credit or debit card payment */
	case CREDIT_CARD 	= 'CREDIT_CARD';

	/** Bank transfer payment */
	case TRANSFER 		= 'TRANSFER';

	/** Check payment */
	case CHEQUE 		= 'CHEQUE';

	/** PayPal payment */
	case PAYPAL 		= 'PAYPAL';

	/** SEPA Direct Debit payment */
	case DIRECT_DEBIT 	= 'DIRECT_DEBIT';

	/**
	 * Gets the localized label for the payment method.
	 * Returns the French label for display purposes.
	 * @return string The localized label
	 */
	public function getLabel(): string
	{
		return match ($this) {
			self::CREDIT_CARD => 'Carte bancaire', // 'payment_method.credit_card'
			self::TRANSFER => 'Virement', // 'payment_method.transfer'
			self::CHEQUE => 'Chèque', // 'payment_method.cheque'
			self::PAYPAL => 'PayPal', // 'payment_method.paypal'
			self::DIRECT_DEBIT => 'Prélèvement', // 'payment_method.direct_debit'
		};
	}

}