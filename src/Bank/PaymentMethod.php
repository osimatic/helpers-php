<?php

namespace Osimatic\Bank;

enum PaymentMethod: string
{
	case CREDIT_CARD 	= 'CREDIT_CARD';
	case TRANSFER 		= 'TRANSFER';
	case CHEQUE 		= 'CHEQUE';
	case PAYPAL 		= 'PAYPAL';
	case DIRECT_DEBIT 	= 'DIRECT_DEBIT'; // Prélèvement SEPA


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