<?php

namespace Osimatic\Data;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputInterface;

class Input
{
	/**
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
}