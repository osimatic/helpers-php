<?php

namespace Osimatic\Location;

use Symfony\Component\Yaml\Yaml;

/**
 * Format an address based on the country template.
 *
 * Takes advantage of the OpenCageData address-formatting templates available at:
 * @link https://github.com/OpenCageData/address-formatting
 *
 * Currently using address templates version:
 * @link https://github.com/OpenCageData/address-formatting/commit/2cecd583ec6563c5d1372d5e16db9cbe4c26aa25
 *
 * Also based on the Perl address formatter using the same address templates:
 * @link https://metacpan.org/pod/Geo::Address::Formatter
 */
class PostalAddressFormatter
{
	private ?string $separator = null;
	private array $components = [];
	private array $componentAliases = [];
	private array $templates = [];
	private array $stateCodes = [];
	private array $countries = [];
	private array $validReplacementComponents = [
		'state',
	];

	public function __construct(?string $separator="\n")
	{
		$this->setSeparator($separator);

		try {
			$this->loadTemplates();
		} catch (\Exception) {}
	}

	/**
	 * @param string|null $separator
	 * @return self Returns this instance for method chaining
	 */
	public function setSeparator(?string $separator=null): self
	{
		if (null !== $separator) {
			$this->separator = $separator;
		}

		return $this;
	}

	/**
	 * Pass a PredictHQ\Address\Address object here
	 * @param PostalAddressInterface $address
	 * @param array $options
	 * @param string|null $separator
	 * @param bool $withAttention
	 * @return string
	 */
	public function format(PostalAddressInterface $address, array $options = [], ?string $separator=null, bool $withAttention=true): string
	{
		$this->setSeparator($separator);
		if (null === $this->separator) {
			$this->separator = "\n";
		}

		$addressArray = [];

		if ($withAttention && !empty($address->getAttention())) {
			$addressArray['attention'] = $address->getAttention();
		}
		if (!empty($address->getHouseNumber())) {
			$addressArray['house_number'] = $address->getHouseNumber();
		}
		if (!empty($address->getHouse())) {
			$addressArray['house'] = $address->getHouse();
		}
		if (!empty($address->getRoad())) {
			$addressArray['road'] = $address->getRoad();
		}
		if (!empty($address->getVillage())) {
			$addressArray['village'] = $address->getVillage();
		}
		if (!empty($address->getSuburb())) {
			$addressArray['suburb'] = $address->getSuburb();
		}
		if (!empty($address->getCity())) {
			$addressArray['city'] = $address->getCity();
		}
		if (!empty($address->getCounty())) {
			$addressArray['county'] = $address->getCounty();
		}
		if (!empty($address->getPostcode())) {
			$addressArray['postcode'] = $address->getPostcode();
		}
		if (!empty($address->getStateDistrict())) {
			$addressArray['state_district'] = $address->getStateDistrict();
		}
		if (!empty($address->getState())) {
			$addressArray['state'] = $address->getState();
		}
		if (!empty($address->getRegion())) {
			$addressArray['region'] = $address->getRegion();
		}
		if (!empty($address->getIsland())) {
			$addressArray['island'] = $address->getIsland();
		}
		if (!empty($address->getCountry())) {
			$addressArray['country'] = $address->getCountry();
		}
		if (!empty($address->getCountryCode())) {
			$addressArray['country_code'] = $address->getCountryCode();
		}
		if (!empty($address->getContinent())) {
			$addressArray['continent'] = $address->getContinent();
		}

		$formattedAddress = $this->formatArray($addressArray, $options);

		//$formattedAddress = str_replace("\r\n", "\n", $formattedAddress);
		return implode($this->separator, array_filter(explode("\n", $formattedAddress)));
	}

	public function formatArray(array $addressArray, array $options = []): string
	{
		$countryCode = $this->determineCountryCode($addressArray);

		//Set the alias values (unless it would override something)
		foreach ($this->componentAliases as $key => $val) {
			if (isset($addressArray[$key]) && !isset($addressArray[$val])) {
				$addressArray[$val] = $addressArray[$key];
			}
		}

		//Do a quick and dirty sanity check
		$addressArray = $this->sanityCleanAddress($addressArray);

		//Figure out which template to use
		$tpl = (isset($this->templates[strtoupper($countryCode)])) ? $this->templates[strtoupper($countryCode)] : $this->templates['default'];
		$tplText = (isset($tpl['address_template'])) ? $tpl['address_template'] : '';

		//Do we have the minimum components for an address, or should we use the fallback template?
		if (!$this->hasMinimumAddressComponents($addressArray)) {
			if (isset($tpl['fallback_template'])) {
				$tplText = $tpl['fallback_template'];
			} elseif (isset($this->templates['default']['fallback_template'])) {
				$tplText = $this->templates['default']['fallback_template'];
			}
		}

		//Cleanup the components
		$addressArray = $this->fixCountry($addressArray);

		if (isset($tpl['replace'])) {
			$addressArray = $this->applyReplacements($addressArray, $tpl['replace']);
		}

		$addressArray = $this->addStateCode($addressArray);

		//Add attention, but only if needed
		$unknownComponents = $this->findUnknownComponents($addressArray);

		if (count($unknownComponents) > 0) {
			$addressArray['attention'] = implode(', ', $unknownComponents);
		}

		//Render the template
		$text = $this->render($tplText, $addressArray);

		//Post render cleanup
		if (isset($tpl['postformat_replace'])) {
			$text = $this->postFormatReplace($text, $tpl['postformat_replace']);

			//Run through cleanup again now that we've done replacements etc
			$text = $this->cleanupRendered($text);
		}

		return $text;
	}

	private function findUnknownComponents(array $addressArray): array
	{
		$unknown = [];

		foreach ($addressArray as $key => $val) {
			if (!array_key_exists($key, $this->components) && !array_key_exists($key, $this->componentAliases)) {
				$unknown[] = $val;
			}
		}

		return $unknown;
	}

	private function postFormatReplace(string $text, $replacements): string
	{
		//Remove duplicates
		$beforePieces = explode(', ', $text);
		$seen = [];
		$afterPieces = [];

		foreach ($beforePieces as $piece) {
			$piece = ltrim($piece);

			if (!isset($seen[$piece])) {
				$seen[$piece] = 0;
			}

			$seen[$piece]++;

			if ($seen[$piece] > 1) {
				continue;
			}

			$afterPieces[] = $piece;
		}

		$text = implode(', ', $afterPieces);

		//Do any country-specific rules
		foreach ($replacements as $replacement) {
			$text = preg_replace('/'.$replacement[0].'/', $replacement[1], $text);
		}

		return $text;
	}

	private function render(string $tplText, array $addressArray): string
	{
		$m = new \Mustache_Engine;

		$context = $addressArray;
		$context['first'] = function($text) use (&$m, &$addressArray) {
			$newText = $m->render($text, $addressArray);
			$matched = preg_split("/\s*\|\|\s*/", $newText);
			$first = current(array_filter($matched));

			return $first;
		};

		$text = $m->render($tplText, $context);

		//Cleanup the output
		$text = $this->cleanupRendered($text);

		//Make sure we have at least something
		if (preg_match('/\w/u', $text) == 0) {
			$backupParts = [];

			foreach ($addressArray as $key => $val) {
				if (strlen($val) > 0) {
					$backupParts[] = $val;
				}
			}

			$text = implode(', ', $backupParts);

			//Cleanup the output again
			$text = $this->cleanupRendered($text);
		}

		return $text;
	}

	private function cleanupRendered(string $text): string
	{
		$replacements = [
			'/[\},\s]+$/u' => '',
			'/^[,\s]+/u' => '',
			'/,\s*,/u' => ', ', //multiple commas to one
			'/\h+,\h+/u' => ', ', //one horiz whitespace behind comma
			'/\h\h+/u' => ' ', //multiple horiz whitespace to one
			"/\h\n/u" => "\n", //horiz whitespace, newline to newline
			"/\n,/u" => "\n", //newline comma to just newline
			'/,,+/u' => ',', //multiple commas to one
			"/,\n/u" => "\n", //comma newline to just newline
			"/\n\h+/u" => "\n", //newline plus space to newline
			"/\n\n+/u" => "\n", //multiple newline to one
		];

		foreach ($replacements as $key => $val) {
			$text = preg_replace($key, $val, $text);
		}

		//Final dedupe across and within lines
		$beforeLines = explode("\n", $text);
		$seenLines = [];
		$afterLines = [];

		foreach ($beforeLines as $line) {
			$line = preg_replace('/^\h+/', '', $line);
			$line = preg_replace('/\h+$/', '', $line);

			if (!isset($seenLines[$line])) {
				$seenLines[$line] = 0;
			}

			$seenLines[$line]++;

			if ($seenLines[$line] > 1) {
				//Don't repeat this line
				continue;
			}

			//Now dedupe within the line
			$beforeWords = explode(', ', $line);
			$seenWords = [];
			$afterWords = [];

			foreach ($beforeWords as $word) {
				$word = preg_replace('/^\h+/', '', $word);
				$word = preg_replace('/\h+$/', '', $word);

				if (!isset($seenWords[$word])) {
					$seenWords[$word] = 0;
				}

				$seenWords[$word]++;

				if ($seenWords[$word] > 1) {
					//Don't repeat this word
					continue;
				}

				$afterWords[] = $word;
			}

			$line = implode(', ', $afterWords);
			$afterLines[] = $line;
		}

		$text = implode("\n", $afterLines);

		$text = ltrim($text); //remove leading whitespace
		$text = rtrim($text); //remove end whitespace

		$text .= "\n"; //add final newline

		return $text;
	}

	private function fixCountry(array $addressArray): array
	{
		/**
		 * Hacks for bad country data
		 */
		if (isset($addressArray['country'])) {
			if (isset($addressArray['state'])) {
				/**
				 * If the country is a number, use the state as country
				 */
				if (is_numeric($addressArray['country'])) {
					$addressArray['country'] = $addressArray['state'];
					unset($addressArray['state']);
				}
			}
		}

		return $addressArray;
	}

	private function applyReplacements(array $addressArray, array $replacements): array
	{
		foreach ($addressArray as $key => $val) {
			foreach ($replacements as $replacement) {
				if (preg_match('/^'.$key.'=(.+)/', $replacement[0], $matches) > 0) {
					//This is a key-specific replacement (e.g., city=ABC), work out the vaule to replace
					$from = $matches[1];

					if ($from == $val) {
						$addressArray[$key] = $replacement[1];
					}
				} else {
					$addressArray[$key] = preg_replace('/'.$replacement[0].'/', $replacement[1], $addressArray[$key]);
				}
			}
		}

		return $addressArray;
	}

	private function addStateCode(array $addressArray): array
	{
		if (!isset($addressArray['state_code'])) {
			if (isset($addressArray['state']) && isset($addressArray['country_code'])) {
				//Make sure country code is uppercase
				$addressArray['country_code'] = strtoupper($addressArray['country_code']);

				if (isset($this->stateCodes[$addressArray['country_code']])) {
					foreach($this->stateCodes[$addressArray['country_code']] as $key => $val) {
						if (strtoupper($addressArray['state']) === strtoupper($val)) {
							$addressArray['state_code'] = $key;
						}
					}
				}
			}
		}

		return $addressArray;
	}

	private function determineCountryCode(array &$addressArray): string
	{
		$countryCode = (isset($addressArray['country_code'])) ? $addressArray['country_code'] : '';

		//Make sure it is 2 characters
		if (strlen($countryCode) === 2) {
			$countryCode = strtoupper($countryCode);

			if (strtoupper($countryCode) === 'UK') {
				$countryCode = 'GB';
			}

			$addressArray['country'] = Country::getCountryNameFromCountryCode($countryCode);

			/**
			 * Check if the country config tells us to use a different country code.
			 * Used in cases of dependent territories like American Samoa (AS) and Puerto Rico (PR)
			 */
			if (isset($this->templates[$countryCode])) {
				if (isset($this->templates[$countryCode]['use_country'])) {
					$oldCountryCode = $countryCode;
					$countryCode = $this->templates[$countryCode]['use_country'];

					if (isset($this->templates[$oldCountryCode]['change_country'])) {
						$newCountry = $this->templates[$oldCountryCode]['change_country'];

						if (preg_match('/\$(\w*)/', $newCountry, $matches) > 0) {
							$component = $matches[1];

							if (isset($addressArray[$component])) {
								$newCountry = preg_replace('/\$'.$component.'/', $addressArray[$component], $newCountry);
							} else {
								$newCountry = preg_replace('/\$'.$component.'/', '', $newCountry);
							}
						}

						$addressArray['country'] = $newCountry;
					}

					if (isset($this->templates[$oldCountryCode]['add_component']) && str_contains($this->templates[$oldCountryCode]['add_component'], '=')) {
						list($k, $v) = explode('=', $this->templates[$oldCountryCode]['add_component']);

						if (in_array($k, $this->validReplacementComponents, true)) {
							$addressArray[$k] = $v;
						}
					}
				}
			}

			if ($countryCode === 'NL') {
				if (isset($addressArray['state']) && $addressArray['state'] === 'Curaçao') {
					$countryCode = 'CW';
					$addressArray['country'] = 'Curaçao';
				} elseif (isset($addressArray['state']) && preg_match('/^sint maarten/i', $addressArray['state']) > 0) {
					$countryCode = 'SX';
					$addressArray['country'] = 'Sint Maarten';
				} elseif (isset($addressArray['state']) && preg_match('/^Aruba/i', $addressArray['state']) > 0) {
					$countryCode = 'AW';
					$addressArray['country'] = 'Aruba';
				}
			}
		}

		$addressArray['country_code'] = $countryCode;

		return $countryCode;
	}

	private function sanityCleanAddress(array $addressArray): array
	{
		if (isset($addressArray['postcode']) && strlen($addressArray['postcode']) > 20) {
			unset($addressArray['postcode']);
		}

		//Try and catch values containing URLs
		foreach ($addressArray as $key => $val) {
			if (preg_match('|https?://|', $val) > 0) {
				unset($addressArray[$key]);
			}
		}

		return $addressArray;
	}

	private function hasMinimumAddressComponents(array $addressArray): bool
	{
		$missing = 0;
		$minThreshold = 2;
		$requiredComponents = ['road', 'postcode']; //These should probably be provided in the templates or somewhere else other than here!

		foreach ($requiredComponents as $requiredComponent) {
			if (!isset($addressArray[$requiredComponent])) {
				$missing++;
			}

			if ($missing >= $minThreshold) {
				break;
			}
		}

		return $missing < $minThreshold;
	}

	/**
	 * @throws \Exception
	 */
	public function loadTemplates(): void
	{
		$templatesPath = implode(DIRECTORY_SEPARATOR, array(realpath(__DIR__), 'conf'));
		if (!is_dir($templatesPath)) {
			throw new \Exception('Address formatting templates path cannot be found.');
		}

		$countriesPath = implode(DIRECTORY_SEPARATOR, array($templatesPath, 'countries', 'worldwide.yaml'));
		$componentsPath = implode(DIRECTORY_SEPARATOR, array($templatesPath, 'components.yaml'));
		$stateCodesPath = implode(DIRECTORY_SEPARATOR, array($templatesPath, 'state_codes.yaml'));

		$components = [];
		$componentAliases = [];

		// The components file is made up of multiple yaml documents but the symfony yaml parser doesn't support multiple docs in a single file. So we split it into multiple docs.
		$componentYamlParts = explode('---', file_get_contents($componentsPath));

		foreach ($componentYamlParts as $val) {
			$component = Yaml::parse($val);

			if (isset($component['aliases'])) {
				foreach ($component['aliases'] as $v) {
					$componentAliases[$v] = $component['name'];
				}
			}

			$components[$component['name']] = (isset($component['aliases'])) ? $component['aliases'] : [];
		}

		//Load the country templates and state codes
		$templates = Yaml::parse(file_get_contents($countriesPath));
		$stateCodes = Yaml::parse(file_get_contents($stateCodesPath));

		$this->components = $components;
		$this->componentAliases = $componentAliases;
		$this->templates = $templates;
		$this->stateCodes = $stateCodes;
	}

	public function getComponents(): array
	{
		return $this->components;
	}

	public function getCountries(): array
	{
		return $this->countries;
	}

	public function getStateCodes(): array
	{
		return $this->stateCodes;
	}

	/*
	public static function format(Address $address)
	{
		$f = new AddressFormatter();
		return $f->format($address);
	}
	*/
}