<?php

namespace Osimatic\Bank;

enum PaymentStatus: string
{
	case ONGOING = 'ONGOING';
	case WAITING = 'WAITING';
	case PAID = 'PAID';

	public static function parse(?string $paymentStatus): ?self
	{
		if (null === $paymentStatus) {
			return null;
		}

		$paymentStatus = mb_strtoupper($paymentStatus);
		if ('CURRENT' === $paymentStatus || 'EN_COURS' === $paymentStatus) {
			return self::ONGOING;
		}
		if ('PAYEE' === $paymentStatus) {
			return self::PAID;
		}
		if ('VALIDATED' === $paymentStatus || 'VALIDEE' === $paymentStatus || 'WAITING' === $paymentStatus) {
			return self::WAITING;
		}

		return self::tryFrom($paymentStatus);
	}
}