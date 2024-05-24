<?php

namespace Osimatic\Person;

/**
 * Cette classe contient des fonctions relatives au fichier VCard.
 * @package Osimatic\Helpers\Person
 * @author Benoit Guiraudou <guiraudou@osimatic.com>
 * @link https://en.wikipedia.org/wiki/VCard
 */
class VCard
{
	public const FILE_EXTENSION = '.vcf';
	public const LN = "\r\n";

	/**
	 * Properties
	 * @var array
	 */
	private array $properties = [];

	/**
	 * Default Charset
	 * @var string
	 */
	public string $charset = 'utf-8';

	/**
	 * definedElements
	 * @var array
	 */
	private array $definedElements = [];

	/**
	 * Filename
	 * @var string|null
	 */
	private ?string $filename = null;

	/**
	 * Multiple properties for element allowed
	 * @var array
	 */
	private static array $multiplePropertiesForElementAllowed = [
		'email',
		'address',
		'phoneNumber',
		'url'
	];

	/**
	 * @param PersonInterface $person
	 * @return self
	 */
	public function setFromPerson(PersonInterface $person): self
	{
		$this->addName($person->getFamilyName() ?? '', $person->getGivenName() ?? '');
		if (null !== $person->getBirthDate()) {
			$this->addBirthday($person->getBirthDate());
		}
		if (null !== ($postalAddress = $person->getAddress())) {
			$this->addAddress(
				'',
				'',
				$postalAddress->getRoad() ?? '',
				$postalAddress->getCity() ?? '',
				$postalAddress->getState() ?? '',
				$postalAddress->getPostcode() ?? '',
				$postalAddress->getCountry() ?? ''
			);
		}
		$this->addEmail($person->getEmail() ?? '');
		if (null !== $person->getFixedLineNumber()) {
			$this->addPhoneNumber($person->getFixedLineNumber(), 'HOME');
		}
		if (null !== $person->getMobileNumber()) {
			$this->addPhoneNumber($person->getMobileNumber(), 'CELL');
		}
		if (null !== ($organization = $person->getWorksFor())) {
			$this->addCompany($organization->getName());
		}

		return $this;
	}

	/**
	 * Add name
	 * @param string $lastName [optional]
	 * @param string $firstName [optional]
	 * @param string $additional [optional]
	 * @param string $prefix [optional]
	 * @param string $suffix [optional]
	 * @return self
	 */
	public function addName(
		string $lastName = '',
		string $firstName = '',
		string $additional = '',
		string $prefix = '',
		string $suffix = ''
	): self
	{
		// define values with non-empty values
		$values = array_filter([$prefix, $firstName, $additional, $lastName, $suffix]);
		// define filename
		$this->setFilename($values);
		// set property
		$property = $lastName . ';' . $firstName . ';' . $additional . ';' . $prefix . ';' . $suffix;
		$this->setProperty(
			'name',
			'N' . $this->getCharsetString(),
			$property
		);
		// is property FN set?
		if (!$this->hasProperty('FN')) {
			// set property
			$this->setProperty(
				'fullname',
				'FN' . $this->getCharsetString(),
				trim(implode(' ', $values))
			);
		}
		return $this;
	}

	/**
	 * Add nickname
	 * @param string $nickname
	 * @return self
	 */
	public function addNickname(string $nickname): self
	{
		$this->setProperty(
			'nickname',
			'NICKNAME',
			$nickname
		);
		return $this;
	}

	/**
	 * Add birthday
	 * @param \DateTime $date
	 * @return self
	 */
	public function addBirthday(\DateTime $date): self
	{
		$this->setProperty(
			'birthday',
			'BDAY',
			$date->format('Y-m-d')
		);
		return $this;
	}

	/**
	 * Add address
	 * @param string $name [optional]
	 * @param string $extended [optional]
	 * @param string $street [optional]
	 * @param string $city [optional]
	 * @param string $region [optional]
	 * @param string $zip [optional]
	 * @param string $country [optional]
	 * @param string $type $type may be DOM | INTL | POSTAL | PARCEL | HOME | WORK or any combination of these: e.g. "WORK,PARCEL,POSTAL" [optional]
	 * @return self
	 */
	public function addAddress(
		string $name = '',
		string $extended = '',
		string $street = '',
		string $city = '',
		string $region = '',
		string $zip = '',
		string $country = '',
		string $type = 'HOME'
	): self
	{
		// init value
		$value = $name . ';' . $extended . ';' . $street . ';' . $city . ';' . $region . ';' . $zip . ';' . $country;
		// set property
		$this->setProperty(
			'address',
			'ADR' . (($type !== '') ? ';TYPE=' . $type : '') . $this->getCharsetString(),
			$value
		);
		return $this;
	}

	/**
	 * Add a location (latitude and longitude)
	 * @param string $coordinates latitude and longitude
	 * @return self
	 */
	public function addLocation(string $coordinates): self
	{
		$this->setProperty(
			'coordinates',
			'GEO',
			$coordinates
		);
		return $this;
	}

	/**
	 * Add email
	 * @param string $email The e-mail address
	 * @param string $type The type of the email address. $type may be PREF | WORK | HOME | INTERNET or any combination of these: e.g. "PREF,WORK" [optional]
	 * @return self
	 */
	public function addEmail(string $email, string $type = ''): self
	{
		$this->setProperty(
			'email',
			'EMAIL' . (($type !== '') ? ';TYPE=' . $type : ''),
			$email
		);
		return $this;
	}

	/**
	 * Add phone number
	 * @param string $number
	 * @param string $type Type may be PREF | WORK | HOME | VOICE | FAX | MSG | CELL | PAGER | BBS | CAR | MODEM | ISDN | VIDEO or any senseful combination, e.g. "PREF,WORK,VOICE" [optional]
	 * @return self
	 */
	public function addPhoneNumber(string $number, string $type = ''): self
	{
		$this->setProperty(
			'phoneNumber',
			'TEL' . (($type !== '') ? ';TYPE=' . $type : ''),
			$number
		);
		return $this;
	}

	/**
	 * Add company
	 * @param string $organizationName
	 * @param string $organizationUnits [optional]
	 * @return self
	 */
	public function addCompany(string $organizationName, string $organizationUnits=''): self
	{
		$values = array_filter([$organizationName, $organizationUnits]);

		// if filename is empty, add to filename
		if ($this->filename === null) {
			$this->setFilename($values);
		}

		$this->setProperty(
			'company',
			'ORG' . $this->getCharsetString(),
			$organizationName.';'.$organizationUnits
		);

		// is property FN set?
		if (!$this->hasProperty('FN')) {
			// set property
			$this->setProperty(
				'fullname',
				'FN' . $this->getCharsetString(),
				trim(implode(' ', $values))
			);
		}

		return $this;
	}

	/**
	 * Add role or occupation or business category
	 * @param string $role The role or occupation or business category for the person.
	 * @return self
	 */
	public function addRole(string $role): self
	{
		$this->setProperty(
			'role',
			'ROLE' . $this->getCharsetString(),
			$role
		);
		return $this;
	}

	/**
	 * Add job title or functional position or function
	 * @param string $jobTitle The job title or functional position or function for the person.
	 * @return self
	 */
	public function addJobTitle(string $jobTitle): self
	{
		$this->setProperty(
			'jobtitle',
			'TITLE' . $this->getCharsetString(),
			$jobTitle
		);
		return $this;
	}

	/**
	 * Add URL
	 * @param string $url
	 * @param string $type Type may be WORK | HOME [optional]
	 * @return self
	 */
	public function addURL(string $url, string $type = ''): self
	{
		$this->setProperty(
			'url',
			'URL' . (($type !== '') ? ';TYPE=' . $type : ''),
			$url
		);
		return $this;
	}

	/**
	 * Add Logo
	 * @param string $url image url or filename
	 * @param bool $include Include the image in our vcard?
	 * @return self
	 */
	public function addLogo(string $url, bool $include = false): self
	{
		$this->addMedia(
			'LOGO',
			$url,
			$include
		);
		return $this;
	}

	/**
	 * Add Photo
	 * @param string $url image url or filename
	 * @param bool $include Include the image in our vcard?
	 * @return self
	 */
	public function addPhoto(string $url, bool $include = false): self
	{
		$this->addMedia(
			'PHOTO',
			$url,
			$include
		);
		return $this;
	}

	/**
	 * Add Sound
	 * @param string $url image url or filename
	 * @param bool $include Include the image in our vcard?
	 * @return self
	 */
	public function addSound(string $url, bool $include = false): self
	{
		$this->addMedia(
			'SOUND',
			$url,
			$include
		);
		return $this;
	}

	/**
	 * Add time zone
	 * @param string $timeZone The time zone of the vCard object.
	 * @return self
	 */
	public function addTimeZone(string $timeZone): self
	{
		$this->setProperty(
			'time_zone',
			'TZ' . $this->getCharsetString(),
			$timeZone
		);
		return $this;
	}

	/**
	 * Add a value that represents a persistent, globally unique identifier associated with the object
	 * @param string $uid
	 * @return self
	 */
	public function addUniqueIdentifier(string $uid): self
	{
		$this->setProperty(
			'uid',
			'UID' . $this->getCharsetString(),
			$uid
		);
		return $this;
	}

	/**
	 * Add a URL that can be used to get the latest version of this vCard.
	 * @param string $source
	 * @return self
	 */
	public function addSource(string $source): self
	{
		$this->setProperty(
			'source',
			'SOURCE' . $this->getCharsetString(),
			$source
		);
		return $this;
	}

	/**
	 * Add lang
	 * @param string $key
	 * @param string $type
	 * @return self
	 */
	public function addPublicKey(string $key, string $type=''): self
	{
		// todo : add encoding
		$this->setProperty(
			'key',
			'KEY' . (($type !== '') ? ';TYPE=' . $type : '') . $this->getCharsetString(),
			$key
		);
		return $this;
	}

	/**
	 * Add lang
	 *
	 * @param string $lang
	 * @return self
	 */
	public function addLang(string $lang): self
	{
		$this->setProperty(
			'lang',
			'LANG' . $this->getCharsetString(),
			$lang
		);
		return $this;
	}

	/**
	 * Add note
	 * @param string $note
	 * @return self
	 */
	public function addNote(string $note): self
	{
		$this->setProperty(
			'note',
			'NOTE' . $this->getCharsetString(),
			$note
		);
		return $this;
	}

	/**
	 * Set charset
	 * @param string $charset
	 * @return void
	 */
	public function setCharset(string $charset): void
	{
		$this->charset = $charset;
	}

	/**
	 * Get charset
	 * @return string
	 */
	public function getCharset(): string
	{
		return $this->charset;
	}

	/**
	 * Save to a file
	 * @param string $path
	 */
	public function build(string $path): void
	{
		\Osimatic\FileSystem\FileSystem::initializeFile($path);

		file_put_contents($path, $this->getContent());
	}

	/**
	 * Download a vcard or vcal file to the browser.
	 * @param string|null $filename
	 */
	public function download(?string $filename=null): void
	{
		$filename = $filename ?? 'vcard'.$this->getFileExtension();

		header('Content-type: text/x-vcard; charset='.$this->getCharset());
		header('Content-Disposition: attachment; filename='.$filename);
		header('Content-Length: '.strlen($this->getContent()));
		header('Connection: close');

		// echo the output and it will be a download
		echo $this->getContent();
	}

	/**
	 * Get output as string
	 * @return string
	 */
	public function getContent(): string
	{
		$props = [];

		// init string
		$props[] = 'BEGIN:VCARD';
		$props[] = 'VERSION:3.0';
		$props[] = 'REV:' . date('Y-m-d') . 'T' . date('H:i:s') . 'Z';
		// loop all properties
		$properties = $this->getProperties();
		foreach ($properties as $property) {
			// add to string
			foreach ($this->fold($property['key'] . ':' . $property['value']) as $prop) {
				$props[] = $prop;
			}
		}
		// add to string
		$props[] = 'END:VCARD';
		// return
		return implode(self::LN, $props);
	}



	/**
	 * Set filename
	 * @param  mixed  $value
	 * @param  bool   $overwrite [optional] Default overwrite is true
	 * @param  string $separator [optional] Default separator is an underscore '_'
	 * @return void
	 */
	public function setFilename(mixed $value, bool $overwrite = true, string $separator = '_'): void
	{
		// recast to string if $value is array
		if (is_array($value)) {
			$value = implode($separator, $value);
		}
		// trim unneeded values
		$value = trim($value, $separator);
		// remove all spaces
		$value = preg_replace('/\s+/', $separator, $value);
		// if value is empty, stop here
		if (empty($value)) {
			return;
		}
		// urlize this part
		$value = \Osimatic\Text\Str::toURLFriendly($value);
		// overwrite filename or add to filename using a prefix in between
		$this->filename = ($overwrite) ? $value : $this->filename . $separator . $value;
	}

	/**
	 * Get file extension
	 * @return string
	 */
	public function getFileExtension(): string
	{
		return self::FILE_EXTENSION;
	}

	/**
	 * @param string $filename
	 * @return array|null
	 */
	public function parseFromFile(string $filename): ?array
	{
		if (!file_exists($filename) || !is_readable($filename)) {
			return null;
		}
		return $this->parse(file_get_contents($filename));
	}

	/**
	 * Start the parsing process.
	 * This method will populate the data object.
	 * @param string $content
	 * @return array|null
	 */
	public function parse(string $content): ?array
	{
		$vcardObjects = array();

		// Normalize new lines.
		$content = str_replace(array("\r\n", "\r"), "\n", $content);
		// RFC2425 5.8.1. Line delimiting and folding
		// Unfolding is accomplished by regarding CRLF immediately followed by
		// a white space character (namely HTAB ASCII decimal 9 or. SPACE ASCII
		// decimal 32) as equivalent to no characters at all (i.e., the CRLF
		// and single white space character are removed).
		$content = preg_replace("/\n(?:[ \t])/", "", $content);
		$lines = explode("\n", $content);
		// Parse the VCard, line by line.
		$cardData = null;
		foreach ($lines as $line) {
			$line = trim($line);
			if (mb_strtoupper($line) === 'BEGIN:VCARD') {
				$cardData = new \stdClass();
			} elseif (mb_strtoupper($line) === 'END:VCARD') {
				$vcardObjects[] = $cardData;
			} elseif (!empty($line)) {
				$type = '';
				$value = '';
				@list($type, $value) = explode(':', $line, 2);
				$types = explode(';', $type);
				$element = mb_strtoupper($types[0]);
				array_shift($types);
				$i = 0;
				$rawValue = false;
				foreach ($types as $type) {
					if (false !== stripos(mb_strtolower($type), 'base64')) {
						$value = base64_decode($value);
						unset($types[$i]);
						$rawValue = true;
					} elseif (preg_match('/encoding=b/i', $type)) {
						$value = base64_decode($value);
						unset($types[$i]);
						$rawValue = true;
					} elseif (false !== stripos($type, 'quoted-printable')) {
						$value = quoted_printable_decode($value);
						unset($types[$i]);
						$rawValue = true;
					} elseif (stripos($type, 'charset=') === 0) {
						try {
							$value = mb_convert_encoding($value, 'UTF-8', substr($type, 8));
						} catch (\Exception $e) { }
						unset($types[$i]);
					}
					$i++;
				}
				switch (mb_strtoupper($element)) {
					case 'FN':
						$cardData['fullname'] = $value;
						break;
					case 'N':
						foreach($this->parseName($value) as $key => $val) {
							$cardData->{$key} = $val;
						}
						break;
					case 'BDAY':
						$cardData['birthday'] = $this->parseBirthday($value) ?? null;
						break;
					case 'ADR':
						if (!isset($cardData['address'])) {
							$cardData['address'] = [];
						}
						$key = !empty($types) ? implode(';', $types) : 'WORK;POSTAL';
						$cardData['address'][$key][] = $this->parseAddress($value);
						break;
					case 'TEL':
						if (!isset($cardData['phone'])) {
							$cardData['phone'] = [];
						}
						$key = !empty($types) ? implode(';', $types) : 'default';
						$cardData['phone'][$key][] = $value;
						break;
					case 'EMAIL':
						if (!isset($cardData['email'])) {
							$cardData['email'] = [];
						}
						$key = !empty($types) ? implode(';', $types) : 'default';
						$cardData['email'][$key][] = $value;
						break;
					case 'REV':
						$cardData['revision'] = $value;
						break;
					case 'VERSION':
						$cardData['version'] = $value;
						break;
					case 'ORG':
						$cardData['organization'] = $value;
						break;
					case 'URL':
						if (!isset($cardData['url'])) {
							$cardData['url'] = [];
						}
						$key = !empty($types) ? implode(';', $types) : 'default';
						$cardData['url'][$key][] = $value;
						break;
					case 'TITLE':
						$cardData['title'] = $value;
						break;
					case 'PHOTO':
						if ($rawValue) {
							$cardData['rawPhoto'] = $value;
						} else {
							$cardData['photo'] = $value;
						}
						break;
				}
			}
		}
		return $vcardObjects;
	}

	// ---------- private ----------

	/**
	 * Add a photo or logo (depending on property name)
	 * @param string $property LOGO|PHOTO|SOUND
	 * @param string $url image url or filename
	 * @param bool $include Do we include the image in our vcard or not?
	 * @return bool
	 */
	private function addMedia(string $property, string $url, bool $include = true): bool
	{
		if ($include) {
			$value = file_get_contents($url);
			if (!$value) {
				//trace('Nothing returned from URL.');
				return false;
			}

			$value = base64_encode($value);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimetype = finfo_file($finfo, 'data://application/octet-stream;base64,' . $value);
			finfo_close($finfo);

			if (preg_match('/^image\//', $mimetype) === 1) {
				$type = mb_strtoupper(str_replace('image/', '', $mimetype));
			}
			// todo : check sound
			elseif (preg_match('/^image\//', $mimetype) === 1) {
				$type = mb_strtoupper(str_replace('image/', '', $mimetype));
			}
			else {
				//trace('Returned data is with an unknown format.');
				return false;
			}

			$property .= ';ENCODING=b;TYPE=' . $type;
		}
		else {
			if (filter_var($url, FILTER_VALIDATE_URL) !== FALSE) {
				$headers = get_headers($url);
				$typeMatched = false;
				$fileType = null;
				foreach ($headers as $header) {
					if (preg_match('/Content-Type:\simage\/([a-z]+)/i', $header, $m)) {
						$fileType = $m[1];
						$typeMatched = true;
						break;
					}
					// todo : check sound
					if (preg_match('/Content-Type:\simage\/([a-z]+)/i', $header, $m)) {
						$fileType = $m[1];
						$typeMatched = true;
						break;
					}
				}
				if (!$typeMatched) {
					//trace('Returned data is with an unknown format.');
					return false;
				}

				$property .= ';VALUE=URL;TYPE=' . mb_strtoupper($fileType);
				$value = $url;
			}
			else {
				$value = $url;
			}
		}
		$this->setProperty(
			mb_strtolower($property),
			$property,
			$value
		);
		return true;
	}

	/**
	 * Fold a line according to RFC2425 section 5.8.1.
	 * @link http://tools.ietf.org/html/rfc2425#section-5.8.1
	 * @param  string $text
	 * @return array
	 */
	private function fold(string $text): array
	{
		//if (strlen($text) <= 75) {
		//	return $text;
		//}
		// split, wrap and trim trailing separator
		//return substr(chunk_split($text, 73, "\r\n "), 0, -3);
		return str_split($text, 75 - strlen(self::LN));
	}

	/**
	 * Get charset string
	 * @return string
	 */
	private function getCharsetString(): string
	{
		$charsetString = '';
		if ($this->charset === 'utf-8') {
			$charsetString = ';CHARSET=' . $this->charset;
		}
		return $charsetString;
	}

	/**
	 * Get properties
	 * @return array
	 */
	private function getProperties(): array
	{
		return $this->properties;
	}

	/**
	 * Has property
	 * @param  string $key
	 * @return bool
	 */
	private function hasProperty(string $key): bool
	{
		$properties = $this->getProperties();
		foreach ($properties as $property) {
			if ($property['key'] === $key && $property['value'] !== '') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Set property
	 *
	 * @param  string $element The element name you want to set, f.e.: name, email, phoneNumber,…
	 * @param  string $key
	 * @param  string $value
	 * @return void
	 */
	private function setProperty(string $element, string $key, string $value): void
	{
		if (isset($this->definedElements[$element]) && !in_array($element, self::$multiplePropertiesForElementAllowed, true)) {
			return;
		}
		// we define that we set this element
		$this->definedElements[$element] = true;
		// adding property
		$this->properties[] = [
			'key' => $key,
			'value' => $value
		];
	}

	/**
	 * @param string $value
	 * @return object
	 */
	private function parseName(string $value): object
	{
		[
			$lastname,
			$firstname,
			$additional,
			$prefix,
			$suffix
		] = explode(';', $value);
		return (object) [
			'lastname' => $lastname,
			'firstname' => $firstname,
			'additional' => $additional,
			'prefix' => $prefix,
			'suffix' => $suffix,
		];
	}

	private function parseBirthday($value): ?\DateTime
	{
		try {
			return new \DateTime($value);
		}
		catch (\Exception $e) { }
		return null;
	}

	/**
	 * @param string $value
	 * @return object
	 */
	private function parseAddress(string $value): object
	{
		[
			$name,
			$extended,
			$street,
			$city,
			$region,
			$zip,
			$country,
		] = explode(';', $value);
		return (object) [
			'name' => $name,
			'extended' => $extended,
			'street' => $street,
			'city' => $city,
			'region' => $region,
			'zip' => $zip,
			'country' => $country,
		];
	}

}