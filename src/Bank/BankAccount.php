<?php

namespace Osimatic\Bank;

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

	/**
	 * @param string $iban
	 * @return bool
	 */
	public static function checkIban(string $iban): bool
	{
		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		return $validator->validate($iban, new \Symfony\Component\Validator\Constraints\Iban())->count() === 0;

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
	 * @param string $bic
	 * @return bool
	 */
	public static function checkBic(string $bic): bool
	{
		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		return $validator->validate($bic, new \Symfony\Component\Validator\Constraints\Bic())->count() === 0;
	}

}