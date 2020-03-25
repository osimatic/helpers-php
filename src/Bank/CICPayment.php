<?php

namespace Osimatic\Helpers\Bank;

/**
 * @author Euro-Information <centrecom@e-i.com>
 */
class CICPayment
{
	const CMCIC_VERSION 			= "3.0";
	const CMCIC_URL_PAIEMENT 		= "https://ssl.paiement.cic-banques.fr/paiement.cgi";
	const CMCIC_URL_PAIEMENT_TEST 	= "https://ssl.paiement.cic-banques.fr/test/paiement.cgi";

	private $tpeNumber;
	private $companyCode;
	private $key;

	// Montant TTC de la commande formaté de la manière suivante : Un nombre entier, Un point décimal (optionnel), Un nombre entier de n chiffres (n étant le nombre maximal de décimales de la devise) (optionnel)
	private $allTaxesInclAmount;

	// Devise sur 3 caractères alphabétiques ISO4217
	private $currency;

	// Référence unique de la commande. Taille : 12 caractères alphanumériques maximum
	private $reference;

	// Zone de texte libre. Taille : 3200 caractères maximum. Exemples : une référence longue, des contextes de session pour le retour,...
	private $texteLibre;

	// Code langue. Taille : 2 caractères. Exemples : "FR","EN","DE","IT","ES" selon options souscrites
	private $language;

	// Adresse email de l’internaute
	private $customerEmail;

	private $formTagClass;
	private $buttonTagClass;
	private $buttonTagText;

	private $paiementTest;

	// URL par laquelle l’acheteur revient sur la page d’accueil de la boutique
	private $returnUrlHome;
	// URL par laquelle l’acheteur revient sur le site du commerçant suite à un paiement accepté
	private $returnUrlOk;
	// URL par laquelle l’acheteur revient sur le site du commerçant suite à un paiement refusé
	private $returnUrlNotOk;


	public function getDataValidationPaiement() {
		return $this->getDataResultPaiement();
	}

	public function getForm() {
		return $this->getCodePaiement(true);
	}

	public function getUrl() {
		return $this->getCodePaiement(false);
	}

	public function getControlStringForSupport() {
		return $this->getCtlHmac();
	}


	public function getAllTaxesInclAmount() {
		return $this->allTaxesInclAmount;
	}

	public function getCurrency() {
		return $this->currency;
	}

	public function getReference() {
		return $this->reference;
	}

	public function getTexteLibre() {
		return $this->texteLibre;
	}

	public function getLanguage() {
		return $this->language;
	}

	public function getCustomerEmail() {
		return $this->customerEmail;
	}

	public function getButtonTagText() {
		return $this->buttonTagText;
	}

	public function getFormTagClass() {
		return $this->formTagClass;
	}

	public function getButtonTagClass() {
		return $this->buttonTagClass;
	}

	public function getPaiementTest() {
		return $this->paiementTest;
	}

	public function getReturnUrlHome() {
		return $this->returnUrlHome;
	}

	public function getReturnUrlOk() {
		return $this->returnUrlOk;
	}

	public function getReturnUrlNotOk() {
		return $this->returnUrlNotOk;
	}

	/**
	 * @param mixed $tpeNumber
	 */
	public function setTpeNumber($tpeNumber): void
	{
		$this->tpeNumber = $tpeNumber;
	}

	/**
	 * @param mixed $companyCode
	 */
	public function setCompanyCode($companyCode): void
	{
		$this->companyCode = $companyCode;
	}

	/**
	 * @param mixed $key
	 */
	public function setKey($key): void
	{
		$this->key = $key;
	}
	
	public function setAllTaxesInclAmount($allTaxesInclAmount) {
		$this->allTaxesInclAmount = $allTaxesInclAmount;
	}

	public function setCurrency($currency) {
		$this->currency = $currency;
	}

	public function setReference($reference) {
		$this->reference = $reference;
	}

	public function setTexteLibre($texteLibre) {
		$this->texteLibre = $texteLibre;
	}

	public function setLanguage($language) {
		$this->language = $language;
	}

	public function setCustomerEmail($customerEmail) {
		$this->customerEmail = $customerEmail;
	}

	public function setButtonTagText($buttonTagText) {
		$this->buttonTagText = $buttonTagText;
	}

	public function setFormTagClass($formTagClass) {
		$this->formTagClass = $formTagClass;
	}

	public function setButtonTagClass($buttonTagClass) {
		$this->buttonTagClass = $buttonTagClass;
	}

	public function setPaiementTest($paiementTest) {
		$this->paiementTest = $paiementTest;
	}

	public function setReturnUrlHome($returnUrlHome) {
		$this->returnUrlHome = $returnUrlHome;
	}

	public function setReturnUrlOk($returnUrlOk) {
		$this->returnUrlOk = $returnUrlOk;
	}

	public function setReturnUrlNotOk($returnUrlNotOk) {
		$this->returnUrlNotOk = $returnUrlNotOk;
	}



	// ========== PRIVATE FUNCTION ==========

	private function getCodePaiement($byForm) {
		if ($this->paiementTest) {
			$urlPaiement = self::CMCIC_URL_PAIEMENT_TEST;
		}
		else {
			$urlPaiement = self::CMCIC_URL_PAIEMENT;
		}


		$date = date("d/m/Y:H:i:s");
		$this->language = substr($this->language, 0, 2);

		if ($this->texteLibre == "") {
			$this->texteLibre .= "-";
		}

		// Calcul du MAC
		$CMCIC_CGI1_FIELDS = "%s*%s*%s%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s";
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
		//trace('$fields = '.$fields);

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
				.'	<input type="hidden" name="texte-libre"    value="'.self::HtmlEncode( $this->texteLibre ).'" />'
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

	private function getCtlHmac() {
		//$hmac = self::computeHmac(sprintf(CMCIC_CTLHMACSTR, self::CMCIC_VERSION, $this->tpeNumber), $this->key);
		//return sprintf("V1.04.sha1.php--[CtlHmac%s%s]-%s", self::CMCIC_VERSION, $this->tpeNumber, $hmac);
		$data = sprintf("V1.04.sha1.php--[CtlHmac%s%s]", self::CMCIC_VERSION, $this->tpeNumber);
		$hmac = self::computeHmac($data, $this->key);
		return $data.'-'.$hmac;
	}

	private static function HtmlEncode ($data) {
		$SAFE_OUT_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890._-";
		$result = "";
		for ($i=0; $i<strlen($data); $i++) {
			if (strchr($SAFE_OUT_CHARS, $data{$i})) {
				$result .= $data{$i};
			}
			else if (($var = bin2hex(substr($data,$i,1))) <= "7F"){
				$result .= "&#x" . $var . ";";
			}
			else {
				$result .= $data{$i};
			}
		}
		return $result;
	}

	private static function getUsableKey($key) {
		$hexStrKey = substr($key, 0, 38);
		$hexFinal = "" . substr($key, 38, 2) . "00";

		$cca0 = ord($hexFinal);

		if ($cca0 > 70 && $cca0 < 97)
			$hexStrKey .= chr($cca0 - 23) . substr($hexFinal, 1, 1);
		else {
			if (substr($hexFinal, 1, 1) == "M")
				$hexStrKey .= substr($hexFinal, 0, 1) . "0";
			else
				$hexStrKey .= substr($hexFinal, 0, 2);
		}
		return pack("H*", $hexStrKey);
	}

	private static function computeHmac($sData, $key) {
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
	private static function hmac_sha1 ($key, $data) {
		$length = 64; // block length for SHA1
		if (strlen($key) > $length) { $key = pack("H*",sha1($key)); }
		$key  = str_pad($key, $length, chr(0x00));
		$ipad = str_pad('', $length, chr(0x36));
		$opad = str_pad('', $length, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return sha1($k_opad  . pack("H*",sha1($k_ipad . $data)));
	}


	private function getDataResultPaiement() {
		if ($_SERVER["REQUEST_METHOD"] == "GET") {
			$bruteVars  = $_GET;
		}
		elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
			$bruteVars  = $_POST;
		}
		else {
			//error('Validation du paiement impossible : Invalid REQUEST_METHOD (not GET, not POST)');
			return false;
		}

		if (empty($bruteVars['TPE'])) {
			//error('Validation du paiement impossible : Variables vides');
			return false;
		}

		// Vérification du MAC
		$CMCIC_CGI2_FIELDS = "%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*";
		$fields = sprintf($CMCIC_CGI2_FIELDS,
			$this->tpeNumber,
			$bruteVars["date"],
			$bruteVars['montant'],
			$bruteVars['reference'],
			$bruteVars['texte-libre'],
			self::CMCIC_VERSION,
			$bruteVars['code-retour'],
			$bruteVars['cvx'],
			$bruteVars['vld'],
			$bruteVars['brand'],
			$bruteVars['status3ds'],
			(isset($bruteVars['numauto'])?$bruteVars['numauto']:''),
			(isset($bruteVars['motifrefus'])?$bruteVars['motifrefus']:''),
			(isset($bruteVars['originecb'])?$bruteVars['originecb']:''),
			(isset($bruteVars['bincb'])?$bruteVars['bincb']:''),
			(isset($bruteVars['hpancb'])?$bruteVars['hpancb']:''),
			(isset($bruteVars['ipclient'])?$bruteVars['ipclient']:''),
			(isset($bruteVars['originetr'])?$bruteVars['originetr']:''),
			(isset($bruteVars['veres'])?$bruteVars['veres']:''),
			(isset($bruteVars['pares'])?$bruteVars['pares']:'')
		);
		$mac = self::computeHmac($fields, $this->key);
		//trace('$fields = '.$fields);

		if (strtolower($bruteVars['MAC']) == $mac) {
			$macOk = true;
			$reponseRenvoyee = "version=2\ncdr=0\n";
		}
		else {
			$macOk = false;
			$reponseRenvoyee = "version=2\ncdr=1\n";
			//trace('Le MAC ne correspond pas. MAC obtenu : '.$mac);
		}

		$paiementValid = $macOk && ($bruteVars['code-retour'] == "payetest" || $bruteVars['code-retour'] == "paiement");
		$paiementTest = ($bruteVars['code-retour'] == "payetest");

		$currency = substr($bruteVars['montant'], -3);
		$montantTtc   = substr($bruteVars['montant'], 0, -3);

		$resultData = array(
			'reference' 					=> $bruteVars['reference'],
			'currency' 						=> $currency,
			'montant_ttc' 					=> $montantTtc,
			'paiement_valid' 				=> $paiementValid,
			'paiement_test' 				=> $paiementTest,
			'accuse_reception_display' 		=> $reponseRenvoyee,
			'texte_libre' 					=> $bruteVars['texte-libre'],
			'numero_autorisation' 			=> (isset($bruteVars['numauto'])?$bruteVars['numauto']:''),
		);
		// trace('$resultData = '.\My\ArrayList\ArrayHelper::displayStringRecursive($resultData));

		return $resultData;
	}


}
