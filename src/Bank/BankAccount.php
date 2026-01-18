<?php

namespace Osimatic\Bank;

/**
 * Utility class for bank account operations
 * Provides methods for validating and formatting IBAN and BIC codes
 */
class BankAccount
{

	/**
	 * Format an IBAN by adding spaces every 4 characters
	 * Automatically removes existing spaces and converts to uppercase for normalization
	 * @param string $iban The IBAN to format (with or without spaces)
	 * @return string The formatted IBAN with spaces every 4 characters, in uppercase
	 * @example formatIban('FR7630006000011234567890189') returns "FR76 3000 6000 0112 3456 7890 189"
	 * @example formatIban('fr76 3000 6000 0112 3456 7890 189') returns "FR76 3000 6000 0112 3456 7890 189"
	 * @example formatIban('de89370400440532013000') returns "DE89 3704 0044 0532 0130 00"
	 */
	public static function formatIban(string $iban): string
	{
		// Remove spaces and convert to uppercase
		$iban = strtoupper(str_replace(' ', '', trim($iban)));

		// Add a space every 4 characters
		return trim(chunk_split($iban, 4, ' '));
	}

	/**
	 * Validate an IBAN using Symfony validator
	 * Checks if the IBAN format is correct according to ISO 13616 standard
	 * Automatically removes spaces and normalizes the input
	 * @param string $iban The IBAN to validate (with or without spaces)
	 * @return bool True if the IBAN is valid, false otherwise
	 * @example checkIban('FR7630006000011234567890189') returns true
	 * @example checkIban('FR76 3000 6000 0112 3456 7890 189') returns true
	 * @example checkIban('DE89370400440532013000') returns true
	 * @example checkIban('invalid') returns false
	 */
	public static function checkIban(string $iban): bool
	{
		// Remove spaces and trim
		$iban = str_replace(' ', '', trim($iban));

		if (empty($iban)) {
			return false;
		}

		return \Osimatic\Validator\Validator::getInstance()->validate($iban, new \Symfony\Component\Validator\Constraints\Iban())->count() === 0;

		/*
		$iban = mb_strtolower(str_replace(' ', '', $iban));
		$Countries = ['al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24];
		$Chars = ['a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35];

		if (!array_key_exists(substr($iban,0,2), $Countries) || strlen($iban) != $Countries[substr($iban,0,2)]) {
			return false;
		}

		$MovedChar = substr($iban, 4).substr($iban,0,4);
		$MovedCharArray = str_split($MovedChar);

		$NewString = '';
		foreach ($MovedCharArray AS $key => $value) {
			if (!is_numeric($MovedCharArray[$key])) {
				$MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
			}
			$NewString .= $MovedCharArray[$key];
		}

		return (bcmod($NewString, '97') == 1);
		*/
	}

	/**
	 * Validate a BIC/SWIFT code using Symfony validator
	 * Checks if the BIC format is correct according to ISO 9362 standard
	 * Automatically removes spaces and normalizes the input
	 * @param string $bic The BIC/SWIFT code to validate (8 or 11 characters)
	 * @return bool True if the BIC is valid, false otherwise
	 * @example checkBic('BNPAFRPP') returns true (8 characters)
	 * @example checkBic('BNPAFRPPXXX') returns true (11 characters)
	 * @example checkBic('BNPA FRPP XXX') returns true (spaces removed)
	 * @example checkBic('invalid') returns false
	 */
	public static function checkBic(string $bic): bool
	{
		// Remove spaces and trim
		$bic = str_replace(' ', '', trim($bic));

		if (empty($bic)) {
			return false;
		}

		return \Osimatic\Validator\Validator::getInstance()->validate($bic, new \Symfony\Component\Validator\Constraints\Bic())->count() === 0;
	}

}