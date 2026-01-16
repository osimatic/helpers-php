<?php

namespace Osimatic\Bank;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Cette classe permet d'effectuer un paiement par CB via la plateforme PayBox
 * @author Benoit Guiraudou <guiraudou@osimatic.com>
 */
class PayBox
{
	private const string URL_PAIEMENT_TEST = 'https://preprod-ppps.paybox.com/PPPS.php';
	private const string URL_PAIEMENT = 'https://ppps.paybox.com/PPPS.php';
	private const string URL_PAIEMENT_SECOURS = 'https://ppps1.paybox.com/PPPS.php';

	private const string URL_FORM_TEST = 'https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi';
	private const string URL_FORM = 'https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi';
	private const string URL_FORM_SECOURS = 'https://tpeweb1.paybox.com/cgi/MYchoix_pagepaiement.cgi';

	public const int DEFAULT_FORM_TIMEOUT = 1800;

	/**
	 * @var PayBoxVersion
	 */
	private PayBoxVersion $version = PayBoxVersion::PAYBOX_DIRECT_PLUS;

	/**
	 * @var string
	 */
	private string $locale = 'FR';

	/**
	 * @var bool
	 */
	private bool $isTest = false;

	/**
	 * @var bool
	 */
	private bool $useForm = false;


	/**
	 * Date and time of the request
	 * @var \DateTime|null
	 */
	private ?\DateTime $date = null;

	/**
	 * @var BankCardOperation
	 */
	private BankCardOperation $bankCardOperation = BankCardOperation::AUTHORIZATION_AND_DEBIT;

	/**
	 * Unique identifier for the request that allows to avoid confusion in case of multiple	simultaneous requests.
	 * @var int|null
	 */
	private ?int $numQuestion = null;

	/**
	 * Amount of the transaction
	 * @var float
	 */
	private float $montant = 0.;

	/**
	 * Currency code for the transaction.
	 * @var string
	 */
	private string $devise = 'EUR';

	/**
	 * This is the merchant order reference (free field). This allows the merchant to link his platform to the Paybox platform using a reference number.
	 * @var string|null
	 */
	private ?string $reference = null;

	/**
	 * Merchant reference number allowing him to clearly identify the subscriber (profile) that corresponds to the transaction.
	 * @var string|null
	 */
	private ?string $subscriberRef = null;

	/**
	 * @var string|null
	 */
	private ?string $porteurEmail = null;

	/**
	 * PAN (card number) of the customer, without any spaces and left aligned, or subscriber number for the request.
	 * @var string|null
	 */
	private ?string $porteur = null;

	/**
	 * Expiry date of the card
	 * @var \DateTime|null
	 */
	private ?\DateTime $expirationDate = null;

	/**
	 * Visual cryptogram on the back of the card
	 * @var string|null
	 */
	private ?string $cvv = null;

	/**
	 * This parameter allows to inform the acquirer (bank) how the transaction was initiated and how the card entry was realized.
	 * @var BankCardCallOrigin|null
	 */
	private ?BankCardCallOrigin $activite = null;

	/**
	 * This reference is transmitted to the acquirer (bank) of the merchant during the settlement of the transaction. The reference needs to be unique and allows the merchant to inquire for additional information from the acquirer (bank) in case of a dispute.
	 * @var string|null
	 */
	private ?string $archivage = null;

	/**
	 * Number of days to postpone the settlement
	 * @var int|null
	 */
	private ?int $differe = null;

	/**
	 * Version du 3D Secure
	 * @var bool
	 */
	private bool $is3DSecureV2 = false;

	/**
	 * @var string|null
	 */
	private ?string $formCssClass = null;

	/**
	 * @var string|null
	 */
	private ?string $buttonCssClass = null;

	/**
	 * @var string|null
	 */
	private ?string $buttonText = null;

	/**
	 * @var string|null
	 */
	private ?string $urlResponseOk = null;

	/**
	 * @var string|null
	 */
	private ?string $urlResponseRefused = null;

	/**
	 * @var string|null
	 */
	private ?string $urlResponseCanceled = null;

	/**
	 * @var string|null
	 */
	private ?string $urlResponseWaiting = null;

	/**
	 * @var string|null
	 */
	private ?string $urlIpn = null;


	/**
	 * This number is returned by Verifone when a transaction is successfully processed.
	 * @var int|null
	 */
	private ?int $numAppel = null;

	/**
	 * This number is returned by Verifone when a transaction is successfully processed.
	 * @var int|null
	 */
	private ?int $numTransaction = null;

	/**
	 * Authorization number provided by the merchant that was obtained by telephone call to	the acquirer (bank)
	 * @var string|null
	 */
	private ?string $autorisation = null;

	/**
	 * Country code of the issuance of the card
	 * @var string|null
	 */
	private ?string $cardCountryCode = null;

	/**
	 * @var ShoppingCartInterface|null
	 */
	private ?ShoppingCartInterface $shoppingCart = null;

	/**
	 * @var BillingAddressInterface|null
	 */
	private ?BillingAddressInterface $billingAddress = null;

	/**
	 * Paybox system form's expiration in seconds
	 * @var int
	 */
	private int $formTimeout = self::DEFAULT_FORM_TIMEOUT;

	private HTTPClient $httpClient;

	private static array $visaResponseCodes = [
		'00100' => 'Transaction approuvée ou traitée avec succès',
		'00101' => 'Contacter l’émetteur de carte',
		'00102' => 'Contacter l’émetteur de carte',
		'00103' => 'Commerçant invalide',
		'00104' => 'Conserver la carte',
		'00105' => 'Ne pas honorer',
		'00107' => 'Conserver la carte, conditions spéciales',
		'00108' => 'Approuver après identification du porteur',
		'00112' => 'Transaction invalide',
		'00113' => 'Montant invalide',
		'00114' => 'Numéro de porteur invalide',
		'00115' => 'Emetteur de carte inconnu',
		'00117' => 'Annulation client',
		'00119' => 'Répéter la transaction ultérieurement',
		'00120' => 'Réponse erronée (erreur dans le domaine serveur)',
		'00124' => 'Mise à jour de fichier non supportée',
		'00125' => 'Impossible de localiser l’enregistrement dans le fichier',
		'00126' => 'Enregistrement dupliqué, ancien enregistrement remplacé',
		'00127' => 'Erreur en « edit » sur champ de mise à jour fichier',
		'00128' => 'Accès interdit au fichier',
		'00129' => 'Mise à jour de fichier impossible',
		'00130' => 'Erreur de format',
		'00131' => 'Identifiant de l’organisme acquéreur inconnu.',
		'00133' => 'Date de validité de la carte dépassée.',
		'00134' => 'Suspicion de fraude.',
		'00138' => 'Nombre d’essais code confidentiel dépassé',
		'00141' => 'Carte perdue',
		'00143' => 'Carte volée',
		'00151' => 'Provision insuffisante ou crédit dépassé',
		'00154' => 'Date de validité de la carte dépassée',
		'00155' => 'Code confidentiel erroné',
		'00156' => 'Carte absente du fichier',
		'00157' => 'Transaction non permise à ce porteur',
		'00158' => 'Transaction interdite au terminal',
		'00159' => 'Suspicion de fraude',
		'00160' => 'L’accepteur de carte doit contacter l’acquéreur',
		'00161' => 'Dépasse la limite du montant de retrait',
		'00163' => 'Règles de sécurité non respectées',
		'00168' => 'Réponse non parvenue ou reçue trop tard',
		'00175' => 'Nombre d’essais code confidentiel dépassé',
		'00176' => 'Porteur déjà en opposition, ancien enregistrement conservé',
		'00189' => 'Echec de l’authentification',
		'00190' => 'Arrêt momentané du système',
		'00191' => 'Emetteur de cartes inaccessible',
		'00194' => 'Demande dupliquée',
		'00196' => 'Mauvais fonctionnement du système',
		'00197' => 'Echéance de la temporisation de surveillance globale',
		'00198' => 'Serveur inaccessible (positionné par le serveur).',
		'00199' => 'Incident domaine initiateur.',
	];

	private static array $responseCodes = [
		'00000' => 'Opération réussie',
		'00001' => 'Echec de connexion au centre d’autorisation',
		'00002' => 'Une erreur de cohérence est survenue',
		'00003' => 'Erreur Paybox',
		'00004' => 'Numéro de porteur ou cryptogramme visuel invalide',
		'00005' => 'Numéro de question invalide',
		'00006' => 'Accès refusé ou site/rang/identifiant incorrect',
		'00007' => 'Date invalide',
		'00008' => 'Date de fin de validité incorrecte',
		'00009' => 'Type d’opération invalide.',
		'00010' => 'Devise inconnue',
		'00011' => 'Montant incorrect',
		'00012' => 'Référence commande invalide',
		'00013' => 'Cette version n’est plus soutenue',
		'00014' => 'Trame reçue incohérente',
		'00015' => 'Erreur d’accès aux données précédemment référencées.',
		'00016' => 'Abonné déjà existant (inscription nouvel abonné)',
		'00017' => 'Abonné inexistant.',
		'00018' => 'Transaction non trouvée',
		'00020' => 'Cryptogramme visuel non présent',
		'00021' => 'Carte non autorisée',
		'00022' => 'Plafond atteint',
		'00023' => 'Porteur déjà passé aujourd’hui',
		'00024' => 'Code pays filtré pour ce commerçant',
		'00026' => 'Code activité incorrect',
		'00040' => 'Porteur enrôlé mais non authentifié',
		'00097' => 'Timeout de connexion atteint',
		'00098' => 'Erreur de connexion interne',
		'00099' => 'Incohérence entre la question et la réponse. Refaire une nouvelle tentative ultérieurement',
	];

	/**
	 * @param string $siteNumber
	 * @param string $rang
	 * @param string $identifier
	 * @param string $httpPassword
	 * @param string $secretKey
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 */
	public function __construct(
		/**
		 */
		private string $siteNumber = '',

		/**
		 */
		private string $rang = '',

		/**
		 * Unique identifier provided by Paybox, used for Paybox System only ("PBX_IDENTIFIANT" parameter)
		 */
		private string $identifier = '',

		/**
		 * Unique key provided by Paybox and used for Paybox Direct only ("CLE" parameter)
		 */
		private string $httpPassword = '',

		/**
		 * Unique key generated in back-office and used for Paybox System only (HMAC generation)
		 */
		private string $secretKey = '',

		private LoggerInterface $logger=new NullLogger(),
	)
	{
		$this->httpClient = new HTTPClient($logger);
	}

	/**
	 * @return void
	 */
	public function reset(): void
	{
		$this->numQuestion = null;
		$this->date = null;
		$this->reference = null;
		$this->subscriberRef = null;
		$this->porteurEmail = null;
		$this->porteur = null;
		$this->expirationDate = null;
		$this->cvv = null;

		$this->numAppel = null;
		$this->numTransaction = null;
		$this->autorisation = null;
		$this->shoppingCart = null;
		$this->billingAddress = null;
	}

	/**
	 *
	 */
	public function newPayment(): ?PayBoxResponse
	{
		if (false === ($result = $this->doRequest())) {
			return null;
		}
		return $result;
	}

	/**
	 * @return PayBoxResponse|null
	 */
	public function doAuthorization(): ?PayBoxResponse
	{
		$this->bankCardOperation = BankCardOperation::AUTHORIZATION_ONLY;
		return $this->newPayment();
	}

	/**
	 * @return PayBoxResponse|null
	 */
	public function doDebit(): ?PayBoxResponse
	{
		$this->bankCardOperation = BankCardOperation::DEBIT;
		return $this->newPayment();
	}

	/**
	 * @return PayBoxResponse|null
	 */
	public function doAuthorizationAndDebit(): ?PayBoxResponse
	{
		$this->bankCardOperation = BankCardOperation::AUTHORIZATION_AND_DEBIT;
		return $this->newPayment();
	}

	/**
	 * @return PayBoxResponse|null
	 */
	public function addSubscriber(): ?PayBoxResponse
	{
		$this->bankCardOperation = BankCardOperation::REGISTER_SUBSCRIBER;
		return $this->newPayment();
	}

	/**
	 * @return PayBoxResponse|null
	 */
	public function deleteSubscriber(): ?PayBoxResponse
	{
		$this->bankCardOperation = BankCardOperation::DELETE_SUBSCRIBER;
		return $this->newPayment();
	}



	/**
	 * @return string|null
	 */
	public function getFormSubscriberRegister(): ?string
	{
		$this->bankCardOperation = BankCardOperation::REGISTER_SUBSCRIBER;
		$this->useForm = true;

		if (false === ($result = $this->doRequest())) {
			return null;
		}
		return $result;
	}

	/**
	 * @return string|null
	 */
	public function getForm(): ?string
	{
		$this->useForm = true;

		if (false === ($result = $this->doRequest())) {
			return null;
		}
		return $result;
	}



	// ========== Set Request ==========

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
	 * @param PayBoxVersion $version
	 * @return self
	 */
	public function setVersion(PayBoxVersion $version): self
	{
		$this->version = $version;

		return $this;
	}

	/**
	 * @param string $siteNumber
	 * @return self
	 */
	public function setSiteNumber(string $siteNumber): self
	{
		$this->siteNumber = $siteNumber;

		return $this;
	}

	/**
	 * @param string $rang
	 * @return self
	 */
	public function setRang(string $rang): self
	{
		$this->rang = $rang;

		return $this;
	}

	/**
	 * @param string $identifier
	 * @return self
	 */
	public function setIdentifier(string $identifier): self
	{
		$this->identifier = $identifier;

		return $this;
	}

	/**
	 * @param string $httpPassword
	 * @return self
	 */
	public function setHttpPassword(string $httpPassword): self
	{
		$this->httpPassword = $httpPassword;

		return $this;
	}

	/**
	 * @param string $secretKey
	 * @return self
	 */
	public function setSecretKey(string $secretKey): self
	{
		$this->secretKey = $secretKey;

		return $this;
	}

	/**
	 * @param string $locale
	 * @return self
	 */
	public function setLocale(string $locale): self
	{
		$this->locale = $locale;

		return $this;
	}

	/**
	 * @param bool $isTest
	 * @return self
	 */
	public function setIsTest(bool $isTest): self
	{
		$this->isTest = $isTest;

		return $this;
	}



	/**
	 * @param \DateTime|null $date
	 * @return self
	 */
	public function setDate(?\DateTime $date): self
	{
		$this->date = $date;

		return $this;
	}

	/**
	 * @param BankCardOperation $bankCardOperation
	 * @return self
	 */
	public function setBankCardOperation(BankCardOperation $bankCardOperation): self
	{
		$this->bankCardOperation = $bankCardOperation;

		return $this;
	}

	/**
	 * @param int|null $questionNumber
	 * @return self
	 */
	public function setQuestionNumber(?int $questionNumber): self
	{
		$this->numQuestion = $questionNumber;

		return $this;
	}

	/**
	 * @param float $total
	 * @return self
	 */
	public function setTotal(float $total): self
	{
		$this->montant = $total;

		return $this;
	}

	/**
	 * @param float $transactionAmount
	 * @return self
	 */
	public function setTransactionAmount(float $transactionAmount): self
	{
		return $this->setTotal($transactionAmount);
	}

	/**
	 * @param string $currency
	 * @return self
	 */
	public function setCurrency(string $currency): self
	{
		$this->devise = $currency;

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
	 * @param string|null $subscriberRef
	 * @return self
	 */
	public function setSubscriberReference(?string $subscriberRef): self
	{
		$this->subscriberRef = $subscriberRef;

		return $this;
	}

	/**
	 * @param string|null $porteurEmail
	 * @return self
	 */
	public function setCustomerEmail(?string $porteurEmail): self
	{
		$this->porteurEmail = $porteurEmail;

		return $this;
	}

	/**
	 * @param string|null $creditCardNumber
	 * @return self
	 */
	public function setCreditCardNumber(?string $creditCardNumber): self
	{
		$this->porteur = $creditCardNumber;

		return $this;
	}

	/**
	 * @param string|null $creditCardToken
	 * @return self
	 */
	public function setCreditCardToken(?string $creditCardToken): self
	{
		$this->porteur = $creditCardToken;

		return $this;
	}

	/**
	 * @param \DateTime|null $expirationDate
	 * @return self
	 */
	public function setExpirationDate(?\DateTime $expirationDate): self
	{
		$this->expirationDate = $expirationDate;

		return $this;
	}

	/**
	 * @param string|null $cvv
	 * @return self
	 */
	public function setCvc(?string $cvv): self
	{
		$this->cvv = $cvv;

		return $this;
	}

	/**
	 * @param BankCardCallOrigin|null $callOrigin
	 * @return self
	 */
	public function setCallOrigin(?BankCardCallOrigin $callOrigin): self
	{
		$this->activite = $callOrigin;

		return $this;
	}

	/**
	 * @param string|null $archivingReference
	 * @return self
	 */
	public function setArchivingReference(?string $archivingReference): self
	{
		$this->archivage = $archivingReference;

		return $this;
	}

	/**
	 * @param int|null $numberOfDays
	 * @return self
	 */
	public function setNumberOfDaysForPostponedSettlement(?int $numberOfDays): self
	{
		$this->differe = $numberOfDays;

		return $this;
	}

	/**
	 * @param int $callNumber
	 * @return self
	 */
	public function setCallNumber(int $callNumber): self
	{
		$this->numAppel = $callNumber;

		return $this;
	}

	/**
	 * @param int $transactionNumber
	 * @return self
	 */
	public function setTransactionNumber(int $transactionNumber): self
	{
		$this->numTransaction = $transactionNumber;

		return $this;
	}

	/**
	 * @param string $authorizationNumber
	 * @return self
	 */
	public function setAuthorizationNumber(string $authorizationNumber): self
	{
		$this->autorisation = $authorizationNumber;

		return $this;
	}

	/**
	 * @param string|null $formCssClass
	 * @return self
	 */
	public function setFormCssClass(?string $formCssClass): self
	{
		$this->formCssClass = $formCssClass;

		return $this;
	}

	/**
	 * @param string|null $buttonCssClass
	 * @return self
	 */
	public function setButtonCssClass(?string $buttonCssClass): self
	{
		$this->buttonCssClass = $buttonCssClass;

		return $this;
	}

	/**
	 * @param string|null $buttonText
	 * @return self
	 */
	public function setButtonText(?string $buttonText): self
	{
		$this->buttonText = $buttonText;

		return $this;
	}

	/**
	 * @param string|null $urlResponseOk
	 * @return self
	 */
	public function setUrlResponseOk(?string $urlResponseOk): self
	{
		$this->urlResponseOk = $urlResponseOk;

		return $this;
	}

	/**
	 * @param string|null $urlResponseRefused
	 * @return self
	 */
	public function setUrlResponseRefused(?string $urlResponseRefused): self
	{
		$this->urlResponseRefused = $urlResponseRefused;

		return $this;
	}

	/**
	 * @param string|null $urlResponseCanceled
	 * @return self
	 */
	public function setUrlResponseCanceled(?string $urlResponseCanceled): self
	{
		$this->urlResponseCanceled = $urlResponseCanceled;

		return $this;
	}

	/**
	 * @param string|null $urlResponseWaiting
	 * @return self
	 */
	public function setUrlResponseWaiting(?string $urlResponseWaiting): self
	{
		$this->urlResponseWaiting = $urlResponseWaiting;

		return $this;
	}

	/**
	 * @param string|null $urlIpn
	 * @return self
	 */
	public function setUrlIpn(?string $urlIpn): self
	{
		$this->urlIpn = $urlIpn;

		return $this;
	}

	/**
	 * @return self
	 */
	public function setAuthorizationOnly(): self
	{
		$this->bankCardOperation = BankCardOperation::AUTHORIZATION_ONLY;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function is3DSecureV2(): bool
	{
		return $this->is3DSecureV2;
	}

	/**
	 * @return self
	 */
	public function set3DSecureV2(): self
	{
		$this->is3DSecureV2 = true;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getFormTimeout(): int
	{
		return $this->formTimeout;
	}

	/**
	 * @param int $formTimeout
	 * @return self
	 */
	public function setFormTimeout(int $formTimeout): self
	{
		$this->formTimeout = $formTimeout;

		return $this;
	}

	/**
	 * @return BillingAddressInterface|null
	 */
	private function getBillingAddress(): ?BillingAddressInterface
	{
	   return $this->billingAddress;
	}
	
	/**
	 * @param BillingAddressInterface $billingAddress
	 * @return self
	 */
	 public function setBillingAddress(BillingAddressInterface $billingAddress): self
	{
		$this->billingAddress = $billingAddress;

		return $this;
	}

	/**
	 * @return ShoppingCartInterface|null
	 */
	private function getShoppingCart(): ?ShoppingCartInterface
	{
	   return $this->shoppingCart;
	}
	
	/**
	 * @param ShoppingCartInterface $shoppingCart
	 * @return self
	 */
	 public function setShoppingCart(ShoppingCartInterface $shoppingCart): self
	{
		$this->shoppingCart = $shoppingCart;

		return $this;
	}


	// ========== Get Response ==========

	/**
	 * @return int|null
	 */
	public function getCallNumber(): ?int
	{
		return $this->numAppel;
	}

	/**
	 * @return int|null
	 */
	public function getTransactionNumber(): ?int
	{
		return $this->numTransaction;
	}

	/**
	 * @return string|null
	 */
	public function getAuthorizationNumber(): ?string
	{
		return $this->autorisation;
	}

	/**
	 * @return string|null
	 */
	public function getCardCountryCode(): ?string
	{
		return $this->cardCountryCode;
	}


	// ========== Private function ==========


	/**
	 * @return string|null
	 */
	private function getBillingAddressAsXml(): ?string
	{
		if (null === ($billingAddress = $this->getBillingAddress())) {
			return null;
		}

		return '<?xml version="1.0" encoding="utf-8"?><Billing>'
			.'<Address><FirstName>'.($billingAddress->getFirstName() ?? '-').'</FirstName><LastName>'.($billingAddress->getLastName() ?? $billingAddress->getCompanyName()).'</LastName><Address1>'.$billingAddress->getStreet().'</Address1>'
			.'<Address2>'.$billingAddress->getStreet2().'</Address2><ZipCode>'.$billingAddress->getZipCode().'</ZipCode><City>'.$billingAddress->getCity().'</City><CountryCode>'.\Osimatic\Location\Country::getCountryNumericCodeFromCountryCode($billingAddress->getCountryCode()).'</CountryCode></Address>'
			.'</Billing>';
	}

	/**
	 * @return string|null
	 */
	private function getShoppingCartAsXml(): ?string
	{
		if (null === ($shoppingCart = $this->getShoppingCart())) {
			return null;
		}

		return '<?xml version="1.0" encoding="utf-8"?><shoppingcart><total><totalQuantity>'.$shoppingCart->getTotalQuantity().'</totalQuantity></total></shoppingcart>';
	}


	/**
	 * @return PayBoxResponse|bool|string|null
	 */
	private function doRequest(): PayBoxResponse|bool|string|null
	{
		if ($this->isTest) {
			$this->version = PayBoxVersion::PAYBOX_DIRECT_PLUS;
			$this->siteNumber = '1999888';
			$this->identifier = '109518543';
			$this->httpPassword = '1999888I';
			$this->secretKey = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';
			$this->rang = '63';
		}

		if (empty($this->siteNumber) || strlen($this->siteNumber) !== 7) {
			$this->logger?->error('Numéro de TPE invalide : ' . $this->siteNumber);
			return false;
		}

		if (empty($this->rang) || strlen($this->rang) < 2 || strlen($this->rang) > 3) {
			$this->logger?->error('Numéro de rang invalide : ' . $this->rang);
			return false;
		}

		if ($this->useForm) {
			if (!$this->_checkIdentifier()) {
				$this->logger?->error('Identifiant PayBox invalide : ' . $this->identifier);
				return false;
			}

			if (!$this->_checkSecretKey()) {
				$this->logger?->error('Clé secrete invalide : ' . $this->secretKey);
				return false;
			}
		}
		else {
			if (!$this->_checkHttpPassword()) {
				$this->logger?->error('Mot de passe PayBox invalide : ' . $this->httpPassword);
				return false;
			}
		}

		$this->numQuestion = (empty($this->numQuestion) ? date('His') . random_int(1, 999) : $this->numQuestion);
		if (!is_numeric($this->numQuestion)) {
			$this->logger?->error('Num question invalide : ' . $this->numQuestion);
			return false;
		}

		if (!in_array($this->bankCardOperation, [BankCardOperation::UPDATE_SUBSCRIBER, BankCardOperation::DELETE_SUBSCRIBER], true)) {
			if (empty($this->montant)) {
				$this->logger?->error('Montant invalide : ' . $this->montant);
				return false;
			}

			if (empty($this->reference) || strlen($this->reference) > 250) {
				$this->logger?->error('Référence invalide.');
				return false;
			}
		}

		if (in_array($this->bankCardOperation, [BankCardOperation::UPDATE_SUBSCRIBER, BankCardOperation::DELETE_SUBSCRIBER], true)) {
			if (empty($this->subscriberRef)) {
				$this->logger?->error('Référence abonné vide.');
				return false;
			}
		}

		if (!empty($this->subscriberRef) && strlen($this->subscriberRef) > 250) {
			$this->logger?->error('Référence abonné invalide.');
			return false;
		}

		$isCaptureAfterAuthorization = in_array($this->bankCardOperation, [BankCardOperation::DEBIT, BankCardOperation::CANCEL], true);

		if ($this->useForm) {
			// Utilisation Paybox System (formulaire de paiement sur la plateforme Paybox)
			if (empty($this->porteurEmail) || strlen($this->porteurEmail) < 6 || strlen($this->porteurEmail) > 120 || !filter_var($this->porteurEmail, FILTER_VALIDATE_EMAIL)) {
				$this->logger?->error('Email porteur invalide.');
				return false;
			}
		} else {
			// Utilisation Paybox Direct (formulaire de paiement coté client et appel de la plateforme Paybox via requete HTTP)

			if (!empty($this->subscriberRef)) {
				// Utilisation système abonné (via le token de la carte)

				if (empty($this->porteur)) {
					$this->logger?->error('Token porteur vide.');
					return false;
				}
			} else {
				// Utilisation système classique (saisie de la carte)

				if (!empty($this->porteur) && strlen($this->porteur) > 19) {
					$this->logger?->error('Numéro carte invalide.');
					return false;
				}

				if (!empty($this->cvv) && (strlen($this->cvv) < 3 || strlen($this->cvv) > 4)) {
					$this->logger?->error('Cryptogramme visuel invalide.');
					return false;
				}
			}

			if (empty($this->getExpirationDateFormatted())) {
				$this->logger?->error('Date validité invalide.');
				return false;
			}
		}

		if (!$this->useForm && $isCaptureAfterAuthorization) {
			// if (empty($this->numAppel) || strlen($this->numAppel) != 10) {
			if (empty($this->numAppel)) {
				$this->logger?->error('Numéro appel invalide.');
				return false;
			}

			// if (empty($this->numTransaction) || strlen($this->numTransaction) != 10) {
			if (empty($this->numTransaction)) {
				$this->logger?->error('Numéro appel invalide.');
				return false;
			}

			$this->porteur = null;
		}

		// ---------- Méthode form (PayBox System) ----------
		if ($this->useForm) {
			return $this->getHtml();
		}

		// ---------- Méthode HTTP (PayBox Direct) ----------
		if (null === ($payboxResponse = $this->doHttpRequestToPayBox())) {
			return false;
		}
		return $payboxResponse;
	}

	/**
	 * @return PayBoxResponse|null
	 */
	private function doHttpRequestToPayBox(): ?PayBoxResponse
	{
		$urlPaiement = ($this->isTest ? self::URL_PAIEMENT_TEST : self::URL_PAIEMENT);

		$postData = [
			'VERSION' => $this->version->value,
			'TYPE' => $this->getTypeOperationFormatted(),
			'SITE' => $this->siteNumber,
			'RANG' => $this->rang,
			'CLE' => $this->httpPassword,

			'NUMQUESTION' => $this->numQuestion,
			'MONTANT' => $this->getFormattedAmount(),
			'DEVISE' => $this->getCurrencyCode(),
			'REFERENCE' => $this->reference,
			'REFABONNE' => $this->subscriberRef,

			'PORTEUR' => $this->porteur,
			'DATEVAL' => $this->getExpirationDateFormatted(),
			'CVV' => $this->cvv,

			'DATEQ' => date('dmYHis', $this->getTimestamp()),

			'ACTIVITE' => $this->getActiviteCode(),
			'ARCHIVAGE' => $this->archivage,
			'DIFFERE' => $this->differe,
			'NUMAPPEL' => $this->numAppel,
			'NUMTRANS' => $this->numTransaction,
			'AUTORISATION' => $this->autorisation,
			'PAYS' => '', // champ vide permettant le renvoi du code pays de la carte dans la réponse
		];

		foreach ($postData as $cleVar => $value) {
			$postData[$cleVar] = trim($value ?? '');
		}
		$queryString = http_build_query($postData);

		// Log
		$this->logger?->info('URL Paiement Paybox : ' . $urlPaiement);
		$this->logger?->info('QueryString envoyée : ' . $queryString);
		$this->logger?->info('Référence achat : ' . $postData['REFERENCE']);

		// Appel de l'URL Paybox avec les arguments POST
		$res = $this->httpClient->stringRequest(HTTPMethod::POST, $urlPaiement, queryData: $postData);

		if (null === $res) {
			$this->logger?->error('Appel Paybox échoué');
			return null;
		}

		$this->logger?->info('Résultat appel Paybox : ' . $res);

		// Récupération des arguments retour
		parse_str($res, $tabArg);
		$responseCode = $tabArg['CODEREPONSE'] ?? '';

		$responseCodes = array_merge(self::$responseCodes, self::$visaResponseCodes);

		if ($responseCode !== '00000') {
			$this->logger?->error('Response code: ' . $responseCode.' ; Error message: ' . ($responseCodes[$responseCode] ?? 'Erreur inconnue'));
			return null;
		}

		$this->logger?->info('Response code: ' . $responseCode.' (Opération réussie)');

		$payBoxResponse = new PayBoxResponse();
		$payBoxResponse->setReference(!empty($tabArg['REFERENCE']) ? urldecode($tabArg['REFERENCE']) : null);
		$payBoxResponse->setResponseCode(!empty($responseCode) ? urldecode($responseCode) : null);
		$payBoxResponse->setAuthorisationNumber(!empty($tabArg['AUTORISATION']) ? urldecode($tabArg['AUTORISATION']) : null);
		$payBoxResponse->setCallNumber(!empty($tabArg['NUMAPPEL']) ? urldecode($tabArg['NUMAPPEL']) : null);
		$payBoxResponse->setTransactionNumber(!empty($tabArg['NUMTRANS']) ? urldecode($tabArg['NUMTRANS']) : null);
		$payBoxResponse->setCardNumber(!empty($tabArg['PORTEUR']) ? urldecode($tabArg['PORTEUR']) : null);
		$payBoxResponse->setCardHash(!empty($tabArg['REFABONNE']) ? urldecode($tabArg['REFABONNE']) : null);
		$payBoxResponse->setCardType(!empty($tabArg['TYPECARTE']) ? urldecode($tabArg['TYPECARTE']) : null);
		// var non utilisé : $tabArg['NUMQUESTION'] ; $tabArg['SHA-1']

		return $payBoxResponse;
	}

	private function getHtml(): string
	{
		//variables demandées par PayBox
		$pbxVars = [
			'PBX_SITE' => $this->siteNumber,
			'PBX_RANG' => $this->rang,
			'PBX_IDENTIFIANT' => $this->identifier,
			'PBX_LANGUE' => $this->getLanguageCode(),
			'PBX_TOTAL' => $this->getAmount(),
			'PBX_DEVISE' => $this->getCurrencyCode(),
			'PBX_CMD' => $this->reference,
			'PBX_PORTEUR' => $this->porteurEmail,
			'PBX_REFABONNE' => $this->subscriberRef,
			'PBX_RETOUR' => $this->getReturnedVars(),
			'PBX_HASH' => 'SHA512',
			'PBX_TIME' => date('c', $this->getTimestamp()),
			'PBX_AUTOSEULE' => (in_array($this->bankCardOperation, [BankCardOperation::AUTHORIZATION_ONLY, BankCardOperation::REGISTER_SUBSCRIBER], true) ? 'O' : 'N'),
			'PBX_REPONDRE_A' => $this->urlIpn,
			'PBX_RUF1' => 'POST',
			'PBX_EFFECTUE' => $this->urlResponseOk,
			'PBX_REFUSE' => $this->urlResponseRefused,
			'PBX_ANNULE' => $this->urlResponseCanceled,
			'PBX_ATTENTE' => $this->urlResponseWaiting,
			'PBX_TYPEPAIEMENT' => 'CARTE',
			'PBX_TYPECARTE' => 'CB',
			'PBX_DISPLAY' => $this->getFormTimeout(),
		];

		if ($this->is3DSecureV2()) {
			$pbxVars['PBX_SHOPPINGCART'] = $this->getShoppingCartAsXml();
			$pbxVars['PBX_BILLING'] = $this->getBillingAddressAsXml();
		}

		// Calcul du HMAC
		$hmac = $this->getHmac($pbxVars);

		// Construction HTML
		$form = '<form method="POST" action="' . ($this->isTest ? self::URL_FORM_TEST : self::URL_FORM) . '" class="' . ($this->formCssClass ?? '') . '">';
		
		foreach ($pbxVars as $index => $value) {
			$form .= '<input type="hidden" name="'.$index.'" value="' . ($index === 'PBX_SHOPPINGCART' || $index === 'PBX_BILLING' ? htmlspecialchars($value) : $value) . '">';
		}

		$form .= '<input type="hidden" name="PBX_HMAC" value="' . $hmac . '">'
			. '<input type="submit" class="' . ($this->buttonCssClass ?? 'btn btn-primary') . '" value="' . $this->buttonText . '">'
			. '</form>';

		return $form;
	}

	private function getTypeOperationFormatted(): string
	{
		if (!empty($this->subscriberRef)) {
			if ($this->bankCardOperation === BankCardOperation::AUTHORIZATION_ONLY) {
				return '00051';
			}
			if ($this->bankCardOperation === BankCardOperation::DEBIT) {
				return '00052';
			}
			if ($this->bankCardOperation === BankCardOperation::AUTHORIZATION_AND_DEBIT) {
				return '00053';
			}
			if ($this->bankCardOperation === BankCardOperation::CREDIT) {
				return '00054';
			}
			if ($this->bankCardOperation === BankCardOperation::CANCEL) {
				return '00055';
			}
		}

		return match($this->bankCardOperation) {
			BankCardOperation::AUTHORIZATION_ONLY => '00001',
			BankCardOperation::DEBIT => '00002',
			BankCardOperation::AUTHORIZATION_AND_DEBIT => '00003',
			BankCardOperation::CREDIT => '00004',
			BankCardOperation::CANCEL => '00005',
			BankCardOperation::REFUND => '00014',
			BankCardOperation::REGISTER_SUBSCRIBER => '00056',
			BankCardOperation::UPDATE_SUBSCRIBER => '00057',
			BankCardOperation::DELETE_SUBSCRIBER => '00058',
		};
	}

	private function getReturnedVars(): string
	{
		// La longueur total de $returnedVars ne doit pas exéder 150 caractères (sinon erreur PayBox)
		//$returnedVars = 'amount:M;reference:R;authorization_number:A;call_number:T;transaction_number:S;card_last_digits:J;card_expiry_date:D;response_code:E;3d_secure_authentication:F;3d_secure_enabled:G;3d_secure_version:v';
		$returnedVars = 'amount:M;ref:R;authorizt_nb:A;call_nb:T;transact_nb:S;bc_type:C;bc_ldigit:J;bc_expdate:D;response_code:E;3ds:G;3ds_auth:F;3ds_v:v';
		if ($this->bankCardOperation === BankCardOperation::REGISTER_SUBSCRIBER) {
			$returnedVars .= ';card_ref:U;bin6:N';
		}

		return $returnedVars;
	}

	private function getTimestamp(): int
	{
		return ($this->date !== null ? $this->date->getTimestamp() : time());
	}

	private function _checkHttpPassword(): bool
	{
		return !empty($this->httpPassword) && strlen($this->httpPassword) >= 8 && strlen($this->httpPassword) <= 10;
	}

	private function _checkIdentifier(): bool
	{
		return !empty($this->identifier) && strlen($this->identifier) >= 1 && strlen($this->identifier) <= 9;
	}

	private function _checkSecretKey(): bool
	{
		return !empty($this->secretKey) && strlen($this->secretKey) === 128;
	}

	private function getHmac($varsList): string
	{
		$vars = [];

		foreach ($varsList as $key => $value) {
			$vars[] = $key . '=' . $value;
		}

		$msg = implode('&', $vars);
		$binKey = pack('H*', $this->secretKey);
		
		return strtoupper(hash_hmac('sha512', $msg, $binKey));
	}

	private function getLanguageCode(): string
	{
		if (in_array($this->locale, ['en', 'en_GB', 'en-GB', 'GB', 'UK'], true)) {
			return 'GBR'; // Anglais
		}
		if (in_array($this->locale, ['es', 'es_ES', 'es-ES', 'ES'], true)) {
			return 'ESP'; // Espagnol
		}
		if (in_array($this->locale, ['pt', 'pt_PT', 'pt-PT', 'PT'], true)) {
			return 'PRT'; // Portugais
		}
		if (in_array($this->locale, ['it', 'it_IT', 'it-IT', 'IT'], true)) {
			return 'ITA'; // Italien
		}
		if (in_array($this->locale, ['de', 'de_DE', 'de-DE', 'DE'], true)) {
			return 'DEU'; // Allemand
		}
		if (in_array($this->locale, ['nl', 'nl_NL', 'nl-NL', 'NL'], true)) {
			return 'NLD'; // Néerlandais
		}
		if (in_array($this->locale, ['sv_SE', 'sv-SE', 'SE'], true)) {
			return 'SWE'; // Suédois
		}
		return 'FRA';
	}

	private function getActiviteCode(): ?string
	{
		if (null === $this->activite) {
			return null;
		}
		
		return match ($this->activite) {
			BankCardCallOrigin::NOT_SPECIFIED => '020',
			BankCardCallOrigin::TELEPHONE_ORDER => '021',
			BankCardCallOrigin::MAIL_ORDER => '022',
			BankCardCallOrigin::MINITEL => '023',
			BankCardCallOrigin::INTERNET_PAYMENT => '024',
			BankCardCallOrigin::RECURRING_PAYMENT => '027',
		};
	}

	private function getAmount(): int
	{
		return round($this->montant * 100, 2);
	}

	private function getFormattedAmount(): ?string
	{
		$montantFormate = (string) $this->getAmount();
		return str_pad($montantFormate, 10, '0', STR_PAD_LEFT);
	}

	private function getCurrencyCode(): ?int
	{
		return Currency::getNumericCode($this->devise);
	}

	private function getExpirationDateFormatted(): ?string
	{
		if (null !== $this->expirationDate) {
			return $this->expirationDate->format('my');
		}
		/*if (strlen($this->dateValidite) === 10) { // format yyyy-mm-dd
			return substr($this->dateValidite, 5, 2) . substr($this->dateValidite, 2, 2);
		}
		if (strlen($this->dateValidite) === 7) { // format mm/yyyy
			return substr($this->dateValidite, 0, 2) . substr($this->dateValidite, -2);
		}
		if (strlen($this->dateValidite) === 4) { // format mmyy
			return substr($this->dateValidite, 0, 2) . substr($this->dateValidite, -2);
		}*/
		return null;
	}

}
