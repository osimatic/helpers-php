<?php

namespace Osimatic\Helpers;

class BankAccount
{

	/**
	 * @param string $iban
	 * @return string
	 */
	public static function formatIban(string $iban): string
	{
		$str = '';
		for ($i=0; $i<27; $i++) {
			if (in_array($i, [4, 8, 12, 16, 20, 24], true)) {
				$str .= ' ';
			}
			$str .= substr($iban, $i, 1);
		}
		return $str;
	}

}