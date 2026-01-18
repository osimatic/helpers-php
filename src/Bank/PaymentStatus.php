<?php

namespace Osimatic\Bank;

/**
 * Enumeration of payment statuses.
 * Represents the various states a payment can be in during its lifecycle.
 */
enum PaymentStatus: string
{
	/** Payment is currently being processed */
	case ONGOING = 'CURRENT';

	/** Payment is validated and waiting for completion */
	case WAITING = 'VALIDATED';

	/** Payment has been completed */
	case PAID = 'PAID';

	/**
	 * Parses a payment status string into the corresponding enum value.
	 * Handles multiple formats including French and English variations.
	 * Case-insensitive parsing.
	 * @param string|null $paymentStatus The status string to parse (e.g., 'CURRENT', 'EN_COURS', 'PAID')
	 * @return self|null The corresponding PaymentStatus enum, or null if input is null or invalid
	 */
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