<?php

namespace Osimatic\Data;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputInterface;

class Input
{
	/**
	 * Récupération des valeurs selon le contexte (HTTP ou CLI)
	 * @param Request|InputInterface $input
	 * @param string $key
	 * @return mixed|null
	 */
	public static function get(Request|InputInterface $input, string $key): mixed
	{
		if ($input instanceof Request) {
			return $input->get($key);
		}

		// CLI : option > argument (au choix)
		if ($input->hasOption($key)) {
			$v = $input->getOption($key);
			if ($v !== null && $v !== '') {
				return $v;
			}
		}
		if ($input->hasArgument($key)) {
			return $input->getArgument($key);
		}

		return null;
	}

	public static function getBool(Request|InputInterface $input, string $key, bool $default=false): bool
	{
		return self::toBool(self::get($input, $key, $default));
	}

	private static function toBool(mixed $value, bool $default=false): bool
	{
		if ($value === null) {
			return $default;
		}
		if (\is_bool($value)) {
			return $value;
		}
		$v = strtolower((string) $value);
		if ($v === '1' || $v === 'true' || $v === 'yes' || $v === 'on') {
			return true;
		}
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}
		return $default;
	}
}