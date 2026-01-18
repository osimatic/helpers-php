<?php

namespace Osimatic\Bank;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * CIC/CM-CIC Monetico payment gateway integration class
 * Handles credit card payments via the CIC/Crédit Mutuel payment platform
 * Supports payment form generation, transaction validation, and HMAC security
 * @author Euro-Information <centrecom@e-i.com>
 */
class MoneticoPayment
{
	public const string CMCIC_VERSION 			= '3.0';
	public const string CMCIC_URL_PAIEMENT 		= 'https://ssl.paiement.cic-banques.fr/paiement.cgi';
	public const string CMCIC_URL_PAIEMENT_TEST = 'https://ssl.paiement.cic-banques.fr/test/paiement.cgi';

	private const string RESPONSE_CODE_PAYMENT = 'paiement';
	private const string RESPONSE_CODE_TEST_PAYMENT = 'payetest';
	private const string SAFE_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890._-';
	private const int HMAC_SHA1_BLOCK_LENGTH = 64;

	/**
	 * TPE (Terminal de Paiement Électronique) number
	 * The merchant's payment terminal identifier provided by CIC
	 * @var int
	 */
	private int $tpeNumber;

	/**
	 * Company code (société code)
	 * The merchant's company identifier provided by CIC
	 * @var string
	 */
	private string $companyCode;

	/**
	 * HMAC security key
	 * The secret key provided by CIC for cryptographic signing of payment requests
	 * @var string
	 */
	private string $key;

	/**
	 * PSR-3 logger instance for error and debugging information
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * Total amount including all taxes
	 * Format: integer, optional decimal point, optional integer with n digits (n being max decimals for currency)
	 * @var float
	 */
	private float $allTaxesInclAmount;

	/**
	 * Currency code (3 alphabetic characters ISO 4217)
	 * @var string
	 */
	private string $currency;

	/**
	 * Unique order reference
	 * Size: max 12 alphanumeric characters
	 * @var string
	 */
	private ?string $reference = null;

	/**
	 * Free text field
	 * Size: max 3200 characters. Examples: long reference, session contexts for return
	 * @var string
	 */
	private ?string $texteLibre = null;

	/**
	 * Language code
	 * Size: 2 characters. Examples: "FR", "EN", "DE", "IT", "ES" according to subscribed options
	 * @var string
	 */
	private ?string $language = null;

	/**
	 * Customer email address
	 * @var string
	 */
	private ?string $customerEmail = null;

	/**
	 * CSS class name for the payment form element
	 * Applied to the <form> HTML tag for styling purposes
	 * @var string
	 */
	private ?string $formTagClass = null;

	/**
	 * CSS class name for the submit button
	 * Applied to the submit button in the payment form for styling purposes
	 * @var string
	 */
	private ?string $buttonTagClass = null;

	/**
	 * Submit button text/label
	 * The text displayed on the payment form submit button
	 * @var string
	 */
	private ?string $buttonTagText = null;

	/**
	 * Test mode flag
	 * When true, uses CIC test environment URLs for development/testing
	 * @var bool
	 */
	private bool $paiementTest = false;

	/**
	 * URL where the buyer returns to the shop's homepage
	 * @var string
	 */
	private ?string $returnUrlHome = null;

	/**
	 * URL where the buyer returns to the merchant's site after a successful payment
	 * @var string
	 */
	private ?string $returnUrlOk = null;

	/**
	 * URL where the buyer returns to the merchant's site after a refused payment
	 * @var string
	 */
	private ?string $returnUrlNotOk = null;

	public function __construct()
	{
		$this->logger = new NullLogger();
	}


	/**
	 * Get payment validation data from CIC payment gateway response
	 * Validates HMAC signature and returns payment information
	 * @return array|null Payment data array with validation status, or null on error
	 */
	public function getValidationPaymentData(): ?array
	{
		return $this->getDataResultPaiement();
	}

	/**
	 * Generate HTML payment form
	 * Creates an HTML form that redirects the customer to CIC payment page
	 * @return string HTML form markup
	 */
	public function getForm(): string
	{
		return $this->getCodePaiement(true);
	}

	/**
	 * Generate payment URL
	 * Creates a direct URL to CIC payment gateway with parameters
	 * @return string Payment gateway URL with query parameters
	 */
	public function getUrl(): string
	{
		return $this->getCodePaiement(false);
	}

	/**
	 * Get control string for technical support
	 * Generates HMAC control string used for verifying configuration with CIC support team
	 * @return string Control HMAC string
	 */
	public function getControlStringForSupport(): string
	{
		return $this->getCtlHmac();
	}


	/**
	 * Set the TPE (Terminal de Paiement Électronique) number
	 * The merchant's payment terminal identifier provided by CIC
	 * @param int $tpeNumber The TPE number
	 * @return self Returns this instance for method chaining
	 */
	public function setTpeNumber(int $tpeNumber): self
	{
		$this->tpeNumber = $tpeNumber;

		return $this;
	}

	/**
	 * Set the company code (société code)
	 * The merchant's company identifier provided by CIC
	 * @param string $companyCode The company code
	 * @return self Returns this instance for method chaining
	 */
	public function setCompanyCode(string $companyCode): self
	{
		$this->companyCode = $companyCode;

		return $this;
	}

	/**
	 * Set the HMAC security key
	 * The secret key provided by CIC for cryptographic signing of payment requests
	 * @param string $key The HMAC security key
	 * @return self Returns this instance for method chaining
	 */
	public function setKey(string $key): self
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * Sets the logger for error and debugging information.
	 * @param LoggerInterface $logger The PSR-3 logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Set the transaction amount including all taxes
	 * The total amount to be charged to the customer
	 * @param float $allTaxesInclAmount The amount including all taxes
	 * @return self Returns this instance for method chaining
	 */
	public function setAllTaxesInclAmount(float $allTaxesInclAmount): self
	{
		$this->allTaxesInclAmount = $allTaxesInclAmount;

		return $this;
	}

	/**
	 * Set the transaction currency
	 * Three-letter ISO 4217 currency code (e.g., EUR, USD, GBP)
	 * @param string $currency The currency code
	 * @return self Returns this instance for method chaining
	 */
	public function setCurrency(string $currency): self
	{
		$this->currency = $currency;

		return $this;
	}

	/**
	 * Set the unique order reference
	 * Maximum 12 alphanumeric characters identifying the transaction
	 * @param string|null $reference The order reference
	 * @return self Returns this instance for method chaining
	 */
	public function setReference(?string $reference): self
	{
		$this->reference = $reference;

		return $this;
	}

	/**
	 * Set the free text field
	 * Free text field for additional data (max 3200 characters)
	 * Can be used for long references, session contexts, or other custom data
	 * @param string|null $texteLibre The free text content
	 * @return self Returns this instance for method chaining
	 */
	public function setTexteLibre(?string $texteLibre): self
	{
		$this->texteLibre = $texteLibre;

		return $this;
	}

	/**
	 * Set the payment page language
	 * Two-letter language code (e.g., FR, EN, DE, IT, ES) according to subscribed options
	 * @param string|null $language The language code
	 * @return self Returns this instance for method chaining
	 */
	public function setLanguage(?string $language): self
	{
		$this->language = $language;

		return $this;
	}

	/**
	 * Set the customer email address
	 * Email address of the customer making the payment
	 * @param string|null $customerEmail The customer's email address
	 * @return self Returns this instance for method chaining
	 */
	public function setCustomerEmail(?string $customerEmail): self
	{
		$this->customerEmail = $customerEmail;

		return $this;
	}

	/**
	 * Set the payment button text label
	 * Customizes the text displayed on the payment form submit button
	 * @param string|null $buttonTagText The button text/label
	 * @return self Returns this instance for method chaining
	 */
	public function setButtonTagText(?string $buttonTagText): self
	{
		$this->buttonTagText = $buttonTagText;

		return $this;
	}

	/**
	 * Set CSS class for payment form
	 * Applied to the <form> HTML tag for styling
	 * @param string|null $formTagClass CSS class name
	 * @return self Returns this instance for method chaining
	 */
	public function setFormTagClass(?string $formTagClass): self
	{
		$this->formTagClass = $formTagClass;

		return $this;
	}

	/**
	 * Set CSS class for payment button
	 * Applied to the submit button in payment form for styling
	 * @param string|null $buttonTagClass CSS class name
	 * @return self Returns this instance for method chaining
	 */
	public function setButtonTagClass(?string $buttonTagClass): self
	{
		$this->buttonTagClass = $buttonTagClass;

		return $this;
	}

	/**
	 * Enable or disable test mode
	 * In test mode, uses CIC test URLs for development/testing
	 * @param bool $paiementTest True to enable test mode, false for production
	 * @return self Returns this instance for method chaining
	 */
	public function setPaiementTest(bool $paiementTest): self
	{
		$this->paiementTest = $paiementTest;

		return $this;
	}

	/**
	 * Set the return URL to shop homepage
	 * URL where the buyer returns after payment regardless of outcome
	 * @param string|null $returnUrlHome The homepage URL
	 * @return self Returns this instance for method chaining
	 */
	public function setReturnUrlHome(?string $returnUrlHome): self
	{
		$this->returnUrlHome = $returnUrlHome;

		return $this;
	}

	/**
	 * Set the return URL for successful payment
	 * URL where the buyer is redirected after successful payment completion
	 * @param string|null $returnUrlOk The success return URL
	 * @return self Returns this instance for method chaining
	 */
	public function setReturnUrlOk(?string$returnUrlOk): self
	{
		$this->returnUrlOk = $returnUrlOk;

		return $this;
	}

	/**
	 * Set the return URL for refused payment
	 * URL where the buyer is redirected after payment refusal or error
	 * @param string|null $returnUrlNotOk The error return URL
	 * @return self Returns this instance for method chaining
	 */
	public function setReturnUrlNotOk(?string$returnUrlNotOk): self
	{
		$this->returnUrlNotOk = $returnUrlNotOk;

		return $this;
	}



	// ========== Private function ==========

	/**
	 * Generate payment code (form HTML or URL)
	 * Creates either an HTML form or a direct URL to CIC payment gateway
	 * Calculates HMAC signature to secure the payment request
	 * @param bool $byForm True to generate HTML form, false to generate URL with query parameters
	 * @return string The HTML form markup or payment URL with parameters
	 * @throws \RuntimeException if required fields are not set
	 */
	private function getCodePaiement(bool $byForm): string
	{
		// Validate required fields
		if (!isset($this->tpeNumber)) {
			throw new \RuntimeException('TPE number is required for CIC payment');
		}
		if (!isset($this->companyCode)) {
			throw new \RuntimeException('Company code is required for CIC payment');
		}
		if (!isset($this->key)) {
			throw new \RuntimeException('HMAC key is required for CIC payment');
		}
		if (!isset($this->allTaxesInclAmount)) {
			throw new \RuntimeException('Amount is required for CIC payment');
		}
		if (!isset($this->currency)) {
			throw new \RuntimeException('Currency is required for CIC payment');
		}

		if ($this->paiementTest) {
			$urlPaiement = self::CMCIC_URL_PAIEMENT_TEST;
		}
		else {
			$urlPaiement = self::CMCIC_URL_PAIEMENT;
		}


		$date = date('d/m/Y:H:i:s');
		$this->language = $this->language ? substr($this->language, 0, 2) : null;

		if (empty($this->texteLibre)) {
			$this->texteLibre = '-';
		}

		// Calcul du MAC
		$CMCIC_CGI1_FIELDS = '%s*%s*%s%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s';
		$fields = sprintf($CMCIC_CGI1_FIELDS,
			$this->tpeNumber,
			$date,
			$this->allTaxesInclAmount,
			$this->currency,
			$this->reference,
			$this->texteLibre,
			self::CMCIC_VERSION,
			$this->language,
			$this->companyCode,
			$this->customerEmail,
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			''
		);

		$mac = self::computeHmac($fields, $this->key);
		$this->logger->info('CICPayment $fields = '.$fields);

		$data = array(
			'version' => self::CMCIC_VERSION,
			'TPE' => $this->tpeNumber,
			'societe' => $this->companyCode,
			'lgue' => $this->language,
			'date' => $date,
			'montant' => $this->allTaxesInclAmount.$this->currency,
			'reference' => $this->reference,
			'MAC' => $mac,
			'url_retour' => $this->returnUrlHome,
			'url_retour_ok' => $this->returnUrlOk,
			'url_retour_err' => $this->returnUrlNotOk,
			'texte-libre' => $this->texteLibre,
			'mail' => $this->customerEmail,
			'bouton' => $this->buttonTagText,
		);
		//trace('$data = '.\My\ArrayList\ArrayHelper::displayStringRecursive($data));

		if ($byForm) {
			$codePaiement = ''
				.'<form action="'.$urlPaiement.'" method="post" name="PaymentRequest" target="_top" class="'.$this->formTagClass.'">'
				.'	<input type="hidden" name="version"        value="'.self::CMCIC_VERSION.'" />'
				.'	<input type="hidden" name="TPE"            value="'.$this->tpeNumber.'" />'
				.'	<input type="hidden" name="societe"        value="'.$this->companyCode.'" />'
				.'	<input type="hidden" name="lgue"           value="'.$this->language.'" />'
				.'	<input type="hidden" name="date"           value="'.$date.'" />'
				.'	<input type="hidden" name="montant"        value="'.$this->allTaxesInclAmount.$this->currency.'" />'
				.'	<input type="hidden" name="reference"      value="'.$this->reference.'" />'
				.'	<input type="hidden" name="MAC"            value="'.$mac.'" />'
				.'	<input type="hidden" name="url_retour"     value="'.$this->returnUrlHome.'" />'
				.'	<input type="hidden" name="url_retour_ok"  value="'.$this->returnUrlOk.'" />'
				.'	<input type="hidden" name="url_retour_err" value="'.$this->returnUrlNotOk.'" />'
				.'	<input type="hidden" name="texte-libre"    value="'.self::htmlEncode($this->texteLibre).'" />'
				.'	<input type="hidden" name="mail"           value="'.$this->customerEmail.'" />'
				.'	<!-- Uniquement pour le Paiement fractionné -->'
				.'	<input type="hidden" name="nbrech"         value="" />'
				.'	<input type="hidden" name="dateech1"       value="" />'
				.'	<input type="hidden" name="montantech1"    value="" />'
				.'	<input type="hidden" name="dateech2"       value="" />'
				.'	<input type="hidden" name="montantech2"    value="" />'
				.'	<input type="hidden" name="dateech3"       value="" />'
				.'	<input type="hidden" name="montantech3"    value="" />'
				.'	<input type="hidden" name="dateech4"       value="" />'
				.'	<input type="hidden" name="montantech4"    value="" />'
				.'	<button type="submit" name="bouton" class="btn btn-default '.$this->buttonTagClass.'" >'.$this->buttonTagText.'</button>'
				.'</form>'
			;
		}
		else {
			$strPost = '';
			foreach ($data as $cle => $value) {
				$strPost .= $cle.'='.$value.'&';
			}
			$strPost .= '';
			$strPost = substr($strPost, 0, -1);

			$codePaiement = $urlPaiement.'?'.$strPost;
		}

		return $codePaiement;
	}

	/**
	 * Generate control HMAC for technical support
	 * Creates a control string with HMAC signature used to verify CIC configuration with support team
	 * Format: "V1.04.sha1.php--[CtlHmac{version}{tpe}]-{hmac}"
	 * @return string The control HMAC string for technical support verification
	 */
	private function getCtlHmac(): string
	{
		//$hmac = self::computeHmac(sprintf(CMCIC_CTLHMACSTR, self::CMCIC_VERSION, $this->tpeNumber), $this->key);
		//return sprintf("V1.04.sha1.php--[CtlHmac%s%s]-%s", self::CMCIC_VERSION, $this->tpeNumber, $hmac);
		$data = sprintf('V1.04.sha1.php--[CtlHmac%s%s]', self::CMCIC_VERSION, $this->tpeNumber);
		$hmac = self::computeHmac($data, $this->key);
		return $data.'-'.$hmac;
	}

	/**
	 * HTML encode data for safe inclusion in payment forms
	 * Encodes special characters to HTML entities for safe transmission in form fields
	 * Only alphanumeric and safe characters (._-) are preserved
	 * @param string|null $data The data to encode
	 * @return string The HTML-encoded string
	 */
	private static function htmlEncode(?string $data): string
	{
		if (empty($data)) {
			return '';
		}

		$result = '';
		for ($i = 0, $iMax = strlen($data); $i < $iMax; $i++) {
			$char = $data[$i];
			if (strpos(self::SAFE_CHARS, $char) !== false) {
				$result .= $char;
			}
			elseif (($var = bin2hex($char)) <= '7F') {
				$result .= '&#x' . $var . ';';
			}
			else {
				$result .= $char;
			}
		}
		return $result;
	}

	/**
	 * Convert CIC hexadecimal key to usable binary key
	 * Processes the 40-character hexadecimal key provided by CIC to convert it to binary format
	 * Applies CIC-specific transformations to handle special characters in the key
	 * @param string $key The hexadecimal key string provided by CIC
	 * @return string The binary key ready for HMAC computation
	 */
	private static function getUsableKey(string $key): string
	{
		$hexStrKey = substr($key, 0, 38);
		$hexFinal = substr($key, 38, 2) . '00';

		$cca0 = ord($hexFinal);

		if ($cca0 > 70 && $cca0 < 97) {
			$hexStrKey .= chr($cca0 - 23) . $hexFinal[1];
		}
		elseif ($hexFinal[1] === 'M') {
			$hexStrKey .= $hexFinal[0] . '0';
		}
		else {
			$hexStrKey .= substr($hexFinal, 0, 2);
		}
		return pack('H*', $hexStrKey);
	}

	/**
	 * Compute HMAC-SHA1 signature for payment data
	 * Calculates the HMAC signature required by CIC to authenticate payment requests and responses
	 * Uses the CIC-specific key format and returns lowercase hexadecimal hash
	 * @param string $sData The data string to sign
	 * @param string $key The CIC hexadecimal key
	 * @return string The lowercase hexadecimal HMAC-SHA1 signature
	 */
	private static function computeHmac(string $sData, string $key): string
	{
		return strtolower(self::hmac_sha1(self::getUsableKey($key), $sData));
	}

	/**
	 * RFC 2104 HMAC-SHA1 implementation
	 * Creates a SHA1 HMAC without requiring the mhash extension
	 * Implements the HMAC algorithm using SHA1 as specified in RFC 2104
	 * Adjusted from the MD5 version by Lance Rushing
	 * @param string $key The binary key for HMAC
	 * @param string $data The data to sign
	 * @return string The hexadecimal SHA1 HMAC signature
	 */
	private static function hmac_sha1(string $key, string $data): string
	{
		$length = self::HMAC_SHA1_BLOCK_LENGTH;
		if (strlen($key) > $length) {
			$key = pack('H*', sha1($key));
		}
		$key = str_pad($key, $length, chr(0x00));
		$ipad = str_pad('', $length, chr(0x36));
		$opad = str_pad('', $length, chr(0x5c));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;

		return sha1($k_opad . pack('H*', sha1($k_ipad . $data)));
	}

	/**
	 * Process and validate payment response from CIC gateway
	 * Retrieves payment result data from GET or POST request, validates HMAC signature,
	 * and returns structured payment information including validation status
	 * @return array|null Array with payment result data (reference, amount, currency, validation status), or null on error
	 */
	private function getDataResultPaiement(): ?array
	{
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$bruteVars  = $_GET;
		}
		elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$bruteVars  = $_POST;
		}
		else {
			$this->logger->error('Payment validation failed: Invalid REQUEST_METHOD (not GET, not POST)');
			return null;
		}

		if (empty($bruteVars['TPE'])) {
			$this->logger->error('Payment validation failed: Empty required variables');
			return null;
		}

		// MAC verification
		$CMCIC_CGI2_FIELDS = '%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*';
		$fields = sprintf($CMCIC_CGI2_FIELDS,
			$this->tpeNumber,
			$bruteVars['date'],
			$bruteVars['montant'],
			$bruteVars['reference'],
			$bruteVars['texte-libre'],
			self::CMCIC_VERSION,
			$bruteVars['code-retour'],
			$bruteVars['cvx'],
			$bruteVars['vld'],
			$bruteVars['brand'],
			$bruteVars['status3ds'],
			($bruteVars['numauto'] ?? ''),
			($bruteVars['motifrefus'] ?? ''),
			($bruteVars['originecb'] ?? ''),
			($bruteVars['bincb'] ?? ''),
			($bruteVars['hpancb'] ?? ''),
			($bruteVars['ipclient'] ?? ''),
			($bruteVars['originetr'] ?? ''),
			($bruteVars['veres'] ?? ''),
			($bruteVars['pares'] ?? '')
		);
		$mac = self::computeHmac($fields, $this->key);
		$this->logger->info('CICPayment $fields = '.$fields);

		if (strtolower($bruteVars['MAC']) === $mac) {
			$macOk = true;
			$reponseRenvoyee = "version=2\ncdr=0\n";
		}
		else {
			$macOk = false;
			$reponseRenvoyee = "version=2\ncdr=1\n";
			$this->logger->info('MAC mismatch. Received MAC: '.$mac);
		}

		$paiementValid = $macOk && ($bruteVars['code-retour'] === self::RESPONSE_CODE_TEST_PAYMENT || $bruteVars['code-retour'] === self::RESPONSE_CODE_PAYMENT);
		$paiementTest = ($bruteVars['code-retour'] === self::RESPONSE_CODE_TEST_PAYMENT);

		$currency = substr($bruteVars['montant'], -3);
		$montantTtc   = substr($bruteVars['montant'], 0, -3);

		// trace('$resultData = '.\My\ArrayList\ArrayHelper::displayStringRecursive($resultData));

		return [
			'reference' 					=> $bruteVars['reference'],
			'currency' 						=> $currency,
			'montant_ttc' 					=> $montantTtc,
			'paiement_valid' 				=> $paiementValid,
			'paiement_test' 				=> $paiementTest,
			'accuse_reception_display' 		=> $reponseRenvoyee,
			'texte_libre' 					=> $bruteVars['texte-libre'],
			'numero_autorisation' 			=> ($bruteVars['numauto'] ?? ''),
		];
	}


}
