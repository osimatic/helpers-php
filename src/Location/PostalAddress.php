<?php

namespace Osimatic\Location;

use Symfony\Component\Yaml\Yaml;

/**
 * Class PostalAddress
 * @package Osimatic\Helpers\Location
 */
class PostalAddress
{
	// ========== Vérification ==========

	/**
	 * @param string|null $value
	 * @return bool
	 */
	public static function checkStreet(?string $value): bool
	{
		//return preg_match('/(([0-9]+ )?[a-zA-Z ]){1,200}$/', $value);
		return preg_match('/^(.){1,200}$/u', $value);
	}

	/**
	 * @param string|null $value
	 * @param string|null $country
	 * @return bool
	 */
	public static function checkPostalCode(?string $value, ?string $country=null): bool
	{
		// Si le pays est fourni, on vérifie le code postal spécifique à ce pays
		if (null !== $country) {
			$regEx = Yaml::parse(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'postal_codes.yaml'));
			if (!empty($regEx[$country])) {
				return preg_match('/^'.$regEx[$country].'$/u', $value);
			}
		}

		return preg_match('/^([\-\.\s\w]){3,15}$/u', $value);
	}

	/**
	 * @param string|null $value
	 * @return bool
	 */
	public static function checkZipCode(?string $value): bool
	{
		return self::checkPostalCode($value);
	}

	/**
	 * @param string|null $value
	 * @return bool
	 */
	public static function checkCity(?string $value): bool
	{
		// /^([a-zA-Z'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]){2-100}$/
		//return preg_match('/^[a-zA-Z\'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]+$/u', $value);
		return preg_match('/^(.){1,100}$/u', $value);
	}


	// ========== Affichage ==========

	/**
	 * @param PostalAddressInterface $postalAddress
	 * @param bool $withAttention
	 * @param string|null $separator
	 * @return string|null
	 */
	public static function format(PostalAddressInterface $postalAddress, bool $withAttention=true, ?string $separator='<br/>'): ?string
	{
		return (new PostalAddressFormatter())->format($postalAddress, [], $separator, $withAttention);
	}

	/**
	 * @param PostalAddressInterface $postalAddress
	 * @param bool $withAttention
	 * @param string|null $separator
	 * @return string|null
	 */
	public static function formatInline(PostalAddressInterface $postalAddress, bool $withAttention=true, ?string $separator=', '): ?string
	{
		return self::format($postalAddress, $withAttention, $separator);
	}




	// ========== Formatage ==========

	/**
	 * @param string|null $value
	 * @return string|null
	 */
	public static function replaceSpecialChar(?string $value): ?string
	{
		if (null === $value) {
			return null;
		}

		// caractère parfois utilisé pour séparer la rue de la ville (exemple pour une adresse de la Tunisie retourné par Google Maps, coordonnées 36.7691557,10.2432981)
		$value = str_replace('،', ',', $value);

		// caractère parfois utilisé pour l'apostrophe
		$value = str_replace('́', '’', $value);

		// caractère parfois utilisé pour le numéro de rue (exemple pour une adresse en Réunion retourné par Google Maps, coordonnées -21.0506425,55.2241411)
		$value = str_replace('№', 'N°', $value);

		return \Osimatic\Text\Str::replaceAnnoyingChar($value);
	}



	// ========== DEPRECATED ==========

	/**
	 * @deprecated use formatInline instead
	 * @param PostalAddressInterface $postalAddress
	 * @param string|null $separator
	 * @return string|null
	 */
	public static function formatInlineFromTwig(PostalAddressInterface $postalAddress, ?string $separator=', '): ?string
	{
		return self::format($postalAddress, $separator);
	}

	/**
	 * @deprecated use format instead
	 * @param PostalAddressInterface $postalAddress
	 * @param bool $withAttention
	 * @param string|null $separator
	 * @return string|null
	 */
	public static function formatFromTwig(PostalAddressInterface $postalAddress, bool $withAttention=true, ?string $separator='<br/>'): ?string
	{
		return self::format($postalAddress, $withAttention, $separator);
	}

}