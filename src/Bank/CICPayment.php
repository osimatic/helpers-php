<?php

namespace Osimatic\Bank;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author Euro-Information <centrecom@e-i.com>
 */
class CICPayment
{
	const CMCIC_VERSION 			= '3.0';
	const CMCIC_URL_PAIEMENT 		= 'https://ssl.paiement.cic-banques.fr/paiement.cgi';
	const CMCIC_URL_PAIEMENT_TEST 	= 'https://ssl.paiement.cic-banques.fr/test/paiement.cgi';

	/**
	 * @var int
	 */
	private $tpeNumber;

	/**
	 * @var string
	 */
	private $companyCode;

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * Montant TTC de la commande formaté de la manière suivante : Un nombre entier, Un point décimal (optionnel), Un nombre entier de n chiffres (n étant le nombre maximal de décimales de la devise) (optionnel)
	 * @var float
	 */
	private $allTaxesInclAmount;

	/**
	 * Devise sur 3 caractères alphabétiques ISO4217
	 * @var string
	 */
	private $currency;

	/**
	 * Référence unique de la commande. Taille : 12 caractères alphanumériques maximum
	 * @var string
	 */
	private $reference;

	/**
	 * Zone de texte libre. Taille : 3200 caractères maximum. Exemples : une référence longue, des contextes de session pour le retour,…
	 * @var string
	 */
	private $texteLibre;

	/**
	 * Code langue. Taille : 2 caractères. Exemples : "FR","EN","DE","IT","ES" selon options souscrites
	 * @var string
	 */
	private $language;

	/**
	 * Adresse email de l’internaute
	 * @var string
	 */
	private $customerEmail;

	/**
	 * @var string
	 */
	private $formTagClass;

	/**
	 * @var string
	 */
	private $buttonTagClass;

	/**
	 * @var string
	 */
	private $buttonTagText;

	/**
	 * @var boolean
	 */
	private $paiementTest;

	/**
	 * URL par laquelle l’acheteur revient sur la page d’accueil de la boutique
	 * @var string
	 */
	private $returnUrlHome;

	/**
	 * URL par laquelle l’acheteur revient sur le site du commerçant suite à un paiement accepté
	 * @var string
	 */
	private $returnUrlOk;

	/**
	 * URL par laquelle l’acheteur revient sur le site du commerçant suite à un paiement refusé
	 * @var string
	 */
	private $returnUrlNotOk;

	public function __construct()
	{
		$this->logger = new NullLogger();
	}


	/**
	 * @return array|null
	 */
	public function getValidationPaymentData(): ?array
	{
		return $this->getDataResultPaiement();
	}

	/**
	 * @return string
	 */
	public function getForm(): string
	{
		return $this->getCodePaiement(true);
	}

	/**
	 * @return string
	 */
	public function getUrl(): string
	{
		return $this->getCodePaiement(false);
	}

	/**
	 * @return string
	 */
	public function getControlStringForSupport(): string
	{
		return $this->getCtlHmac();
	}


	/**
	 * @param int $tpeNumber
	 * @return self
	 */
	public function setTpeNumber(int $tpeNumber): self
	{
		$this->tpeNumber = $tpeNumber;

		return $this;
	}

	/**
	 * @param string $companyCode
	 * @return self
	 */
	public function setCompanyCode(string $companyCode): self
	{
		$this->companyCode = $companyCode;

		return $this;
	}

	/**
	 * @param string $key
	 * @return self
	 */
	public function setKey(string $key): self
	{
		$this->key = $key;
		
		return $this;
	}

	/**
	 * Set the logger to use to log debugging data.
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	/**
	 * @param float $allTaxesInclAmount
	 * @return self
	 */
	public function setAllTaxesInclAmount(float $allTaxesInclAmount): self
	{
		$this->allTaxesInclAmount = $allTaxesInclAmount;

		return $this;
	}

	/**
	 * @param string $currency
	 * @return self
	 */
	public function setCurrency(string $currency): self
	{
		$this->currency = $currency;

		return $this;
	}

	/**
	 * @param string|null $reference
	 * @return self
	 */
	public function setReference(?string $reference): self
	{
		$this->reference = $reference;

		return $this;
	}

	/**
	 * @param string|null $texteLibre
	 * @return self
	 */
	public function setTexteLibre(?string $texteLibre): self
	{
		$this->texteLibre = $texteLibre;

		return $this;
	}

	/**
	 * @param string|null $language
	 * @return self
	 */
	public function setLanguage(?string $language): self
	{
		$this->language = $language;

		return $this;
	}

	/**
	 * @param string|null $customerEmail
	 * @return self
	 */
	public function setCustomerEmail(?string $customerEmail): self
	{
		$this->customerEmail = $customerEmail;

		return $this;
	}

	public function setButtonTagText(?string $buttonTagText): self
	{
		$this->buttonTagText = $buttonTagText;

		return $this;
	}

	public function setFormTagClass(?string $formTagClass): self
	{
		$this->formTagClass = $formTagClass;

		return $this;
	}

	public function setButtonTagClass(?string $buttonTagClass): self
	{
		$this->buttonTagClass = $buttonTagClass;

		return $this;
	}

	/**
	 * @param bool $paiementTest
	 * @return self
	 */
	public function setPaiementTest(bool $paiementTest): self
	{
		$this->paiementTest = $paiementTest;

		return $this;
	}

	/**
	 * @param string|null $returnUrlHome
	 * @return self
	 */
	public function setReturnUrlHome(?string $returnUrlHome): self
	{
		$this->returnUrlHome = $returnUrlHome;

		return $this;
	}

	/**
	 * @param string|null $returnUrlOk
	 * @return self
	 */
	public function setReturnUrlOk(?string$returnUrlOk): self
	{
		$this->returnUrlOk = $returnUrlOk;

		return $this;
	}

	/**
	 * @param string|null $returnUrlNotOk
	 * @return self
	 */
	public function setReturnUrlNotOk(?string$returnUrlNotOk): self
	{
		$this->returnUrlNotOk = $returnUrlNotOk;

		return $this;
	}



	// ========== Private function ==========

	private function getCodePaiement($byForm): string
	{
		if ($this->paiementTest) {
			$urlPaiement = self::CMCIC_URL_PAIEMENT_TEST;
		}
		else {
			$urlPaiement = self::CMCIC_URL_PAIEMENT;
		}


		$date = date('d/m/Y:H:i:s');
		$this->language = substr($this->language, 0, 2);

		if ($this->texteLibre == '') {
			$this->texteLibre .= '-';
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
				.'	<input type="hidden" name="texte-libre"    value="'.self::HtmlEncode($this->texteLibre).'" />'
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

	private function getCtlHmac(): string
	{
		//$hmac = self::computeHmac(sprintf(CMCIC_CTLHMACSTR, self::CMCIC_VERSION, $this->tpeNumber), $this->key);
		//return sprintf("V1.04.sha1.php--[CtlHmac%s%s]-%s", self::CMCIC_VERSION, $this->tpeNumber, $hmac);
		$data = sprintf('V1.04.sha1.php--[CtlHmac%s%s]', self::CMCIC_VERSION, $this->tpeNumber);
		$hmac = self::computeHmac($data, $this->key);
		return $data.'-'.$hmac;
	}

	private static function HtmlEncode($data): string
	{
		$SAFE_OUT_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890._-';
		$result = '';
		for ($i=0, $iMax = strlen($data); $i < $iMax; $i++) {
			if (strstr($SAFE_OUT_CHARS, $data[$i])) {
				$result .= $data[$i];
			}
			else if (($var = bin2hex(substr($data, $i,1))) <= '7F') {
				$result .= '&#x' . $var . ';';
			}
			else {
				$result .= $data[$i];
			}
		}
		return $result;
	}

	private static function getUsableKey($key)
	{
		$hexStrKey = substr($key, 0, 38);
		$hexFinal = '' . substr($key, 38, 2) . '00';

		$cca0 = ord($hexFinal);

		if ($cca0 > 70 && $cca0 < 97) {
			$hexStrKey .= chr($cca0 - 23) . substr($hexFinal, 1, 1);
		}
		else {
			if (substr($hexFinal, 1, 1) == 'M') {
				$hexStrKey .= substr($hexFinal, 0, 1) . '0';
			}
			else {
				$hexStrKey .= substr($hexFinal, 0, 2);
			}
		}
		return pack('H*', $hexStrKey);
	}

	private static function computeHmac($sData, $key): string
	{
		return strtolower(self::hmac_sha1(self::getUsableKey($key), $sData));
	}

	// ----------------------------------------------------------------------------
	// RFC 2104 HMAC implementation for PHP 4 >= 4.3.0 - Creates a SHA1 HMAC.
	// Eliminates the need to install mhash to compute a HMAC
	// Adjusted from the md5 version by Lance Rushing .

	// Implémentation RFC 2104 HMAC pour PHP 4 >= 4.3.0 - Création d'un SHA1 HMAC.
	// Elimine l'installation de mhash pour le calcul d'un HMAC
	// Adaptée de la version MD5 de Lance Rushing.
	// ----------------------------------------------------------------------------
	private static function hmac_sha1 ($key, $data): string
	{
		$length = 64; // block length for SHA1
		if (strlen($key) > $length) { $key = pack('H*',sha1($key)); }
		$key  = str_pad($key, $length, chr(0x00));
		$ipad = str_pad('', $length, chr(0x36));
		$opad = str_pad('', $length, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return sha1($k_opad  . pack('H*',sha1($k_ipad . $data)));
	}

	private function getDataResultPaiement(): ?array
	{
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$bruteVars  = $_GET;
		}
		elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$bruteVars  = $_POST;
		}
		else {
			$this->logger->error('Validation du paiement impossible : Invalid REQUEST_METHOD (not GET, not POST)');
			return null;
		}

		if (empty($bruteVars['TPE'])) {
			$this->logger->error('Validation du paiement impossible : Variables vides');
			return null;
		}

		// Vérification du MAC
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
			$this->logger->info('Le MAC ne correspond pas. MAC obtenu : '.$mac);
		}

		$paiementValid = $macOk && ($bruteVars['code-retour'] === 'payetest' || $bruteVars['code-retour'] === 'paiement');
		$paiementTest = ($bruteVars['code-retour'] === 'payetest');

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
