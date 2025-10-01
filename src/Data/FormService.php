<?php

namespace Osimatic\Data;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormService
{
	/**
	 * @param string|null $value
	 * @param bool $deleteZero
	 * @return string|null
	 */
	public static function trim(?string $value, bool $deleteZero=true): ?string
	{
		if (null === $value) {
			return null;
		}

		if (!$deleteZero) {
			return trim($value, " \n\r\t\v");
		}

		return trim($value);
	}

	/**
	 * @param mixed $array
	 * @param bool $filterEmptyValues
	 * @param string|null $separatorIfString
	 * @return array
	 */
	public static function parseArray(mixed $array, bool $filterEmptyValues=true, ?string $separatorIfString=null): array
	{
		if (null === $array) {
			return [];
		}
		if (!is_array($array)) {
			$array = null !== $separatorIfString && is_string($array) ? explode($separatorIfString, $array) : [$array];
		}
		return $filterEmptyValues ? array_values(array_filter($array)) : $array;
	}

	/**
	 * @param string $enum
	 * @param string|null $value
	 * @param int $caseType CASE_LOWER or CASE_UPPER
	 * @return \BackedEnum|null
	 */
	public static function parseEnum(string $enum, ?string $value, int $caseType=CASE_UPPER): ?\BackedEnum
	{
		if (null === $value) {
			return null;
		}

		return $enum::tryFrom(CASE_LOWER === $caseType ? mb_strtolower($value) : mb_strtoupper($value)); // @phpstan-ignore
	}

	/**
	 * @param mixed $array
	 * @param string $className
	 * @param array|null $allowedValues
	 * @param callable|null $parseFunction
	 * @param string|null $separatorIfString
	 * @return array
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
			foreach ($enumList as $key => $value) {
				if (!in_array($value, $allowedValues, true)) {
					unset($enumList[$key]);
				}
			}
			$enumList = array_values($enumList);
		}
		return $enumList;
	}

	public static function setFormData(Request $request): void
	{
		// hack car les données en PUT ou PATCH ne sont pas dans l'object Request
		if ('PATCH' === $request->getMethod() || 'PUT' === $request->getMethod() || 'DELETE' === $request->getMethod()) {
			$_PATCH = \Osimatic\Network\HTTPRequest::parseRawHttpRequestData();
			$request->request->add($_PATCH);
		}
	}

	/**
	 * @deprecated
	 * @return array
	 */
	public static function getFormData(): array
	{
		$_PATCH = \Osimatic\Network\HTTPRequest::parseRawHttpRequestData();
		return array_merge($_GET, $_POST, $_PATCH);
	}

	/**
	 * @param ConstraintViolationListInterface|null $entityErrors
	 * @param array|null $otherErrors
	 * @param bool $translateMessages
	 * @param bool $returnErrorMessageOnly
	 * @param TranslatorInterface|null $translator
	 * @param string|null $translatorDomain
	 * @return string[]
	 */
	public static function getErrorMessages(?ConstraintViolationListInterface $entityErrors, ?array $otherErrors=null, bool $translateMessages=true, bool $returnErrorMessageOnly=true, ?TranslatorInterface $translator=null, ?string $translatorDomain='validators'): array
	{
		$errorMessages = [];

		if (null !== $entityErrors) {
			foreach ($entityErrors as $error) {
				$propertyPath = $error->getPropertyPath();
				$propertyPath = \Osimatic\Text\Str::toSnakeCase(substr($propertyPath, 0, strpos($propertyPath, '.'))).substr($propertyPath, strpos($propertyPath, '.'));
				$errorMessages[$propertyPath] = $translateMessages ? $error->getMessage() : $error->getMessageTemplate();
			}
		}

		if (null !== $otherErrors) {
			foreach ($otherErrors as $key => $error) {
				$errorKey = is_array($error) ? $error[0] ?? null : $error;
				$errorMessage = is_array($error) ? $error[1] ?? null : $error;

				if ($translateMessages && null !== $translator) {
					$parameters = is_array($error) ? $error[1] ?? [] : [];
					$errorMessage = $translator->trans($errorKey, $parameters, $translatorDomain);
				}

				$errorMessages[$key] = $returnErrorMessageOnly ? $errorMessage : [$errorKey, $errorMessage];
			}
		}

		return $errorMessages;
	}
}