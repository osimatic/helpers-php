<?php

namespace Osimatic\Person;

/**
 * Class VCard
 * This class provides functionality for creating, reading, and manipulating vCard files.
 * vCard is a file format standard for electronic business cards supporting contact information exchange.
 * Supports vCard version 3.0 with properties like names, addresses, phone numbers, emails, and more.
 * @author Benoit Guiraudou <guiraudou@osimatic.com>
 * @link https://en.wikipedia.org/wiki/VCard
 */
class VCard
{
	public const string FILE_EXTENSION = '.vcf';
	public const string LN = "\r\n";

	/**
	 * Array of vCard properties to be included in the output.
	 * @var array
	 */
	private array $properties = [];

	/**
	 * The character encoding for the vCard content.
	 * @var string
	 */
	public string $charset = 'utf-8';

	/**
	 * Tracks which elements have been defined to prevent duplicates (except for allowed multiples).
	 * @var array
	 */
	private array $definedElements = [];

	/**
	 * The filename to use when saving or downloading the vCard.
	 * @var string|null
	 */
	private ?string $filename = null;

	/**
	 * List of elements that can have multiple properties (e.g., multiple email addresses).
	 * @var array
	 */
	private static array $multiplePropertiesForElementAllowed = [
		'email',
		'address',
		'phoneNumber',
		'url'
	];

	/**
	 * Populates the vCard from a PersonInterface object.
	 * Extracts all relevant person data and adds it to the vCard.
	 * @param PersonInterface $person The person object to extract data from
	 * @return self Returns this instance for method chaining
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
	 * Adds the person's name to the vCard.
	 * Sets both the N (structured name) and FN (formatted name) properties.
	 * Automatically sets the filename based on the provided name components.
	 * @param string $lastName The last/family name (optional)
	 * @param string $firstName The first/given name (optional)
	 * @param string $additional Additional/middle names (optional)
	 * @param string $prefix Name prefix (e.g., Mr., Dr.) (optional)
	 * @param string $suffix Name suffix (e.g., Jr., III) (optional)
	 * @return self Returns this instance for method chaining
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
	 * Adds a nickname to the vCard.
	 * @param string $nickname The nickname to add
	 * @return self Returns this instance for method chaining
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
	 * Adds a birthday to the vCard.
	 * The date is formatted as Y-m-d according to vCard specifications.
	 * @param \DateTime $date The birth date
	 * @return self Returns this instance for method chaining
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
	 * Adds a postal address to the vCard.
	 * Supports various address types (DOM, INTL, POSTAL, PARCEL, HOME, WORK).
	 * @param string $name The name or PO Box (optional)
	 * @param string $extended Extended address information (e.g., apartment number) (optional)
	 * @param string $street The street address (optional)
	 * @param string $city The city or locality (optional)
	 * @param string $region The state or region (optional)
	 * @param string $zip The postal/ZIP code (optional)
	 * @param string $country The country (optional)
	 * @param string $type The address type: DOM, INTL, POSTAL, PARCEL, HOME, WORK or combinations (e.g., "WORK,PARCEL,POSTAL") (optional, default: 'HOME')
	 * @return self Returns this instance for method chaining
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
	 * Adds a geographic location (latitude and longitude) to the vCard.
	 * @param string $coordinates The coordinates in latitude,longitude format (e.g., "37.386013,-122.082932")
	 * @return self Returns this instance for method chaining
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
	 * Adds an email address to the vCard.
	 * Multiple email addresses can be added with different types.
	 * @param string $email The email address
	 * @param string $type The email type: PREF, WORK, HOME, INTERNET or combinations (e.g., "PREF,WORK") (optional)
	 * @return self Returns this instance for method chaining
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
	 * Adds a phone number to the vCard.
	 * Multiple phone numbers can be added with different types.
	 * @param string $number The phone number
	 * @param string $type The phone type: PREF, WORK, HOME, VOICE, FAX, MSG, CELL, PAGER, BBS, CAR, MODEM, ISDN, VIDEO or combinations (e.g., "PREF,WORK,VOICE") (optional)
	 * @return self Returns this instance for method chaining
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
	 * Adds a company/organization to the vCard.
	 * Sets the ORG property and optionally sets the FN property if not already set.
	 * @param string $organizationName The organization/company name
	 * @param string $organizationUnits The organizational units/departments (optional)
	 * @return self Returns this instance for method chaining
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
	 * Adds a role, occupation, or business category to the vCard.
	 * @param string $role The role, occupation, or business category
	 * @return self Returns this instance for method chaining
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
	 * Adds a job title or functional position to the vCard.
	 * @param string $jobTitle The job title or functional position
	 * @return self Returns this instance for method chaining
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
	 * Adds a URL to the vCard.
	 * Multiple URLs can be added with different types.
	 * @param string $url The URL to add
	 * @param string $type The URL type: WORK or HOME (optional)
	 * @return self Returns this instance for method chaining
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
	 * Adds a logo to the vCard.
	 * The logo can be embedded as base64 or referenced as a URL.
	 * @param string $url The image URL or file path
	 * @param bool $include Whether to embed the image as base64 in the vCard (default: false)
	 * @return self Returns this instance for method chaining
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
	 * Adds a photo to the vCard.
	 * The photo can be embedded as base64 or referenced as a URL.
	 * @param string $url The image URL or file path
	 * @param bool $include Whether to embed the image as base64 in the vCard (default: false)
	 * @return self Returns this instance for method chaining
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
	 * Adds a sound file to the vCard.
	 * The sound can be embedded as base64 or referenced as a URL.
	 * @param string $url The sound file URL or file path
	 * @param bool $include Whether to embed the sound as base64 in the vCard (default: false)
	 * @return self Returns this instance for method chaining
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
	 * Adds a time zone to the vCard.
	 * @param string $timeZone The time zone identifier (e.g., "America/New_York", "+05:00")
	 * @return self Returns this instance for method chaining
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
	 * Adds a unique identifier (UID) to the vCard.
	 * The UID is a persistent, globally unique identifier associated with the vCard.
	 * @param string $uid The unique identifier (typically a UUID)
	 * @return self Returns this instance for method chaining
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
	 * Adds a source URL that can be used to retrieve the latest version of this vCard.
	 * @param string $source The URL where the vCard can be retrieved
	 * @return self Returns this instance for method chaining
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
	 * Adds a public key to the vCard.
	 * Can be used for encryption or authentication purposes.
	 * @param string $key The public key data
	 * @param string $type The key type (optional)
	 * @return self Returns this instance for method chaining
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
	 * Adds a language preference to the vCard.
	 * @param string $lang The language code (e.g., "en", "fr", "es")
	 * @return self Returns this instance for method chaining
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
	 * Adds a note or comment to the vCard.
	 * @param string $note The note text
	 * @return self Returns this instance for method chaining
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
	 * Sets the character encoding for the vCard content.
	 * @param string $charset The character set (e.g., "utf-8", "iso-8859-1")
	 * @return void
	 */
	public function setCharset(string $charset): void
	{
		$this->charset = $charset;
	}

	/**
	 * Gets the current character encoding of the vCard.
	 * @return string The character set
	 */
	public function getCharset(): string
	{
		return $this->charset;
	}

	/**
	 * Saves the vCard content to a file.
	 * Creates the file and any necessary parent directories.
	 * @param string $path The file path where the vCard should be saved
	 * @return void
	 */
	public function build(string $path): void
	{
		\Osimatic\FileSystem\FileSystem::initializeFile($path);

		file_put_contents($path, $this->getContent());
	}

	/**
	 * Triggers a browser download of the vCard file.
	 * Sets appropriate headers to prompt the user to download the vCard.
	 * @param string|null $filename The filename for the download (default: 'vcard.vcf')
	 * @return void
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
	 * Gets the complete vCard content as a string.
	 * Generates the vCard file content in version 3.0 format with all added properties.
	 * @return string The complete vCard content
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
	 * Sets the filename to use when saving or downloading the vCard.
	 * Can accept a string or array of values which will be joined with a separator.
	 * The filename is automatically URL-encoded and sanitized.
	 * @param mixed $value The filename or array of filename components
	 * @param bool $overwrite Whether to overwrite the existing filename or append to it (default: true)
	 * @param string $separator The separator to use when joining array values (default: '_')
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
	 * Gets the vCard file extension.
	 * @return string The file extension ('.vcf')
	 */
	public function getFileExtension(): string
	{
		return self::FILE_EXTENSION;
	}

	/**
	 * Parses a vCard from a file.
	 * Reads the file and extracts vCard data into structured arrays.
	 * @param string $filename The path to the vCard file
	 * @return array|null Array of parsed vCard objects or null if file cannot be read
	 */
	public function parseFromFile(string $filename): ?array
	{
		if (!file_exists($filename) || !is_readable($filename)) {
			return null;
		}
		return $this->parse(file_get_contents($filename));
	}

	/**
	 * Parses vCard content from a string.
	 * Extracts and decodes vCard properties into structured data arrays.
	 * Supports multiple vCards in a single string.
	 * @param string $content The vCard content to parse
	 * @return array|null Array of parsed vCard objects or null on failure
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
				continue;
			}
			if (mb_strtoupper($line) === 'END:VCARD') {
				$vcardObjects[] = $cardData;
				continue;
			}
			if (empty($line)) {
				continue;
			}

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
					} catch (\Exception) {}
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
					$cardData['birthday'] = $this->parseBirthday($value);
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
		return $vcardObjects;
	}

	// ---------- Private Methods ----------

	/**
	 * Adds a media file (photo, logo, or sound) to the vCard.
	 * The media can be embedded as base64 or referenced as a URL.
	 * Handles MIME type detection and appropriate encoding.
	 * @param string $property The property type: LOGO, PHOTO, or SOUND
	 * @param string $url The media URL or file path
	 * @param bool $include Whether to embed the media as base64 in the vCard (default: true)
	 * @return bool True on success, false on failure
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
	 * Folds a line according to RFC2425 section 5.8.1.
	 * Splits long lines into chunks to comply with vCard line length restrictions.
	 * @link http://tools.ietf.org/html/rfc2425#section-5.8.1
	 * @param string $text The text to fold
	 * @return array Array of folded line segments
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
	 * Gets the charset string for vCard properties.
	 * Returns the CHARSET parameter if the charset is UTF-8.
	 * @return string The charset parameter string or empty string
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
	 * Gets all vCard properties.
	 * @return array Array of properties with 'key' and 'value' elements
	 */
	private function getProperties(): array
	{
		return $this->properties;
	}

	/**
	 * Checks if a specific property has been set.
	 * @param string $key The property key to check
	 * @return bool True if property exists with a non-empty value, false otherwise
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
	 * Sets a vCard property.
	 * Prevents duplicate properties unless they are in the allowed list.
	 * @param string $element The element name (e.g., name, email, phoneNumber)
	 * @param string $key The vCard property key
	 * @param string $value The property value
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
	 * Parses a name property from vCard format.
	 * Extracts structured name components (lastname, firstname, additional, prefix, suffix).
	 * @param string $value The semicolon-separated name value
	 * @return object Object containing name components
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

	/**
	 * Parses a birthday value into a DateTime object.
	 * @param string $value The birthday value from the vCard
	 * @return \DateTime|null DateTime object or null if parsing fails
	 */
	private function parseBirthday($value): ?\DateTime
	{
		try {
			return new \DateTime($value);
		}
		catch (\Exception) {}
		return null;
	}

	/**
	 * Parses an address property from vCard format.
	 * Extracts structured address components (name, extended, street, city, region, zip, country).
	 * @param string $value The semicolon-separated address value
	 * @return object Object containing address components
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