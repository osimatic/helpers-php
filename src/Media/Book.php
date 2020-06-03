<?php

namespace Osimatic\Helpers\Media;

/**
 * Class Book
 * @package Osimatic\Helpers\Media
 */
class Book
{

	/**
	 * Vérifie la validité d'un numéro ISBN (pour les livres)
	 * @param string $isbn
	 * @return bool
	 * @link https://en.wikipedia.org/wiki/International_Standard_Book_Number
	 */
	public static function checkIsbn(string $isbn): bool
	{
		return self::_checkIsbn($isbn);
	}

	/**
	 * Vérifie la validité d'un numéro ISBN (pour les livres)
	 * @param string $isbn
	 * @return bool
	 */
	public static function checkIsbn10(string $isbn): bool
	{
		return self::_checkIsbn($isbn, 'isbn10');
	}

	/**
	 * Vérifie la validité d'un numéro ISBN (pour les livres)
	 * @param string $isbn
	 * @return bool
	 */
	public static function checkIsbn13(string $isbn): bool
	{
		return self::_checkIsbn($isbn, 'isbn13');
	}

	/**
	 * @param string $isbn
	 * @param string|null $type
	 * @return bool
	 */
	private static function _checkIsbn(string $isbn, ?string $type=null): bool
	{
		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		$constraint = new \Symfony\Component\Validator\Constraints\Isbn();
		$constraint->type = $type;
		return $validator->validate($isbn, $constraint)->count() === 0;
	}

	/**
	 * Vérifie la validité d'un numéro ISSN (pour les revues/magazines)
	 * @param string $issn
	 * @return bool
	 * @link https://en.wikipedia.org/wiki/International_Standard_Serial_Number
	 */
	public static function checkIssn(string $issn): bool
	{
		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		return $validator->validate($issn, new \Symfony\Component\Validator\Constraints\Issn())->count() === 0;
	}

}