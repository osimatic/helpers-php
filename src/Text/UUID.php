<?php

namespace Osimatic\Text;

/**
 * Class UUID
 * @package Osimatic\Helpers\Text
 */
class UUID
{
	/**
	 * @param string $uuid
	 * @return bool
	 */
	public static function check(string $uuid): bool
	{
		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		return $validator->validate($uuid, new \Symfony\Component\Validator\Constraints\Uuid())->count() === 0;
	}

	/**
	 * @return string
	 */
	public static function generate(): string
	{
		try {
			$data = random_bytes(16);
			$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // Set version to 0100
			$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // Set bits 6-7 to 10
			return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
		}
		catch (\Exception) {}

		/*
		try {
			return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				// 32 bits for "time_low"
				random_int( 0, 0xffff ), random_int( 0, 0xffff ),

				// 16 bits for "time_mid"
				random_int( 0, 0xffff ),

				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				random_int( 0, 0x0fff ) | 0x4000,

				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				random_int( 0, 0x3fff ) | 0x8000,

				// 48 bits for "node"
				random_int( 0, 0xffff ), random_int( 0, 0xffff ), random_int( 0, 0xffff )
			);
		}
		catch (\Exception $e) {
		}
		*/

		return '';
	}

}