<?php

namespace Osimatic\Security;

use Symfony\Component\Validator\Constraints\PasswordStrength;

/**
 * Custom password strength estimator that evaluates password security based on various criteria including length, character types, and common weak passwords detection.
 */
class CustomPasswordStrengthEstimator
{
	private const array WEAK_PASSWORDS = ['password', 'azerty', '123456', 'qwerty', 'admin', 'test', 'welcome'];
	private const int MIN_LENGTH = 8;
	private const int LONG_PASSWORD_LENGTH = 12;
	private const int LONG_PASSWORD_MIN_SCORE = 4;

	private const int SCORE_VERY_STRONG = 6;
	private const int SCORE_STRONG = 5;
	private const int SCORE_MEDIUM = 4;
	private const int SCORE_WEAK = 2;

	/**
	 * Estimates the strength of a given password based on multiple criteria including length, character diversity, and blacklist checking. The password is evaluated against forbidden weak passwords and receives points for various security factors such as length (8+ characters), uppercase letters, lowercase letters, numbers, special characters, and a bonus for long passwords (12+ characters with good diversity).
	 * @param string $password The password to evaluate for strength
	 * @return PasswordStrength::STRENGTH_* The strength level (VERY_WEAK, WEAK, MEDIUM, STRONG, or VERY_STRONG)
	 */
	public function __invoke(string $password): int
	{
		if (empty($password)) {
			return PasswordStrength::STRENGTH_VERY_WEAK;
		}

		if ($this->isWeakPassword($password)) {
			return PasswordStrength::STRENGTH_VERY_WEAK;
		}

		$score = $this->calculateScore($password);

		return $this->mapScoreToStrength($score);
	}

	/**
	 * Checks if the password is in the list of forbidden weak passwords.
	 * @param string $password The password to check
	 * @return bool Returns true if the password is weak, false otherwise
	 */
	private function isWeakPassword(string $password): bool
	{
		return in_array(strtolower($password), self::WEAK_PASSWORDS, true);
	}

	/**
	 * Calculates the password score based on various security criteria.
	 * @param string $password The password to evaluate
	 * @return int The calculated score (0-6)
	 */
	private function calculateScore(string $password): int
	{
		$score = 0;
		$length = strlen($password);

		if ($length >= self::MIN_LENGTH) {
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
		if ($length >= self::LONG_PASSWORD_LENGTH && $score >= self::LONG_PASSWORD_MIN_SCORE) {
			$score++;
		}

		return $score;
	}

	/**
	 * Maps the calculated score to a password strength level.
	 * @param int $score The password score
	 * @return PasswordStrength::STRENGTH_* The strength level
	 */
	private function mapScoreToStrength(int $score): int
	{
		return match (true) {
			$score >= self::SCORE_VERY_STRONG => PasswordStrength::STRENGTH_VERY_STRONG,
			$score >= self::SCORE_STRONG => PasswordStrength::STRENGTH_STRONG,
			$score >= self::SCORE_MEDIUM => PasswordStrength::STRENGTH_MEDIUM,
			$score >= self::SCORE_WEAK => PasswordStrength::STRENGTH_WEAK,
			default => PasswordStrength::STRENGTH_VERY_WEAK,
		};
	}

}