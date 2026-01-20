<?php

namespace Osimatic\Data;

use Osimatic\Network\HTTPMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Service class providing comprehensive utility methods for secure form data processing.
 * This class offers:
 * - String manipulation: trim() with null handling
 * - Type parsing: parseBoolean(), parseInteger(), parseFloat() with validation
 * - Array processing: parseArray() with DoS protection
 * - Enum handling: parseEnum(), parseEnumList() with security validation
 * - HTML sanitization: sanitizeHtml() for XSS prevention
 * - HTTP request handling: setFormData() for PUT/PATCH/DELETE methods
 * - Error message formatting: getErrorMessages() with translation support
 * All methods include proper null handling and security considerations.
 */
class FormService
{
	/**
	 * Trims whitespace characters from the beginning and end of a string, with optional preservation of zero characters and conversion of empty strings to null.
	 * By default, removes space, tab, newline, carriage return, vertical tab, and null byte characters.
	 * When deleteZero is false, preserves null byte characters.
	 * When returnNullIfEmpty is true, returns null instead of an empty string.
	 * Example:
	 * FormService::trim('  hello  ') // Returns: 'hello'
	 * FormService::trim('   ', true, true) // Returns: null
	 * FormService::trim('   ', true, false) // Returns: ''
	 *
	 * @param string|null $value The string value to trim, or null
	 * @param bool $deleteZero Whether to delete null byte characters (\0) in addition to other whitespace (default: true)
	 * @param bool $returnNullIfEmpty Whether to return null instead of an empty string (default: false)
	 * @return string|null The trimmed string, or null if the input was null or empty (when returnNullIfEmpty is true)
	 */
	public static function trim(?string $value, bool $deleteZero=true, bool $returnNullIfEmpty=false): ?string
	{
		if (null === $value) {
			return null;
		}

		$trimmed = $deleteZero ? trim($value) : trim($value, " \n\r\t\v");

		if ($returnNullIfEmpty && '' === $trimmed) {
			return null;
		}

		return $trimmed;
	}

	/**
	 * Parses various input types into an array with optional filtering and string splitting.
	 * If the input is null, returns an empty array. If the input is not already an array, it converts it to an array (optionally splitting strings by a separator).
	 * Empty values can be filtered out, and the array is re-indexed with sequential numeric keys when filtering is enabled.
	 * Includes protection against DoS attacks by limiting array size.
	 * Example:
	 * FormService::parseArray('a,b,c', true, ',') // Returns: ['a', 'b', 'c']
	 * FormService::parseArray(['a', '', 'c'], true) // Returns: ['a', 'c']
	 * FormService::parseArray(['a', '', 'c'], false) // Returns: ['a', '', 'c']
	 * @param mixed $array The input value to parse (can be null, array, string, or any other type)
	 * @param bool $filterEmptyValues Whether to remove empty values and re-index the array with sequential keys (default: true)
	 * @param string|null $separatorIfString If provided and the input is a string, split the string by this separator before returning (default: null)
	 * @param int $maxSize Maximum number of elements allowed in the array to prevent DoS attacks (default: 10000, set to 0 for unlimited)
	 * @return array The parsed array with empty values optionally filtered and re-indexed
	 * @throws \InvalidArgumentException If the array size exceeds the maximum allowed size
	 */
	public static function parseArray(mixed $array, bool $filterEmptyValues=true, ?string $separatorIfString=null, int $maxSize=10000): array
	{
		if (null === $array) {
			return [];
		}
		if (!is_array($array)) {
			$array = null !== $separatorIfString && is_string($array) ? explode($separatorIfString, $array) : [$array];
		}

		// Security: Protect against DoS attacks by limiting array size
		if ($maxSize > 0 && count($array) > $maxSize) {
			throw new \InvalidArgumentException(sprintf('Array size (%d) exceeds maximum allowed size (%d)', count($array), $maxSize));
		}

		return $filterEmptyValues ? array_values(array_filter($array)) : $array;
	}

	/**
	 * Parses a value into a boolean.
	 * Accepts various string representations of boolean values (case-insensitive):
	 * - true: 'true', '1', 'yes', 'on', true, 1
	 * - false: 'false', '0', 'no', 'off', '', false, 0
	 * - null: null, or any invalid value
	 * Example:
	 * FormService::parseBoolean('true') // Returns: true
	 * FormService::parseBoolean('0') // Returns: false
	 * FormService::parseBoolean(1) // Returns: true
	 * FormService::parseBoolean(null) // Returns: null
	 * FormService::parseBoolean('invalid') // Returns: null
	 * @param mixed $value The value to parse into a boolean
	 * @return bool|null The parsed boolean value, or null if the input was null or invalid
	 */
	public static function parseBoolean(mixed $value): ?bool
	{
		if (null === $value) {
			return null;
		}

		if (is_bool($value)) {
			return $value;
		}

		if (is_int($value)) {
			return match ($value) {
				0 => false,
				1 => true,
				default => null,
			};
		}

		if (is_string($value)) {
			$value = mb_strtolower(trim($value));
			if (in_array($value, ['true', '1', 'yes', 'on'], true)) {
				return true;
			}
			if (in_array($value, ['false', '0', 'no', 'off', ''], true)) {
				return false;
			}
		}

		return null;
	}

	/**
	 * Parses a value into an integer with optional range validation.
	 * Example:
	 * FormService::parseInteger('42') // Returns: 42
	 * FormService::parseInteger('42', 0, 100) // Returns: 42
	 * FormService::parseInteger('150', 0, 100) // Returns: null (out of range)
	 * FormService::parseInteger(null) // Returns: null
	 * @param mixed $value The value to parse into an integer
	 * @param int|null $min Minimum allowed value (inclusive, default: null for no minimum)
	 * @param int|null $max Maximum allowed value (inclusive, default: null for no maximum)
	 * @return int|null The parsed integer value, or null if the input was null or invalid or out of range
	 */
	public static function parseInteger(mixed $value, ?int $min=null, ?int $max=null): ?int
	{
		if (null === $value || '' === $value) {
			return null;
		}

		if (!is_numeric($value)) {
			return null;
		}

		$intValue = (int) $value;

		// Validate range
		if (null !== $min && $intValue < $min) {
			return null;
		}
		if (null !== $max && $intValue > $max) {
			return null;
		}

		return $intValue;
	}

	/**
	 * Parses a value into a float with optional range validation.
	 * Example:
	 * FormService::parseFloat('42.5') // Returns: 42.5
	 * FormService::parseFloat('42.5', 0.0, 100.0) // Returns: 42.5
	 * FormService::parseFloat('150.5', 0.0, 100.0) // Returns: null (out of range)
	 * FormService::parseFloat(null) // Returns: null
	 * @param mixed $value The value to parse into a float
	 * @param float|null $min Minimum allowed value (inclusive, default: null for no minimum)
	 * @param float|null $max Maximum allowed value (inclusive, default: null for no maximum)
	 * @return float|null The parsed float value, or null if the input was null or invalid or out of range
	 */
	public static function parseFloat(mixed $value, ?float $min=null, ?float $max=null): ?float
	{
		if (null === $value || '' === $value) {
			return null;
		}

		if (!is_numeric($value)) {
			return null;
		}

		$floatValue = (float) $value;

		// Validate range
		if (null !== $min && $floatValue < $min) {
			return null;
		}
		if (null !== $max && $floatValue > $max) {
			return null;
		}

		return $floatValue;
	}

	/**
	 * Sanitizes HTML content by stripping all tags or allowing only specific safe tags.
	 * By default, removes all HTML tags. When allowBasicFormatting is true, allows safe formatting tags like <b>, <i>, <u>, <em>, <strong>, <br>, <p>.
	 * Dangerous tags like <script>, <style>, <iframe> are removed along with their content before processing.
	 * Example:
	 * FormService::sanitizeHtml('<script>alert("xss")</script>Hello') // Returns: 'Hello'
	 * FormService::sanitizeHtml('<b>Hello</b> <script>bad</script>', true) // Returns: '<b>Hello</b> '
	 * FormService::sanitizeHtml(null) // Returns: null
	 * @param string|null $value The HTML content to sanitize
	 * @param bool $allowBasicFormatting Whether to allow basic safe HTML formatting tags (default: false)
	 * @return string|null The sanitized string, or null if the input was null
	 */
	public static function sanitizeHtml(?string $value, bool $allowBasicFormatting=false): ?string
	{
		if (null === $value) {
			return null;
		}

		// Remove dangerous tags and their content (script, style, iframe, object, embed)
		$value = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $value);
		$value = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $value);
		$value = preg_replace('/<iframe\b[^>]*>.*?<\/iframe>/is', '', $value);
		$value = preg_replace('/<object\b[^>]*>.*?<\/object>/is', '', $value);
		$value = preg_replace('/<embed\b[^>]*>.*?<\/embed>/is', '', $value);

		if ($allowBasicFormatting) {
			// Allow only safe formatting tags
			$allowedTags = '<b><i><u><em><strong><br><p>';
			return strip_tags($value, $allowedTags);
		}

		// Strip all tags
		return strip_tags($value);
	}

	/**
	 * Parses a string value into a backed enum instance by converting the value to the specified case (uppercase or lowercase) and attempting to match it with the enum's valid values.
	 * Returns null if the input value is null or if no matching enum case is found.
	 * Validates that the provided class is actually a BackedEnum for security.
	 * Example:
	 * FormService::parseEnum(Status::class, 'active') // Returns: Status::ACTIVE
	 * FormService::parseEnum(Status::class, 'pending', CASE_LOWER) // Returns: Status::pending
	 * @param string $enum The fully qualified class name of the backed enum to parse into
	 * @param string|null $value The string value to convert to an enum case, or null
	 * @param int $caseType The case conversion to apply before matching (CASE_LOWER or CASE_UPPER, default: CASE_UPPER)
	 * @return \BackedEnum|null The matched enum instance, or null if the value is null or no match is found
	 * @throws \InvalidArgumentException If the provided class is not a BackedEnum
	 */
	public static function parseEnum(string $enum, ?string $value, int $caseType=CASE_UPPER): ?\BackedEnum
	{
		if (null === $value) {
			return null;
		}

		// Security: Validate that the class is actually a BackedEnum
		if (!enum_exists($enum) || !is_subclass_of($enum, \BackedEnum::class)) {
			throw new \InvalidArgumentException(sprintf('Class "%s" is not a valid BackedEnum', $enum));
		}

		return $enum::tryFrom(CASE_LOWER === $caseType ? mb_strtolower($value) : mb_strtoupper($value)); // @phpstan-ignore
	}

	/**
	 * Parses an input value into an array of enum instances by first converting it to an array (with optional string splitting), then mapping each element to an enum instance using either a custom callable or the enum class name. The resulting list can be filtered to include only specific allowed enum values, with invalid values removed and the array re-indexed.
	 * Example:
	 * FormService::parseEnumList('ADMIN,USER', UserRole::class, separatorIfString: ',')
	 * FormService::parseEnumList(['ADMIN', 'USER'], UserRole::class, [UserRole::ADMIN])
	 * @param mixed $array The input value to parse (can be string, array, or other types that will be processed by parseArray)
	 * @param string $className The fully qualified class name of the enum to parse into (used if parseFunction is null)
	 * @param array|null $allowedValues Optional array of allowed enum values to filter the result (values not in this list are removed, default: null for no filtering)
	 * @param callable|null $parseFunction Optional custom function to convert each element to an enum (default: null to use the enum class directly)
	 * @param string|null $separatorIfString If the input is a string, split it by this separator before parsing (default: null)
	 * @return array Array of enum instances, filtered by allowedValues if provided, with sequential numeric keys
	 */
	public static function parseEnumList(mixed $array, string $className, ?array $allowedValues=null, ?callable $parseFunction=null, ?string $separatorIfString=null): array
	{
		if (null !== $parseFunction) {
			$enumList = \Osimatic\ArrayList\Arr::parseEnumListFromCallable(self::parseArray($array, separatorIfString: $separatorIfString), $parseFunction);
		}
		else {
			$enumList = \Osimatic\ArrayList\Arr::parseEnumList(self::parseArray($array, separatorIfString: $separatorIfString), $className);
		}

		if (null !== $allowedValues) {
			$enumList = array_values(array_filter($enumList, static fn($value) => in_array($value, $allowedValues, true)));
		}
		return $enumList;
	}

	/**
	 * Populates the Symfony Request object with parsed request body data for HTTP methods that do not automatically populate request parameters (PATCH, UPDATE, DELETE). This is necessary because Symfony's Request class only automatically parses POST data, while PATCH, UPDATE, and DELETE request bodies need to be manually extracted and added to the request parameter bag.
	 * @param Request $request The Symfony Request object to populate with parsed data
	 * @return void
	 */
	public static function setFormData(Request $request): void
	{
		// Workaround because data from PUT, PATCH, or DELETE methods is not automatically in the Request object
		$method = $request->getMethod();
		if (in_array($method, [HTTPMethod::PATCH->value, HTTPMethod::PUT->value, HTTPMethod::DELETE->value], true)) {
			$_PATCH = \Osimatic\Network\HTTPRequest::parseRawHttpRequestData();
			$request->request->add($_PATCH);
		}
	}

	/**
	 * Retrieves and merges form data from GET, POST, and raw HTTP request body (for methods like PUT, PATCH, DELETE). This method is deprecated and should be replaced with proper Request object usage in Symfony applications.
	 * @deprecated Use setFormData() with Symfony Request object instead for better framework integration
	 * @return array Merged array containing all form data from GET parameters, POST parameters, and parsed raw request body
	 */
	public static function getFormData(): array
	{
		$_PATCH = \Osimatic\Network\HTTPRequest::parseRawHttpRequestData();
		return array_merge($_GET, $_POST, $_PATCH);
	}

	/**
	 * Extracts and formats error messages from Symfony validation constraint violations and custom error arrays, with support for translation and flexible output formatting.
	 * Entity errors have their property paths converted to snake_case, and both error sources can be optionally translated using the provided translator.
	 * The method returns an associative array where keys are property paths (or custom error keys) and values are either just the error messages or tuples of error key and message depending on the returnErrorMessageOnly parameter.
	 * Example:
	 * FormService::getErrorMessages($violations, ['email' => 'Email is required'])
	 * FormService::getErrorMessages($violations, ['email' => ['error.email.required', ['%min%' => 5]]], true, false, $translator)
	 * @param ConstraintViolationListInterface|null $entityErrors Symfony validation constraint violations from entity validation, or null if no entity errors
	 * @param array|null $otherErrors Additional custom errors as an associative array where keys are error identifiers and values are either error messages (string) or arrays with [errorKey, errorMessage] or [errorKey, parameters] for translation (default: null)
	 * @param bool $translateMessages Whether to translate error messages using the translator (default: true)
	 * @param bool $returnErrorMessageOnly Whether to return only error messages as values (true) or return [errorKey, errorMessage] tuples (false, default: true)
	 * @param TranslatorInterface|null $translator The Symfony translator instance for translating error messages (required if translateMessages is true, default: null)
	 * @param string|null $translatorDomain The translation domain to use for error message translation (default: 'validators')
	 * @return string[] Associative array of error messages indexed by property path or error key
	 */
	public static function getErrorMessages(?ConstraintViolationListInterface $entityErrors, ?array $otherErrors=null, bool $translateMessages=true, bool $returnErrorMessageOnly=true, ?TranslatorInterface $translator=null, ?string $translatorDomain='validators'): array
	{
		$errorMessages = [];

		if (null !== $entityErrors) {
			$errorMessages = self::processEntityErrors($entityErrors, $translateMessages);
		}

		if (null !== $otherErrors) {
			$errorMessages = array_merge($errorMessages, self::processOtherErrors($otherErrors, $translateMessages, $returnErrorMessageOnly, $translator, $translatorDomain));
		}

		return $errorMessages;
	}

	/**
	 * Processes entity validation errors and converts property paths to snake_case.
	 * Fixes potential bug where strpos() returns false if no dot is found in property path.
	 * @param ConstraintViolationListInterface $entityErrors The validation constraint violations
	 * @param bool $translateMessages Whether to return translated messages or message templates
	 * @return array Associative array of error messages indexed by snake_case property path
	 */
	private static function processEntityErrors(ConstraintViolationListInterface $entityErrors, bool $translateMessages): array
	{
		$errorMessages = [];

		foreach ($entityErrors as $error) {
			$propertyPath = $error->getPropertyPath();
			$dotPosition = strpos($propertyPath, '.');

			if (false !== $dotPosition) {
				$beforeDot = substr($propertyPath, 0, $dotPosition);
				$afterDot = substr($propertyPath, $dotPosition);
				$propertyPath = \Osimatic\Text\Str::toSnakeCase($beforeDot) . $afterDot;
			} else {
				$propertyPath = \Osimatic\Text\Str::toSnakeCase($propertyPath);
			}

			$errorMessages[$propertyPath] = $translateMessages ? $error->getMessage() : $error->getMessageTemplate();
		}

		return $errorMessages;
	}

	/**
	 * Processes custom errors with optional translation support.
	 * @param array $otherErrors Array of custom errors
	 * @param bool $translateMessages Whether to translate error messages
	 * @param bool $returnErrorMessageOnly Whether to return only messages or [key, message] tuples
	 * @param TranslatorInterface|null $translator The translator instance
	 * @param string|null $translatorDomain The translation domain
	 * @return array Associative array of processed error messages
	 */
	private static function processOtherErrors(array $otherErrors, bool $translateMessages, bool $returnErrorMessageOnly, ?TranslatorInterface $translator, ?string $translatorDomain): array
	{
		$errorMessages = [];

		foreach ($otherErrors as $key => $error) {
			$errorKey = is_array($error) ? $error[0] ?? null : $error;
			$errorMessage = is_array($error) ? $error[1] ?? null : $error;

			if ($translateMessages && null !== $translator) {
				$parameters = is_array($error) && isset($error[1]) && is_array($error[1]) ? $error[1] : [];
				$errorMessage = $translator->trans($errorKey, $parameters, $translatorDomain);
			}

			$errorMessages[$key] = $returnErrorMessageOnly ? $errorMessage : [$errorKey, $errorMessage];
		}

		return $errorMessages;
	}
}