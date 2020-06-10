<?php

namespace Osimatic\Helpers\Text;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class Str
{

	// ========== Remplacement de caractères ==========

	/**
	 * Remplace une série de caractère par un autre caractère dans une chaîne.
	 * @param string $str la chaîne sur laquelle remplacer des caractères
	 * @param array $replacements liste des caractères à remplacer (sensible à la casse) : en clé, le caractère à remplacer ; en valeur, le caractère de remplacement.
	 * @param boolean $replaceUppercaseChar true pour remplacer également les caractères en majuscule, false sinon (false par défaut)
	 * @param boolean $replaceLowercaseChar true pour remplacer également les caractères en minuscule, false sinon (false par défaut)
	 * @param boolean $replaceBrutChar true pour remplacer les caractères tels quels, false sinon (true par défaut)
	 * @return string la chaîne avec les caractères remplacés
	 */
	public static function replaceListChar(string $str, array $replacements, bool $replaceUppercaseChar=false, bool $replaceLowercaseChar=false, bool $replaceBrutChar=true): string
	{
		if ($replaceBrutChar) {
			$str = str_replace(array_keys($replacements), array_values($replacements), $str);
		}
		if ($replaceLowercaseChar) {
			$str = str_replace(array_map('mb_strtolower', array_keys($replacements)), array_map('mb_strtolower', array_values($replacements)), $str);
		}
		if ($replaceUppercaseChar) {
			// Probleme de conversion des caracteres šž du à la fonction replaceListChar (cf. test StringTest::testSupprimerDiacritiques)
			// mb_strtoupper ne met pas en majuscule ces deux caracteres, ce qui entraine leur remplacement par s et z
			$str = str_replace(array_map('mb_strtoupper', array_keys($replacements)), array_map('mb_strtoupper', array_values($replacements)), $str);
			// $str = str_replace(mb_strtoupper($listeRemplacementChar), mb_strtoupper($lettreRemplacement), $str);
		}
		return $str;
	}

	/**
	 * Supprime une série de caractère dans une chaîne.
	 * @param string $str la chaîne sur laquelle supprimer des caractères
	 * @param array $charactersToRemove liste des caractères à supprimer (sensible à la casse).
	 * @param boolean $replaceUppercaseChar true pour supprimer également les caractères en majuscule, false sinon (false par défaut)
	 * @param boolean $replaceLowercaseChar true pour supprimer également les caractères en minuscule, false sinon (false par défaut)
	 * @param boolean $replaceBrutChar true pour supprimer les caractères tels quels, false sinon (true par défaut)
	 * @return string la chaîne avec les caractères supprimés
	 */
	public static function removeListeChar(string $str, array $charactersToRemove, bool $replaceUppercaseChar=false, bool $replaceLowercaseChar=false, bool $replaceBrutChar=true): string
	{
		if ($replaceBrutChar) {
			$str = str_replace($charactersToRemove, '', $str);
		}
		if ($replaceLowercaseChar) {
			$str = str_replace(array_map('mb_strtolower', array_values($charactersToRemove)), '', $str);
		}
		if ($replaceUppercaseChar) {
			$str = str_replace(array_map('mb_strtoupper', array_values($charactersToRemove)), '', $str);
		}
		return $str;
	}


	// ========== Comparaison de chaînes ==========

	/**
	 * Calcule la distance Levenshtein entre deux chaînes.
	 * La distance de Levenshtein mesure la similarité entre deux chaînes de caractères. Elle est égale au nombre minimal de caractères qu’il faut supprimer, insérer ou remplacer pour passer d’une chaîne à l’autre.
	 * @param string $str1 Une des chaînes à évaluer.
	 * @param string $str2 L'autre des chaînes à évaluer.
	 * @return int la distance Levenshtein entre deux chaînes de caractères, ou -1 si l'un des deux arguments contient plus de 255 caractères.
	 * @link http://fr.wikipedia.org/wiki/Distance_de_Levenshtein
	 * @link http://fr.php.net/manual/fr/function.levenshtein.php
	 */
	public static function levenshtein(string $str1, string $str2): int
	{
		return levenshtein($str1, $str2);
	}

	/**
	 * Suggère un mot similaire tiré d'un dictionnaire à partir d'un mot de base passé en paramètre.
	 * @param string $word le mot de base à comparer au dictionnaire.
	 * @param array $dictionnary la liste des mots "autorisés" qui peuvent être retourné si l'un de ces mots est similaire au mot de base.
	 * @param int $distanceMax distance de Levenshtein maximum désirée. Plus la distance maximum est importante, plus les mots suggérés seront éloignés, donc différents.
	 * @return string|null le mot le plus proche du dictionnaire par rapport au mot de base (c'est-à-dire le mot le moins "distant" dans le dictionnaire), false si aucun mot du dictionnaire ne correspond au critère de distance.
	 * @author : Jay Salvat (blog.jaysalvat.com)
	 * @link http://blog.jaysalvat.com/article/suggerez-des-orthographes-alternatives-en-php
	 */
	public static function suggest(string $word, array $dictionnary, int $distanceMax = 3): ?string
	{
		$scores = array();
		foreach ($dictionnary as $index) {
			$distanceLevenshtein = levenshtein($word, $index);
			if ($distanceLevenshtein >= 0) {
				$scores[$index] = $distanceLevenshtein;
			}
		}
		$min = min($scores);
		if ($min <= $distanceMax) {
			return array_search($min, $scores, true);
		}
		return null;
	}

	/**
	 * @param $val1
	 * @param $val2
	 * @param bool $naturalOrder
	 * @param bool $caseSensitive
	 * @return int
	 */
	public static function compare($val1, $val2, bool $naturalOrder=false, bool $caseSensitive=false): int
	{
		if (is_numeric($val1) && is_numeric($val2)) {
			if ($val1 < $val2) {
				return -1;
			}
			if ($val1 > $val2) {
				return 1;
			}
			return 0;
		}

		if ($naturalOrder) {
			if ($caseSensitive) {
				return strnatcmp($val1, $val2); // Comparaison ordre naturel, sensible à la casse
			}
			return strnatcasecmp($val1, $val2); // Comparaison ordre naturel, insensible à la casse
		}

		if ($caseSensitive) {
			return strcmp($val1, $val2); // Comparaison, sensible à la casse
		}
		return strcasecmp($val1, $val2); // Comparaison, insensible à la casse
	}

	// ========== Raccourcissement de chaîne ==========

	/**
	 * Coupe une chaîne de caractères avant un certains nombre de caractères, le début de la chaîne étant tronquée, et ajoute éventuellement une chaîne avant la coupure
	 * @param string $string la chaîne à couper
	 * @param int $nbCharMax le nombre de caractères maximum de la chaîne (le début étant coupé)
	 * @param bool $dontCutInMiddleOfWord true pour ne pas couper la chaîne en plein mot (attendre la fin d'un mot avant de couper), false pour couper strictement au nombre de caracètres maximum (true par défaut)
	 * @param string $strAddingAtBeginning la chaîne de caractères à ajouter après avoir coupé la chaîne, si coupure il y a eu ("..." par défaut)
	 * @return string la chaîne coupée
	 */
	public static function truncateTextAtBeginning(string $string, int $nbCharMax, bool $dontCutInMiddleOfWord = true, string $strAddingAtBeginning = '...'): string
	{
		$space = ' ';
		$stringTruncate = $string;

		if (strlen($string) > $nbCharMax) {
			$stringTruncate = substr($string, -$nbCharMax);

			if ($dontCutInMiddleOfWord && $string[$nbCharMax-1] !== $space) {
				$posSpace = strpos($stringTruncate, $space);
				if ($posSpace !== false) {
					$stringTruncate = substr($stringTruncate, $posSpace);
				}
			}

			$stringTruncate = $strAddingAtBeginning.$stringTruncate;
		}

		return $stringTruncate;
	}

	/**
	 * Coupe une chaîne de caractères après un certains nombre de caractères, la fin de la chaîne étant tronquée, et ajoute éventuellement une chaîne après la coupure
	 * @param string $string la chaîne à couper
	 * @param int $nbCharMax le nombre de caractères maximum de la chaîne (la fin étant coupé)
	 * @param bool $dontCutInMiddleOfWord true pour ne pas couper la chaîne en plein mot (attendre la fin d'un mot avant de couper), false pour couper strictement au nombre de caracètres maximum (true par défaut)
	 * @param string $strAddingAtEnd la chaîne de caractères à ajouter après avoir coupé la chaîne, si coupure il y a eu ("..." par défaut)
	 * @return string la chaîne coupée
	 */
	public static function truncateTextAtEnd(string $string, int $nbCharMax, bool $dontCutInMiddleOfWord = true, string $strAddingAtEnd = '...'): string
	{
		$space = ' ';
		$stringTruncate = $string;

		if (strlen($string) > $nbCharMax) {
			$stringTruncate = substr($string, 0, $nbCharMax);

			if ($dontCutInMiddleOfWord && $string[$nbCharMax-1] != $space) {
				$posSpace = strrpos($stringTruncate, $space);
				if ($posSpace !== false) {
					$stringTruncate = substr($stringTruncate, 0, $posSpace);
				}
			}

			$stringTruncate .= $strAddingAtEnd;
		}
		return $stringTruncate;
	}

	/**
	 * TODO : prendre en compte le paramètre $dontCutInMiddleOfWord
	 * @param string $string
	 * @param int $nbCharMax
	 * @param bool $dontCutInMiddleOfWord
	 * @param string $strAddingInMiddle
	 * @return string
	 */
	public static function truncateTextInMiddle(string $string, int $nbCharMax, bool $dontCutInMiddleOfWord = true, string $strAddingInMiddle = '[...]'): string
	{
		$stringTruncate = $string;

		if (strlen($string) > $nbCharMax) {
			$nbCharEachPart = (int)($nbCharMax / 2);
			$stringTruncate = substr($string, 0, $nbCharEachPart) . $strAddingInMiddle . substr($string, -$nbCharEachPart);
		}

		return $stringTruncate;
	}

	/**
	 * This function will strip tags from a string, split it at its max_length and ellipsize
	 * @param string $str string to ellipsize
	 * @param int $nbCharInFinalString max length of string
	 * @param mixed $whereEllipsisShouldAppear int (1|0) or float, .5, .2, etc for position to split
	 * @param string $ellipsis ellipsis ; Default '...'
	 * @return string ellipsized string
	 */
	public static function ellipsize(string $str, int $nbCharInFinalString, $whereEllipsisShouldAppear=1, string $ellipsis = '&hellip;'): string
	{
		// Strip tags
		$str = trim(strip_tags($str));

		// Is the string long enough to ellipsize?
		if (strlen($str) <= $nbCharInFinalString) {
			return $str;
		}

		$beg = substr($str, 0, floor($nbCharInFinalString * $whereEllipsisShouldAppear));

		$whereEllipsisShouldAppear = ($whereEllipsisShouldAppear > 1) ? 1 : $whereEllipsisShouldAppear;
		if ($whereEllipsisShouldAppear === 1) {
			$end = substr($str, 0, -($nbCharInFinalString - strlen($beg)));
		}
		else {
			$end = substr($str, -($nbCharInFinalString - strlen($beg)));
		}

		return $beg.$ellipsis.$end;
	}


	// ========== Vérification ==========

	/**
	 * @param string $string
	 * @param bool $numericAllowed
	 * @return bool
	 */
	public static function checkLowercase(string $string, bool $numericAllowed=true): bool
	{
		if ($numericAllowed) {
			$string = str_replace('/\d/', '', $string);
		}
		return ctype_lower($string);
	}

	/**
	 * @param string $string
	 * @param bool $numericAllowed
	 * @return bool
	 */
	public static function checkUppercase(string $string, bool $numericAllowed=true): bool
	{
		if ($numericAllowed) {
			$string = str_replace('/\d/', '', $string);
		}
		return ctype_upper($string);
	}

	// ========== Comptage de caractère ==========

	/**
	 * @param string $string
	 * @param int $nbCharFormat
	 * @param bool $addBlankInBeginning
	 * @return string
	 */
	public static function getStringWithBlank(string $string, int $nbCharFormat, bool $addBlankInBeginning=false): string
	{
		$nbBlankAdd = $nbCharFormat - strlen($string);
		$strBlank = self::getStringWithSameChar(' ', $nbBlankAdd);
		if ($addBlankInBeginning) {
			$stringFormat = $strBlank.$string;
		}
		else {
			$stringFormat = $string.$strBlank;
		}
		return $stringFormat;
	}

	/**
	 * @param string $char
	 * @param int $nb
	 * @return string
	 */
	public static function getStringWithSameChar(string $char, int $nb): string
	{
		$string = '';
		for ($numChar=0; $numChar<$nb; $numChar++) {
			$string .= $char;
		}
		return $string;
	}

	/**
	 * retourne le nombre de caractère $char dans la chaine $str
	 * @param string $str
	 * @param string $char
	 * @return int
	 */
	public static function getNumberOccurrencesOfPreciseChar(string $str, string $char): int
	{
		return substr_count($str, $char);
	}

	/**
	 * retourne le nombre de caractère présent dans le tableau/string $listChar dans la chaine $str
	 * @param string $str
	 * @param $listChar
	 * @return int
	 */
	public static function getNumberOccurrencesOfListChar(string $str, $listChar): int
	{
		if (!is_array($listChar)) {
			$stringListeChar = (string) $listChar;
			$listChar = array();
			$strlen = strlen($stringListeChar);
			for ($numChar=0; $numChar<$strlen; $numChar++) {
				$listChar[] = $stringListeChar[$numChar];
			}
		}

		$nbChar = 0;
		foreach ($listChar as $char) {
			$nbChar += substr_count($str, $char);
		}
		return $nbChar;
	}

	/**
	 * Teste si la chaîne de caractère ne contient que le même caractère (un seul caractère utilisé)
	 * @param string str la chaîne de caractère à tester
	 * @return bool true si la chaîne ne contient que le même caractère, false si la chaîne contient au moins 2 caractères différents
	 */
	public static function containsOnlySameChar(string $str): bool
	{
		return strlen(count_chars($str, 3)) === 1;
	}

	/**
	 * Teste si la chaîne de caractère ne contient que des caractères différents (pas de caractère en double)
	 * @param string str la chaîne de caractère à tester
	 * @return bool true si la chaîne ne contient que des caractères différents, false si elle contient au moins un même caractère présent au moins 2 fois
	 */
	public static function containsOnlyDifferentChar(string $str): bool
	{
		foreach (count_chars($str, 1) as $val) {
			if ($val > 1) {
				return false;
			}
		}
		return true;
	}

	/**
	 * retourne true si $str contient au minimum $min caractères différents
	 * @param string $str
	 * @param int $min
	 * @return bool
	 */
	public static function nbCharUniqueMinimum(string $str, int $min=2): bool
	{
		return count(count_chars($str, 1)) >= $min;
	}

	/**
	 * retourne true si $str contient au maximum $max caractères différents
	 * @param string $str
	 * @param int $max
	 * @return bool
	 */
	public static function nbCharUniqueMaximum(string $str, int $max=2): bool
	{
		return count(count_chars($str, 1)) <= $max;
	}

	// ========== Transformation ==========

	/**
	 * Gère les singuliers/pluriels dans une chaîne de caractères en fonction d'un nombre
	 * @param float $nb le nombre d'élément qui permet de savoir si la chaîne doit être au singulier ou au pluriel (1 par défaut)
	 * @param string $string la chaîne de caractères à mettre au singulier ou au pluriel
	 * @param array $values ??? (array() par défaut)
	 * @return string la chaîne de caractères mise au singulier ou au pluriel
	 * @author Jay Salvat (blog.jaysalvat.com)
	 * @link http://blog.jaysalvat.com/articles/gerer-facilement-les-singuliers-pluriels-en-php.php
	 */
	public static function pluralize(float $nb, string $string, array $values = []): string
	{
		// remplace {#} par le chiffre
		$string = str_replace('{#}', $nb, $string);
		// cherche toutes les occurences de {...}
		preg_match_all("/\{(.*?)\}/", $string, $matches);
		foreach($matches[1] as $k=>$v) {
			// on coupe l'occurence à |
			$part = explode('|', $v);
			// si aucun
			if ($nb === 0) {
				$mod = (count($part) === 1) ? '' : $part[0];
			}
			// si singulier
			else if ($nb === 1) {
				$mod = (count($part) === 1) ? '' : ((count($part) == 2) ? $part[0] : $part[1]);
			}
			// sinon pluriel
			else {
				$mod = (count($part) === 1) ? $part[0] : ((count($part) == 2) ? $part[1] : $part[2]);
			}
			// remplace les occurences trouvées par le bon résultat.
			$string = str_replace($matches[0][$k], $mod , $string);
		}
		// retourne le résultat en y incluant éventuellement les valeurs passées
		return vsprintf($string, $values);
	}

	/**
	 * Takes multiple words separated by spaces and underscores them
	 * @param string $str
	 * @return string
	 */
	public static function underscore(string $str): string
	{
		return preg_replace('/[\s]+/', '_', strtolower(trim($str)));
	}

	/**
	 * Takes multiple words separated by underscores and changes them to spaces
	 * @param string $str
	 * @return string
	 */
	public static function humanize(string $str): string
	{
		return ucwords(preg_replace('/[_]+/', ' ', strtolower(trim($str))));
	}

	/**
	 *
	 * @param string $str
	 * @return string
	 */
	public static function toSnakeCase(string $str): string
	{
		return (new CamelCaseToSnakeCaseNameConverter())->normalize($str);
	}

	/**
	 * Takes multiple words separated by spaces or underscores and camelizes them
	 * @param string $str
	 * @return string
	 */
	public static function toCamelCase(string $str): string
	{
		return (new CamelCaseToSnakeCaseNameConverter())->denormalize($str);

		//$str = 'x'.strtolower(trim($str));
		//$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));
		//return substr(str_replace(' ', '', $str), 1);
	}

	/**
	 * @param string $str
	 * @param string $replace
	 * @return string
	 */
	public static function removeSpaces(string $str, string $replace=''): string
	{
		$str = str_replace('&nbsp;', $replace, $str);
		$str = preg_replace('#\s+#', $replace, $str);
		// $str = preg_replace('#([\s\t]*)#', $replace, $str);
		return $str;
	}

	/**
	 * @param string $str
	 * @param string $replace
	 * @return string
	 */
	public static function removeLineBreak(string $str, string $replace=''): string
	{
		return self::normalizeBreaks($str, $replace);
	}

	/**
	 * Normalize line breaks in a string.
	 * Converts UNIX LF, Mac CR and Windows CRLF line breaks into a single line break format.
	 * Defaults to CRLF (for message bodies) and preserves consecutive breaks.
	 * @param string $text
	 * @param string $breaktype What kind of line break to use, defaults to CRLF
	 * @return string
	 */
	public static function normalizeBreaks(string $text, string $breaktype = "\r\n"): string
	{
		return preg_replace('/(\r\n|\r|\n)/ms', $breaktype, $text);
	}

	/**
	 * @param string $str
	 * @param string $replace
	 * @return string
	 */
	public static function removePunctuation(string $str, string $replace=''): string
	{
		$charList = [',', '‚', ';', ':', '.', '…', '?', '!', '"', '\'', '(', ')', '[', ']', '{', '}', '‘', '’', '“', '”', '«', '»', '<', '>'];
		foreach ($charList as $char) {
			$str = preg_replace('#([\s]*)\\'.$char.'([\s]*)#', $replace, $str);
		}
		return $str;
	}

	/**
	 * Reduces multiple instances of a particular character.  Example:
	 * Fred, Bill,, Joe, Jimmy becomes: Fred, Bill, Joe, Jimmy
	 * @param string $str
	 * @param string $character the character you wish to reduce
	 * @param bool $trim true/false - whether to trim the character from the beginning/end
	 * @return string
	 */
	public static function reduceMultiples(string $str, string $character=',', $trim=false): string
	{
		$str = preg_replace('#'.preg_quote($character, '#').'{2,}#', $character, $str);
		if ($trim === true) {
			$str = trim($str, $character);
		}
		return $str;
	}

	/**
	 * Add's _1 to a string or increment the ending number to allow _2, _3, etc
	 * @param string $str required
	 * @param string $separator What should the duplicate number be appended with
	 * @param int $first Which number should be used for the first dupe increment
	 * @return string
	 */
	public static function increment(string $str, string $separator = '_', int $first = 1): string
	{
		preg_match('/(.+)'.$separator.'([0-9]+)$/', $str, $match);
		return isset($match[2]) ? $match[1].$separator.($match[2] + 1) : $str.$separator.$first;
	}

	/**
	 *
	 * @param string $data
	 * @param integer $num number of repeats
	 * @return string
	 */
	public static function repeater(string $data, int $num = 1): string
	{
		return (($num > 0) ? str_repeat($data, $num) : '');
	}

	/**
	 * Supply a string and an array of disallowed words and any matched words will be converted to #### or to the replacement word you've submitted.
	 * @param string $str the text string
	 * @param array $censored the array of censoered words
	 * @param string $replacement the optional replacement value
	 * @return string
	 */
	public static function censorWord(string $str, array $censored, string $replacement='####'): string
	{
		if (empty($censored)) {
			return $str;
		}

		$str = ' '.$str.' ';

		// \w, \b and a few others do not match on a unicode character set for performance reasons. As a result words like über will not match on a word boundary. Instead, we'll assume that a bad word will be bookeneded by any of these characters.
		$delim = '[-_\'\"`(){}<>\[\]|!?@#%&,.:;^~*+=\/ 0-9\n\r\t]';

		foreach ($censored as $badword) {
			if ($replacement !== '') {
				$str = preg_replace("/({$delim})(".str_replace('\*', '\w*?', preg_quote($badword, '/')).")({$delim})/i", "\\1{$replacement}\\3", $str);
			}
			else {
				$str = preg_replace("/({$delim})(".str_replace('\*', '\w*?', preg_quote($badword, '/')).")({$delim})/ie", "'\\1'.str_repeat('#', strlen('\\2')).'\\3'", $str);
			}
		}
		return trim($str);
	}

	/**
	 * Wraps text at the specified character.  Maintains the integrity of words. Anything placed between {unwrap}{/unwrap} will not be word wrapped, nor will URLs.
	 * @param string the text string
	 * @param int the number of characters to wrap at
	 * @return string
	 */
	public static function wrapWord(string $str, int $charlim): string
	{
		// Se the character limit
		if ( ! is_numeric($charlim)) {
			$charlim = 76;
		}

		// Reduce multiple spaces
		$str = preg_replace('/ +/', ' ', $str);

		// Standardize newlines
		if (strpos($str, "\r") !== false) {
			$str = str_replace(array("\r\n", "\r"), "\n", $str);
		}

		// If the current word is surrounded by {unwrap} tags we'll
		// strip the entire chunk and replace it with a marker.
		$unwrap = array();
		if (preg_match_all("|(\{unwrap\}.+?\{/unwrap\})|s", $str, $matches)) {
			$nb = count($matches['0']);
			for ($i = 0; $i < $nb; $i++) {
				$unwrap[] = $matches['1'][$i];
				$str = str_replace($matches['1'][$i], '{{unwrapped'.$i.'}}', $str);
			}
		}

		// Use PHP's native function to do the initial wordwrap.
		// We set the cut flag to FALSE so that any individual words that are
		// too long get left alone.  In the next step we'll deal with them.
		$str = wordwrap($str, $charlim, "\n", false);

		// Split the string into individual lines of text and cycle through them
		$output = '';
		foreach (explode("\n", $str) as $line) {
			// Is the line within the allowed character count? If so we'll join it to the output and continue
			if (strlen($line) <= $charlim) {
				$output .= $line."\n";
				continue;
			}

			$temp = '';
			while ((strlen($line)) > $charlim) {
				// If the over-length word is a URL we won't wrap it
				if (preg_match("!\[url.+\]|://|wwww.!", $line)) {
					break;
				}

				// Trim the word down
				$temp .= substr($line, 0, $charlim-1);
				$line = substr($line, $charlim-1);
			}

			// If $temp contains data it means we had to split up an over-length word into smaller chunks so we'll add it back to our current line
			if ($temp !== '') {
				$output .= $temp."\n".$line;
			}
			else {
				$output .= $line;
			}

			$output .= "\n";
		}

		// Put our markers back
		if (count($unwrap) > 0) {
			foreach ($unwrap as $key => $val) {
				$output = str_replace('{{unwrapped'.$key.'}}', $val, $output);
			}
		}

		// Remove the unwrap tags
		$output = str_replace(array('{unwrap}', '{/unwrap}'), '', $output);

		return $output;
	}

	/**
	 * @link https://github.com/WordPress/WordPress/blob/a2693fd8602e3263b5925b9d799ddd577202167d/wp-includes/formatting.php#L1528
	 * @param string $string
	 * @return string
	 */
	public static function removeAccents(string $string): string
	{
		if (!preg_match('/[\x80-\xff]/', $string)) {
			return $string;
		}

		// $string = strtr($string, "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ", "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");

		$chars = [
			// Decompositions for Latin-1 Supplement
			'ª' => 'a', 'º' => 'o',
			'À' => 'A', 'Á' => 'A',
			'Â' => 'A', 'Ã' => 'A',
			'Ä' => 'A', 'Å' => 'A',
			'Æ' => 'AE','Ç' => 'C',
			'È' => 'E', 'É' => 'E',
			'Ê' => 'E', 'Ë' => 'E',
			'Ì' => 'I', 'Í' => 'I',
			'Î' => 'I', 'Ï' => 'I',
			'Ð' => 'D', 'Ñ' => 'N',
			'Ò' => 'O', 'Ó' => 'O',
			'Ô' => 'O', 'Õ' => 'O',
			'Ö' => 'O', 'Ù' => 'U',
			'Ú' => 'U', 'Û' => 'U',
			'Ü' => 'U', 'Ý' => 'Y',
			'Þ' => 'TH','ß' => 's',
			'à' => 'a', 'á' => 'a',
			'â' => 'a', 'ã' => 'a',
			'ä' => 'a', 'å' => 'a',
			'æ' => 'ae','ç' => 'c',
			'è' => 'e', 'é' => 'e',
			'ê' => 'e', 'ë' => 'e',
			'ì' => 'i', 'í' => 'i',
			'î' => 'i', 'ï' => 'i',
			'ð' => 'd', 'ñ' => 'n',
			'ò' => 'o', 'ó' => 'o',
			'ô' => 'o', 'õ' => 'o',
			'ö' => 'o', 'ø' => 'o',
			'ù' => 'u', 'ú' => 'u',
			'û' => 'u', 'ü' => 'u',
			'ý' => 'y', 'þ' => 'th',
			'ÿ' => 'y', 'Ø' => 'O',
			// Decompositions for Latin Extended-A
			'Ā' => 'A', 'ā' => 'a',
			'Ă' => 'A', 'ă' => 'a',
			'Ą' => 'A', 'ą' => 'a',
			'Ć' => 'C', 'ć' => 'c',
			'Ĉ' => 'C', 'ĉ' => 'c',
			'Ċ' => 'C', 'ċ' => 'c',
			'Č' => 'C', 'č' => 'c',
			'Ď' => 'D', 'ď' => 'd',
			'Đ' => 'D', 'đ' => 'd',
			'Ē' => 'E', 'ē' => 'e',
			'Ĕ' => 'E', 'ĕ' => 'e',
			'Ė' => 'E', 'ė' => 'e',
			'Ę' => 'E', 'ę' => 'e',
			'Ě' => 'E', 'ě' => 'e',
			'Ĝ' => 'G', 'ĝ' => 'g',
			'Ğ' => 'G', 'ğ' => 'g',
			'Ġ' => 'G', 'ġ' => 'g',
			'Ģ' => 'G', 'ģ' => 'g',
			'Ĥ' => 'H', 'ĥ' => 'h',
			'Ħ' => 'H', 'ħ' => 'h',
			'Ĩ' => 'I', 'ĩ' => 'i',
			'Ī' => 'I', 'ī' => 'i',
			'Ĭ' => 'I', 'ĭ' => 'i',
			'Į' => 'I', 'į' => 'i',
			'İ' => 'I', 'ı' => 'i',
			'Ĳ' => 'IJ','ĳ' => 'ij',
			'Ĵ' => 'J', 'ĵ' => 'j',
			'Ķ' => 'K', 'ķ' => 'k',
			'ĸ' => 'k', 'Ĺ' => 'L',
			'ĺ' => 'l', 'Ļ' => 'L',
			'ļ' => 'l', 'Ľ' => 'L',
			'ľ' => 'l', 'Ŀ' => 'L',
			'ŀ' => 'l', 'Ł' => 'L',
			'ł' => 'l', 'Ń' => 'N',
			'ń' => 'n', 'Ņ' => 'N',
			'ņ' => 'n', 'Ň' => 'N',
			'ň' => 'n', 'ŉ' => 'n',
			'Ŋ' => 'N', 'ŋ' => 'n',
			'Ō' => 'O', 'ō' => 'o',
			'Ŏ' => 'O', 'ŏ' => 'o',
			'Ő' => 'O', 'ő' => 'o',
			'Œ' => 'OE','œ' => 'oe',
			'Ŕ' => 'R','ŕ' => 'r',
			'Ŗ' => 'R','ŗ' => 'r',
			'Ř' => 'R','ř' => 'r',
			'Ś' => 'S','ś' => 's',
			'Ŝ' => 'S','ŝ' => 's',
			'Ş' => 'S','ş' => 's',
			'Š' => 'S', 'š' => 's',
			'Ţ' => 'T', 'ţ' => 't',
			'Ť' => 'T', 'ť' => 't',
			'Ŧ' => 'T', 'ŧ' => 't',
			'Ũ' => 'U', 'ũ' => 'u',
			'Ū' => 'U', 'ū' => 'u',
			'Ŭ' => 'U', 'ŭ' => 'u',
			'Ů' => 'U', 'ů' => 'u',
			'Ű' => 'U', 'ű' => 'u',
			'Ų' => 'U', 'ų' => 'u',
			'Ŵ' => 'W', 'ŵ' => 'w',
			'Ŷ' => 'Y', 'ŷ' => 'y',
			'Ÿ' => 'Y', 'Ź' => 'Z',
			'ź' => 'z', 'Ż' => 'Z',
			'ż' => 'z', 'Ž' => 'Z',
			'ž' => 'z', 'ſ' => 's',
			// Decompositions for Latin Extended-B
			'Ș' => 'S', 'ș' => 's',
			'Ț' => 'T', 'ț' => 't',
			// Euro Sign
			'€' => 'E',
			// GBP (Pound) Sign
			'£' => '',
			// Vowels with diacritic (Vietnamese)
			// unmarked
			'Ơ' => 'O', 'ơ' => 'o',
			'Ư' => 'U', 'ư' => 'u',
			// grave accent
			'Ầ' => 'A', 'ầ' => 'a',
			'Ằ' => 'A', 'ằ' => 'a',
			'Ề' => 'E', 'ề' => 'e',
			'Ồ' => 'O', 'ồ' => 'o',
			'Ờ' => 'O', 'ờ' => 'o',
			'Ừ' => 'U', 'ừ' => 'u',
			'Ỳ' => 'Y', 'ỳ' => 'y',
			// hook
			'Ả' => 'A', 'ả' => 'a',
			'Ẩ' => 'A', 'ẩ' => 'a',
			'Ẳ' => 'A', 'ẳ' => 'a',
			'Ẻ' => 'E', 'ẻ' => 'e',
			'Ể' => 'E', 'ể' => 'e',
			'Ỉ' => 'I', 'ỉ' => 'i',
			'Ỏ' => 'O', 'ỏ' => 'o',
			'Ổ' => 'O', 'ổ' => 'o',
			'Ở' => 'O', 'ở' => 'o',
			'Ủ' => 'U', 'ủ' => 'u',
			'Ử' => 'U', 'ử' => 'u',
			'Ỷ' => 'Y', 'ỷ' => 'y',
			// tilde
			'Ẫ' => 'A', 'ẫ' => 'a',
			'Ẵ' => 'A', 'ẵ' => 'a',
			'Ẽ' => 'E', 'ẽ' => 'e',
			'Ễ' => 'E', 'ễ' => 'e',
			'Ỗ' => 'O', 'ỗ' => 'o',
			'Ỡ' => 'O', 'ỡ' => 'o',
			'Ữ' => 'U', 'ữ' => 'u',
			'Ỹ' => 'Y', 'ỹ' => 'y',
			// acute accent
			'Ấ' => 'A', 'ấ' => 'a',
			'Ắ' => 'A', 'ắ' => 'a',
			'Ế' => 'E', 'ế' => 'e',
			'Ố' => 'O', 'ố' => 'o',
			'Ớ' => 'O', 'ớ' => 'o',
			'Ứ' => 'U', 'ứ' => 'u',
			// dot below
			'Ạ' => 'A', 'ạ' => 'a',
			'Ậ' => 'A', 'ậ' => 'a',
			'Ặ' => 'A', 'ặ' => 'a',
			'Ẹ' => 'E', 'ẹ' => 'e',
			'Ệ' => 'E', 'ệ' => 'e',
			'Ị' => 'I', 'ị' => 'i',
			'Ọ' => 'O', 'ọ' => 'o',
			'Ộ' => 'O', 'ộ' => 'o',
			'Ợ' => 'O', 'ợ' => 'o',
			'Ụ' => 'U', 'ụ' => 'u',
			'Ự' => 'U', 'ự' => 'u',
			'Ỵ' => 'Y', 'ỵ' => 'y',
			// Vowels with diacritic (Chinese, Hanyu Pinyin)
			'ɑ' => 'a',
			// macron
			'Ǖ' => 'U', 'ǖ' => 'u',
			// acute accent
			'Ǘ' => 'U', 'ǘ' => 'u',
			// caron
			'Ǎ' => 'A', 'ǎ' => 'a',
			'Ǐ' => 'I', 'ǐ' => 'i',
			'Ǒ' => 'O', 'ǒ' => 'o',
			'Ǔ' => 'U', 'ǔ' => 'u',
			'Ǚ' => 'U', 'ǚ' => 'u',
			// grave accent
			'Ǜ' => 'U', 'ǜ' => 'u',
		];

		$string = strtr($string, $chars);

		return $string;
	}

	/**
	 * @param string $str The input string
	 * @return string The URL-friendly string (lower-cased, accent-stripped, spaces to dashes).
	 */
	public static function toURLFriendly(string $str): string
	{
		$str = self::removeAccents($str);
		$str = preg_replace(['/[^a-zA-Z0-9 \'-]/', '/[ -\']+/', '/^-|-$/'], ['', '-', ''], $str);
		$str = preg_replace('/-inc$/i', '', $str);
		return strtolower($str);
	}

	/**
	 * Mulit-byte Unserialize
	 * UTF-8 will screw up a serialized string
	 * @param string
	 * @return array|null
	 */
	public static function mb_unserialize(string $string): ?array {
		$array = unserialize(preg_replace_callback('!s:(\d+):"(.*?)";!s', fn($m) => 's:' .strlen($m[2]).':"'.$m[2].'";', $string));
		return $array !== false ? $array : null;
	}

	// ========== Random ==========

	const VOYELLES 					= 'aeiouy';
	const CONSONNES 				= 'bcdfghjklmnpqrstvwxz';
	const LETTRES					= 'abcdefghijklmnopqrstuvwxyz';
	const CHIFFRES					= '0123456789';

	/**
	 * Génère une chaîne de caractères prononcable (c'est-à-dire alternant les consonnes et les voyelles)
	 * @param int $nbChar le nombre de caractères de la chaîne a générer
	 * @param string|null $listeConsonnes la liste des consonnes possibles pour la génération de la chaîne, ou null prendre toutes les consonnes de l'alphabet (null par défaut)
	 * @param string|null $listeVoyelles la liste des voyelles possibles pour la génération de la chaîne, ou null prendre toutes les voyelles de l'alphabet (null par défaut)
	 * @param bool $premiereLettreConsonneAleatoire true pour choisir aléatoirement de commencer par une consonne ou une voyelle, false sinon (false par défaut)
	 * @param bool $premiereLettreConsonne true pour commencer par une consonne, false pour commencer par une voyelle (true par défaut). Ce paramètre est pris en compte seulement si $premiereLettreConsonneAleatoire vaut false
	 * @return string la chaîne générée
	 */
	public static function getRandomPronounceableWord(int $nbChar, ?string $listeConsonnes=null, ?string $listeVoyelles=null, bool $premiereLettreConsonneAleatoire=false, bool $premiereLettreConsonne=true): string
	{
		if ($premiereLettreConsonneAleatoire) {
			$pair = (rand(0, 1) === 0);
		}
		else {
			$pair = ($premiereLettreConsonne);
		}

		if ($listeConsonnes === null) {
			$listeConsonnes = self::CONSONNES;
		}
		if ($listeVoyelles === null) {
			$listeVoyelles = self::VOYELLES;
		}
		$nbConsonnes = strlen($listeConsonnes);
		$nbVoyelles = strlen($listeVoyelles);

		$motPrononcable = '';
		for ($i=0; $i<$nbChar; $i++) {
			if ($pair === true) {
				$motPrononcable .= $listeConsonnes[mt_rand(0, $nbConsonnes-1)];
			}
			else {
				$motPrononcable .= $listeVoyelles[mt_rand(0, $nbVoyelles-1)];
			}

			$pair = !$pair;
		}

		return $motPrononcable;
	}

	/**
	 * Génère une chaîne de caractères
	 * @param int $nbChar le nombre de caractères de la chaîne a générer
	 * @param string $listeChar la liste des caractères possibles pour la génération de la chaîne
	 * @return string la chaîne générée
	 */
	public static function getRandomString(int $nbChar, string $listeChar): string
	{
		$strRand = '';
		$nbLettres = strlen($listeChar);
		for ($i=0; $i<$nbChar; $i++) {
			$charRand = $listeChar[mt_rand(0, $nbLettres-1)];
			$strRand .= $charRand;
		}
		return $strRand;
	}

	/**
	 * Génère une chaîne de caractères alphabétiques
	 * @param int $nbChar le nombre de caractères de la chaîne a générer
	 * @param bool $uppercaseEnabled true pour générer des caractères alphabétiques majuscules, false sinon (false par défaut)
	 * @param bool $lowercaseEnabled true pour générer des caractères alphabétiques minuscules, false sinon (true par défaut)
	 * @return string la chaîne générée
	 */
	public static function getRandomAlphaString(int $nbChar, bool $uppercaseEnabled=false, bool $lowercaseEnabled=true): string
	{
		if (!$lowercaseEnabled && !$uppercaseEnabled) {
			return null;
		}

		$listeLettres = self::LETTRES;
		$nbLettres = strlen($listeLettres);

		$suiteCaractereAlphabetique = '';
		for ($i=0; $i<$nbChar; $i++) {
			$caractereAlphabetique = $listeLettres[mt_rand(0, $nbLettres-1)];

			if ($lowercaseEnabled && $uppercaseEnabled) {
				if (rand(0, 1) === 1) {
					$caractereAlphabetique = strtoupper($caractereAlphabetique);
				}
			}
			elseif ($uppercaseEnabled) {
				$caractereAlphabetique = strtoupper($caractereAlphabetique);
			}

			$suiteCaractereAlphabetique .= $caractereAlphabetique;
		}

		return $suiteCaractereAlphabetique;
	}

	/**
	 * Génère une chaîne de caractères avec un certains nombre de chiffres.
	 * @param int $nbChar le nombre de caractères de la chaîne a générer
	 * @param bool $startWith0 true pour que la chaîne ne commence pas par le chiffre 0, false pour commencer par n'importe quel chiffre (false par défaut)
	 * @return string la chaîne générée
	 */
	public static function getRandomNumericString(int $nbChar, bool $startWith0=false): string
	{
		$listeChiffres = self::CHIFFRES;
		$nbChiffres = strlen($listeChiffres);

		$suiteCaractereNumerique = '';
		for ($i=0; $i<$nbChar; $i++) {
			$caractereNumerique = $listeChiffres[mt_rand(0, $nbChiffres-1)];

			if (false === $startWith0 && 0 === $i && '0' === $caractereNumerique) {
				$i--;
			}
			else {
				$suiteCaractereNumerique .= $caractereNumerique;
			}
		}

		return $suiteCaractereNumerique;
	}

	/**
	 * Génère une chaîne de caractères alphabétiques et numériques ()
	 * @param int $nbChar le nombre de caractères de la chaîne a générer
	 * @param bool $uppercaseEnabled true pour générer des caractères alphabétiques majuscules, false sinon (false par défaut)
	 * @param bool $lowercaseEnabled true pour générer des caractères alphabétiques minuscules, false sinon (true par défaut)
	 * @return string la chaîne générée
	 */
	public static function getRandomAlphanumericString(int $nbChar, bool $uppercaseEnabled=false, bool $lowercaseEnabled=true): string
	{
		$nbTypeCaractere = 1;
		if ($uppercaseEnabled && $lowercaseEnabled) {
			$nbTypeCaractere = 3;
		}
		elseif ($uppercaseEnabled || $lowercaseEnabled) {
			$nbTypeCaractere = 2;
		}

		do {
			$suiteCaractereAlphanumerique = '';
			for ($i=0; $i<$nbChar; $i++) {
				if (!$uppercaseEnabled && !$lowercaseEnabled) {
					$suiteCaractereAlphanumerique .= self::getRandomNumericString(1);
					continue;
				}

				$caractereAlphanumerique = null;
				$typeCaractere = rand(1, $nbTypeCaractere);
				switch ($typeCaractere) {
					case 1 :
						$caractereAlphanumerique = self::getRandomNumericString(1);
						break;

					case 2 :
						$caractereAlphanumerique = self::getRandomAlphaString(1);
						if ($uppercaseEnabled && !$lowercaseEnabled) {
							$caractereAlphanumerique = strtoupper(self::getRandomAlphaString(1));
						}
						break;

					case 3 :
						$caractereAlphanumerique = strtoupper(self::getRandomAlphaString(1));
						break;
				}

				$suiteCaractereAlphanumerique .= $caractereAlphanumerique;
			}
		}
		// Tant que la chaîne générée ne contient que des lettres ou que des chiffres, on recommence, car on doit retourner une chaîne comprenant à la fois des lettres et des chiffres
		while ($nbTypeCaractere > 1 && (ctype_digit($suiteCaractereAlphanumerique) || strpbrk($suiteCaractereAlphanumerique, self::CHIFFRES) === false));

		return $suiteCaractereAlphanumerique;
	}

}