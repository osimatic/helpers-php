<?php

namespace Osimatic\Person;

use Symfony\Component\Validator\Constraints\PasswordStrength;

class CustomPasswordStrengthEstimator
{
	/**
	 * @return PasswordStrength::STRENGTH_*
	 */
	public function __invoke(string $password): int
	{
		if (empty($password)) {
			return PasswordStrength::STRENGTH_VERY_WEAK;
		}

		$score = 0;

		// Mots de passe interdits
		$weakPasswords = ['password', 'azerty', '123456', 'qwerty', 'admin', 'test', 'welcome'];
		if (in_array(strtolower($password), $weakPasswords, true)) {
			return PasswordStrength::STRENGTH_VERY_WEAK;
		}

		// Règles de base
		if (strlen($password) >= 8) {
			$score++;
		}
		if (preg_match('/[A-Z]/', $password)) {
			$score++;
		}
		if (preg_match('/[a-z]/', $password)) {
			$score++;
		}
		if (preg_match('/[0-9]/', $password)) {
			$score++;
		}
		if (preg_match('/[^A-Za-z0-9]/', $password)) {
			$score++;
		}
		if (strlen($password) >= 12 && $score >= 4) {
			$score++;
		} // bonus long mot de passe

		if ($score >= 6) {
			return PasswordStrength::STRENGTH_VERY_STRONG;
		}
		if ($score >= 5) {
			return PasswordStrength::STRENGTH_STRONG;
		}
		if ($score >= 4) {
			return PasswordStrength::STRENGTH_MEDIUM;
		}
		if ($score >= 2) {
			return PasswordStrength::STRENGTH_WEAK;
		}
		return PasswordStrength::STRENGTH_VERY_WEAK;
	}

}