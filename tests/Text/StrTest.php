<?php

declare(strict_types=1);

namespace Tests\Text;

use Osimatic\Text\Str;
use PHPUnit\Framework\TestCase;

final class StrTest extends TestCase
{
	/* ===================== replaceListChar() ===================== */

	public function testReplaceListChar(): void
	{
		$str = 'Hello World';
		$replacements = ['o' => '0', 'l' => '1'];
		$result = Str::replaceListChar($str, $replacements);
		$this->assertSame('He110 W0r1d', $result);
	}

	public function testReplaceListCharWithUppercase(): void
	{
		$str = 'Hello WORLD';
		$replacements = ['o' => '0', 'l' => '1'];
		$result = Str::replaceListChar($str, $replacements, replaceUppercaseChar: true);
		$this->assertSame('He110 W0R1D', $result);
	}

	public function testReplaceListCharWithLowercase(): void
	{
		$str = 'Hello WORLD';
		$replacements = ['O' => '0', 'L' => '1'];
		$result = Str::replaceListChar($str, $replacements, replaceLowercaseChar: true);
		$this->assertSame('He110 W0R1D', $result);
	}

	/* ===================== removeListeChar() ===================== */

	public function testRemoveListeChar(): void
	{
		$str = 'Hello World';
		$charsToRemove = ['o', 'l'];
		$result = Str::removeListeChar($str, $charsToRemove);
		$this->assertSame('He Wrd', $result);
	}

	public function testRemoveListeCharWithUppercase(): void
	{
		$str = 'Hello WORLD';
		$charsToRemove = ['o', 'l'];
		$result = Str::removeListeChar($str, $charsToRemove, replaceUppercaseChar: true);
		$this->assertSame('He WRD', $result);
	}

	/* ===================== levenshtein() ===================== */

	public function testLevenshtein(): void
	{
		$this->assertSame(0, Str::levenshtein('test', 'test'));
		$this->assertSame(1, Str::levenshtein('test', 'tests'));
		$this->assertSame(1, Str::levenshtein('test', 'best')); // 1 substitution (t->b)
		$this->assertSame(3, Str::levenshtein('test', 'testing')); // 3 insertions
	}

	/* ===================== suggest() ===================== */

	public function testSuggest(): void
	{
		$dictionary = ['apple', 'application', 'apply', 'banana'];
		$this->assertSame('apple', Str::suggest('aple', $dictionary));
		$this->assertSame('banana', Str::suggest('banane', $dictionary));
	}

	public function testSuggestNoMatch(): void
	{
		$dictionary = ['apple', 'banana'];
		$this->assertNull(Str::suggest('xyz', $dictionary));
	}

	/* ===================== compare() ===================== */

	public function testCompareNumeric(): void
	{
		$this->assertSame(-1, Str::compare(5, 10));
		$this->assertSame(1, Str::compare(10, 5));
		$this->assertSame(0, Str::compare(5, 5));
	}

	public function testCompareString(): void
	{
		$this->assertLessThan(0, Str::compare('apple', 'banana'));
		$this->assertGreaterThan(0, Str::compare('banana', 'apple'));
		$this->assertSame(0, Str::compare('test', 'test'));
	}

	public function testCompareCaseSensitive(): void
	{
		$result = Str::compare('Test', 'test', caseSensitive: true);
		$this->assertNotSame(0, $result);
	}

	public function testCompareCaseInsensitive(): void
	{
		$result = Str::compare('Test', 'test', caseSensitive: false);
		$this->assertSame(0, $result);
	}

	/* ===================== truncateTextAtEnd() ===================== */

	public function testTruncateTextAtEnd(): void
	{
		$str = 'This is a very long text';
		$result = Str::truncateTextAtEnd($str, 12);
		// Le résultat doit faire exactement 10 caractères (incluant l'ellipsis)
		$this->assertSame('This is a…', $result);
		$this->assertSame(10, mb_strlen($result));
	}

	public function testTruncateTextAtEndNoTruncate(): void
	{
		$str = 'Short';
		$result = Str::truncateTextAtEnd($str, 10);
		$this->assertSame('Short', $result);
	}

	public function testTruncateTextAtEndDontCutWord(): void
	{
		$str = 'This is a test';
		$result = Str::truncateTextAtEnd($str, 8, dontCutInMiddleOfWord: true);
		// Le résultat doit faire exactement 8 caractères (incluant l'ellipsis)
		$this->assertSame('This is…', $result);
		$this->assertSame(8, mb_strlen($result));
	}

	/* ===================== truncateTextAtBeginning() ===================== */

	public function testTruncateTextAtBeginning(): void
	{
		$str = 'This is a very long text';
		$result = Str::truncateTextAtBeginning($str, 10, dontCutInMiddleOfWord: true);
		$this->assertStringStartsWith('…', $result);
		$this->assertLessThanOrEqual(11, strlen($result)); // 10 + ellipsis
	}

	/* ===================== truncateTextInMiddle() ===================== */

	public function testTruncateTextInMiddle(): void
	{
		$str = 'This is a very long text to test';
		$result = Str::truncateTextInMiddle($str, 15);
		$this->assertStringContainsString('[…]', $result);
	}

	/* ===================== ellipsize() ===================== */

	public function testEllipsize(): void
	{
		$str = 'This is a very long text';
		$result = Str::ellipsize($str, 15);
		$this->assertLessThanOrEqual(20, strlen($result));
		$this->assertStringContainsString('&hellip;', $result);
	}

	/* ===================== checkLowercase() ===================== */

	public function testCheckLowercase(): void
	{
		$this->assertTrue(Str::checkLowercase('hello'));
		$this->assertFalse(Str::checkLowercase('Hello'));
		$this->assertFalse(Str::checkLowercase('HELLO'));
	}

	/* ===================== checkUppercase() ===================== */

	public function testCheckUppercase(): void
	{
		$this->assertTrue(Str::checkUppercase('HELLO'));
		$this->assertFalse(Str::checkUppercase('Hello'));
		$this->assertFalse(Str::checkUppercase('hello'));
	}

	/* ===================== checkLength() ===================== */

	public function testCheckLength(): void
	{
		$this->assertTrue(Str::checkLength('test', 3, 5));
		$this->assertTrue(Str::checkLength('test', 4, 4));
		$this->assertFalse(Str::checkLength('test', 5, 10));
		$this->assertFalse(Str::checkLength('test', 1, 3));
	}

	public function testCheckLengthInvalidRange(): void
	{
		$this->assertFalse(Str::checkLength('test', 10, 5));
	}

	/* ===================== checkForAlphabeticCharacters() ===================== */

	public function testCheckForAlphabeticCharacters(): void
	{
		$this->assertTrue(Str::checkForAlphabeticCharacters('test', 3, 5));
		$this->assertFalse(Str::checkForAlphabeticCharacters('test123', 3, 10));
		$this->assertFalse(Str::checkForAlphabeticCharacters('te', 3, 5));
	}

	/* ===================== checkForAlphanumericCharacters() ===================== */

	public function testCheckForAlphanumericCharacters(): void
	{
		$this->assertTrue(Str::checkForAlphanumericCharacters('test123', 5, 10));
		$this->assertFalse(Str::checkForAlphanumericCharacters('test-123', 5, 10));
		$this->assertFalse(Str::checkForAlphanumericCharacters('te', 5, 10));
	}

	/* ===================== checkForNumericCharacters() ===================== */

	public function testCheckForNumericCharacters(): void
	{
		$this->assertTrue(Str::checkForNumericCharacters('12345', 3, 10));
		$this->assertFalse(Str::checkForNumericCharacters('123a5', 3, 10));
		$this->assertFalse(Str::checkForNumericCharacters('12', 3, 10));
	}

	public function testCheckForNumericCharactersStartWithZero(): void
	{
		$this->assertTrue(Str::checkForNumericCharacters('01234', 3, 10, canStartWithZero: true));
		$this->assertFalse(Str::checkForNumericCharacters('01234', 3, 10, canStartWithZero: false));
	}

	/* ===================== ctype_alpha_and_num() ===================== */

	public function testCtypeAlphaAndNum(): void
	{
		$this->assertTrue(Str::ctype_alpha_and_num('test123'));
		$this->assertFalse(Str::ctype_alpha_and_num('123456'));
		$this->assertFalse(Str::ctype_alpha_and_num('test-123'));
	}

	/* ===================== getStringWithBlank() ===================== */

	public function testGetStringWithBlank(): void
	{
		$result = Str::getStringWithBlank('test', 10);
		$this->assertSame('test      ', $result);
		$this->assertSame(10, strlen($result));
	}

	public function testGetStringWithBlankAtBeginning(): void
	{
		$result = Str::getStringWithBlank('test', 10, addBlankInBeginning: true);
		$this->assertSame('      test', $result);
		$this->assertSame(10, strlen($result));
	}

	/* ===================== getStringWithSameChar() ===================== */

	public function testGetStringWithSameChar(): void
	{
		$this->assertSame('aaaaa', Str::getStringWithSameChar('a', 5));
		$this->assertSame('-----', Str::getStringWithSameChar('-', 5));
	}

	/* ===================== getNumberOccurrencesOfPreciseChar() ===================== */

	public function testGetNumberOccurrencesOfPreciseChar(): void
	{
		$this->assertSame(2, Str::getNumberOccurrencesOfPreciseChar('hello', 'l')); // 'hello' a 2 'l'
		$this->assertSame(1, Str::getNumberOccurrencesOfPreciseChar('hello', 'h'));
		$this->assertSame(0, Str::getNumberOccurrencesOfPreciseChar('hello', 'x'));
	}

	/* ===================== getNumberOccurrencesOfListChar() ===================== */

	public function testGetNumberOccurrencesOfListChar(): void
	{
		$this->assertSame(3, Str::getNumberOccurrencesOfListChar('hello', ['l', 'o'])); // 2 'l' + 1 'o' = 3
		$this->assertSame(2, Str::getNumberOccurrencesOfListChar('hello', ['h', 'e']));
	}

	public function testGetNumberOccurrencesOfListCharString(): void
	{
		$this->assertSame(3, Str::getNumberOccurrencesOfListChar('hello', 'lo')); // 2 'l' + 1 'o' = 3
	}

	/* ===================== containsOnlySameChar() ===================== */

	public function testContainsOnlySameChar(): void
	{
		$this->assertTrue(Str::containsOnlySameChar('aaaaa'));
		$this->assertTrue(Str::containsOnlySameChar('11111'));
		$this->assertFalse(Str::containsOnlySameChar('aaaba'));
	}

	/* ===================== containsOnlyDifferentChar() ===================== */

	public function testContainsOnlyDifferentChar(): void
	{
		$this->assertTrue(Str::containsOnlyDifferentChar('abcde'));
		$this->assertFalse(Str::containsOnlyDifferentChar('abcdea'));
		$this->assertFalse(Str::containsOnlyDifferentChar('aabcd'));
	}

	/* ===================== nbCharUniqueMinimum() ===================== */

	public function testNbCharUniqueMinimum(): void
	{
		$this->assertTrue(Str::nbCharUniqueMinimum('abcde', 3));
		$this->assertTrue(Str::nbCharUniqueMinimum('aabbcc', 3));
		$this->assertFalse(Str::nbCharUniqueMinimum('aabbcc', 5));
	}

	/* ===================== nbCharUniqueMaximum() ===================== */

	public function testNbCharUniqueMaximum(): void
	{
		$this->assertTrue(Str::nbCharUniqueMaximum('aabbcc', 5));
		$this->assertFalse(Str::nbCharUniqueMaximum('abcdefg', 5));
	}

	/* ===================== pluralize() ===================== */

	public function testPluralize(): void
	{
		$this->assertSame('Aucun item', Str::pluralize('{Aucun item|1 item|{#} items}', 0));
		$this->assertSame('1 item', Str::pluralize('{Aucun item|1 item|{#} items}', 1));
		$this->assertSame('5 items', Str::pluralize('{Aucun item|1 item|{#} items}', 5));
	}

	public function testPluralizeTwoForms(): void
	{
		$this->assertSame('1 item', Str::pluralize('{1 item|{#} items}', 1));
		$this->assertSame('5 items', Str::pluralize('{1 item|{#} items}', 5));
	}

	/* ===================== underscore() ===================== */

	public function testUnderscore(): void
	{
		$this->assertSame('hello_world', Str::underscore('Hello World'));
		$this->assertSame('test_string', Str::underscore('Test  String'));
	}

	/* ===================== humanize() ===================== */

	public function testHumanize(): void
	{
		$this->assertSame('Hello World', Str::humanize('hello_world'));
		$this->assertSame('Test String', Str::humanize('test_string'));
	}

	/* ===================== toSnakeCase() ===================== */

	public function testToSnakeCase(): void
	{
		$this->assertSame('hello_world', Str::toSnakeCase('HelloWorld'));
		$this->assertSame('test_string', Str::toSnakeCase('TestString'));
		$this->assertSame('my_class_name', Str::toSnakeCase('MyClassName'));
	}

	/* ===================== toCamelCase() ===================== */

	public function testToCamelCase(): void
	{
		$this->assertSame('helloWorld', Str::toCamelCase('hello_world'));
		$this->assertSame('testString', Str::toCamelCase('test_string'));
	}

	/* ===================== removeSpaces() ===================== */

	public function testRemoveSpaces(): void
	{
		$this->assertSame('HelloWorld', Str::removeSpaces('Hello World'));
		$this->assertSame('Test', Str::removeSpaces('  Test  '));
	}

	public function testRemoveSpacesWithReplacement(): void
	{
		$this->assertSame('Hello-World', Str::removeSpaces('Hello World', '-'));
	}

	/* ===================== removeNonBreakingSpaces() ===================== */

	public function testRemoveNonBreakingSpaces(): void
	{
		$str = "Hello\xE2\x80\xAFWorld";
		$result = Str::removeNonBreakingSpaces($str);
		$this->assertSame('Hello World', $result);
	}

	/* ===================== removeLineBreak() ===================== */

	public function testRemoveLineBreak(): void
	{
		$str = "Hello\nWorld";
		$result = Str::removeLineBreak($str);
		$this->assertSame('HelloWorld', $result);
	}

	/* ===================== normalizeBreaks() ===================== */

	public function testNormalizeBreaks(): void
	{
		$str = "Hello\nWorld\rTest\r\nEnd";
		$result = Str::normalizeBreaks($str);
		$this->assertSame("Hello\r\nWorld\r\nTest\r\nEnd", $result);
	}

	public function testNormalizeBreaksCustom(): void
	{
		$str = "Hello\nWorld\rTest";
		$result = Str::normalizeBreaks($str, ' ');
		$this->assertSame('Hello World Test', $result);
	}

	/* ===================== removePunctuation() ===================== */

	public function testRemovePunctuation(): void
	{
		$str = 'Hello, World! How are you?';
		$result = Str::removePunctuation($str);
		$this->assertSame('Hello World How are you', $result);
	}

	public function testRemovePunctuationWithDoubleSpaces(): void
	{
		// Teste que les doubles espaces créés après suppression de ponctuation sont nettoyés
		$str = 'bonjour : je suis Benoit';
		$result = Str::removePunctuation($str);
		$this->assertSame('bonjour je suis Benoit', $result);
		// Vérifie qu'il n'y a pas de double espace
		$this->assertStringNotContainsString('  ', $result);
	}

	/* ===================== replaceAnnoyingChar() ===================== */

	public function testReplaceAnnoyingChar(): void
	{
		$str = "Hello\xe2\x80\x93World"; // En dash
		$result = Str::replaceAnnoyingChar($str);
		$this->assertSame('Hello-World', $result);
	}

	/* ===================== reduceMultiples() ===================== */

	public function testReduceMultiples(): void
	{
		$str = 'Fred, Bill,, Joe, Jimmy';
		$result = Str::reduceMultiples($str, ',');
		$this->assertSame('Fred, Bill, Joe, Jimmy', $result);
	}

	public function testReduceMultiplesWithTrim(): void
	{
		$str = ',,Fred, Bill,, Joe, Jimmy,,';
		$result = Str::reduceMultiples($str, ',', trim: true);
		$this->assertSame('Fred, Bill, Joe, Jimmy', $result);
	}

	/* ===================== increment() ===================== */

	public function testIncrement(): void
	{
		$this->assertSame('file_1', Str::increment('file'));
		$this->assertSame('file_2', Str::increment('file_1'));
		$this->assertSame('file_3', Str::increment('file_2'));
	}

	public function testIncrementCustomSeparator(): void
	{
		$this->assertSame('file-1', Str::increment('file', '-'));
		$this->assertSame('file-2', Str::increment('file-1', '-'));
	}

	/* ===================== repeater() ===================== */

	public function testRepeater(): void
	{
		$this->assertSame('testtest', Str::repeater('test', 2));
		$this->assertSame('aaaaa', Str::repeater('a', 5));
		$this->assertSame('', Str::repeater('test', 0));
	}

	/* ===================== censorWord() ===================== */

	public function testCensorWord(): void
	{
		$str = 'This is a bad word';
		$censored = ['bad'];
		$result = Str::censorWord($str, $censored);
		$this->assertSame('This is a #### word', $result);
	}

	public function testCensorWordCustomReplacement(): void
	{
		$str = 'This is a bad word';
		$censored = ['bad'];
		$result = Str::censorWord($str, $censored, '***');
		$this->assertSame('This is a *** word', $result);
	}

	/* ===================== removeAccents() ===================== */

	public function testRemoveAccents(): void
	{
		$this->assertSame('eeee', Str::removeAccents('éèêë'));
		$this->assertSame('aaaa', Str::removeAccents('àâäã'));
		$this->assertSame('AEOoe', Str::removeAccents('ÆØœ'));
	}

	public function testRemoveAccentsNoAccents(): void
	{
		$this->assertSame('Hello World', Str::removeAccents('Hello World'));
	}

	/* ===================== toURLFriendly() ===================== */

	public function testToURLFriendly(): void
	{
		$this->assertSame('hello-world', Str::toURLFriendly('Hello World'));
		$this->assertSame('cafe-paris', Str::toURLFriendly('Café Paris'));
		$this->assertSame('test-123', Str::toURLFriendly('Test 123'));
	}

	/* ===================== mb_ucfirst() ===================== */

	public function testMbUcfirst(): void
	{
		$this->assertSame('Hello', Str::mb_ucfirst('hello'));
		$this->assertSame('École', Str::mb_ucfirst('école'));
	}
}