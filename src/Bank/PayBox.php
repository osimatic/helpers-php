<?php

namespace Osimatic\Helpers\Bank;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\Http\Message\ResponseInterface;

/**
 * Cette classe permet d'effectuer un paiement par CB via la plateforme PayBox
 * @author Benoit Guiraudou <guiraudou@osimatic.com>
 */
class PayBox
{
	const URL_PAIEMENT_TEST = 'https://preprod-ppps.paybox.com/PPPS.php';
	const URL_PAIEMENT = 'https://ppps.paybox.com/PPPS.php';
	const URL_PAIEMENT_SECOURS = 'https://ppps1.paybox.com/PPPS.php';

	const URL_FORM_TEST = 'https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi';
	const URL_FORM = 'https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi';
	const URL_FORM_SECOURS = 'https://tpeweb1.paybox.com/cgi/MYchoix_pagepaiement.cgi';

	const VERSION_PAYBOX_DIRECT = '00103';
	const VERSION_PAYBOX_DIRECT_PLUS = '00104';

	const CALL_ORIGIN_NOT_SPECIFIED = '020';
	const CALL_ORIGIN_TELEPHONE_ORDER = '021';
	const CALL_ORIGIN_MAIL_ORDER = '022';
	const CALL_ORIGIN_MINITEL = '023';
	const CALL_ORIGIN_INTERNET_PAYMENT = '024';
	const CALL_ORIGIN_RECURRING_PAYMENT = '027';

	/**
	 * Ces constantes sont internes, ne pas utiliser de l'extérieur
	 */
	const TYPE_OPERATION_AUTORISATION_SEULE = '00001';
	const TYPE_OPERATION_DEBIT = '00002';
	const TYPE_OPERATION_AUTORISATION_AND_DEBIT = '00003';
	const TYPE_OPERATION_CREDIT = '00004';
	const TYPE_OPERATION_ANNULATION = '00005';
	const TYPE_OPERATION_AUTORISATION_SEULE_ABONNE = '00051';
	const TYPE_OPERATION_DEBIT_ABONNE = '00052';
	const TYPE_OPERATION_AUTORISATION_AND_DEBIT_ABONNE = '00053';
	const TYPE_OPERATION_CREDIT_ABONNE = '00054';
	const TYPE_OPERATION_ANNULATION_ABONNE = '00055';
	const TYPE_OPERATION_INSCRIPTION_ABONNE = '00056';
	const TYPE_OPERATION_SUPPRESSION_ABONNE = '00058';

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var string
	 */
	private $numSite;

	/**
	 * @var string
	 */
	private $identifier;

	/**
	 * @var string
	 */
	private $httpPassword;

	/**
	 * @var string
	 */
	private $secretKey;

	/**
	 * @var string
	 */
	private $rang;

	/**
	 * @var string
	 */
	private $locale = 'FR';

	/**
	 * @var bool
	 */
	private $isTest = false;

	/**
	 * @var bool
	 */
	private $useForm = false;


	/**
	 * Date and time of the request
	 * @var \DateTime|null
	 */
	private $date;

	/**
	 * @var string
	 */
	private $typeQuestion;

	/**
	 * Unique identifier for the request that allows to avoid confusion in case of multiple	simultaneous requests.
	 * @var int
	 */
	private $numQuestion;

	/**
	 * Amount of the transaction
	 * @var float
	 */
	private $montant;

	/**
	 * Currency code for the transaction.
	 * @var string
	 */
	private $devise = 'EUR';

	/**
	 * This is the merchant order reference (free field). This allows the merchant to link his platform to the Paybox platform using a reference number.
	 * @var string
	 */
	private $reference;

	/**
	 * Merchant reference number allowing him to clearly identify the subscriber (profile) that corresponds to the transaction.
	 * @var string
	 */
	private $subscriberRef;

	/**
	 * @var string
	 */
	private $porteurEmail;

	/**
	 * PAN (card number) of the customer, without any spaces and left aligned, or subscriber number for the request.
	 * @var string
	 */
	private $porteur;

	/**
	 * Expiry date of the card
	 * @var string
	 */
	private $dateValidite;

	/**
	 * Visual cryptogram on the back of the card
	 * @var string
	 */
	private $cvv;

	/**
	 * This parameter allows to inform the acquirer (bank) how the transaction was initiated and how the card entry was realized.
	 * @var string
	 */
	private $activite;

	/**
	 * This reference is transmitted to the acquirer (bank) of the merchant during the settlement of the transaction. The reference needs to be unique and allows the merchant to inquire for additional information from the acquirer (bank) in case of a dispute.
	 * @var string
	 */
	private $archivage;

	/**
	 * Number of days to postpone the settlement
	 * @var int
	 */
	private $differe;

	/**
	 * @var string
	 */
	private $formCssClass;

	/**
	 * @var string
	 */
	private $buttonCssClass;

	/**
	 * @var string
	 */
	private $buttonText;

	/**
	 * @var string
	 */
	private $urlResponseOk;

	/**
	 * @var string
	 */
	private $urlResponseRefused;

	/**
	 * @var string
	 */
	private $urlResponseCanceled;

	/**
	 * @var string
	 */
	private $urlResponseWaiting;

	/**
	 * @var string
	 */
	private $urlIpn;


	/**
	 * This number is returned by Verifone when a transaction is successfully processed.
	 * @var int
	 */
	private $numAppel;

	/**
	 * This number is returned by Verifone when a transaction is successfully processed.
	 * @var int
	 */
	private $numTransaction;

	/**
	 * Authorization number provided by the merchant that was obtained by telephone call to	the acquirer (bank)
	 * @var string
	 */
	private $autorisation;

	/**
	 * Country code of the issuance of the card
	 * @var string
	 */
	private $pays;

	/**
	 * @var string
	 */
	private $codeReponse;

	/**
	 * @var string
	 */
	private $libelleReponse;


	private static $visaResponseCodes = [
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

	private static $responseCodes = [
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

	public function __construct()
	{
		$this->logger = new NullLogger();
	}

	/**
	 *
	 */
	public function newPayment(): void
	{
		$this->codeReponse = '99999';
	}

	/**
	 * @return array|null
	 */
	public function doAuthorization(): ?array
	{
		$this->typeQuestion = self::TYPE_OPERATION_AUTORISATION_SEULE;

		if (false === ($result = $this->doRequest())) {
			return null;
		}
		return $result;
	}

	/**
	 * @return array|null
	 */
	public function doDebit(): ?array
	{
		$this->typeQuestion = self::TYPE_OPERATION_DEBIT;

		if (false === ($result = $this->doRequest())) {
			return null;
		}
		return $result;
	}

	/**
	 * @return array|null
	 */
	public function doAuthorizationAndDebit(): ?array
	{
		$this->typeQuestion = self::TYPE_OPERATION_AUTORISATION_AND_DEBIT;

		if (false === ($result = $this->doRequest())) {
			return null;
		}
		return $result;
	}

	/**
	 * @return array|null
	 */
	public function addSubscriber(): ?array
	{
		$this->typeQuestion = self::TYPE_OPERATION_INSCRIPTION_ABONNE;

		if (false === ($result = $this->doRequest())) {
			return null;
		}
		return $result;
	}

	/**
	 * @return array|null
	 */
	public function deleteSubscriber(): ?array
	{
		$this->typeQuestion = self::TYPE_OPERATION_SUPPRESSION_ABONNE;

		if (false === ($result = $this->doRequest())) {
			return null;
		}
		return $result;
	}



	/**
	 * @return string|null
	 */
	public function getFormSubscriberRegister(): ?string
	{
		$this->typeQuestion = self::TYPE_OPERATION_INSCRIPTION_ABONNE;
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
	 * Set the logger to use to log debugging data.
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * @param string $version
	 * @return self
	 */
	public function setVersion(string $version): self
	{
		$this->version = $version;

		return $this;
	}

	/**
	 * @param string $numSite
	 * @return self
	 */
	public function setNumSite(string $numSite): self
	{
		$this->numSite = $numSite;

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
	 * @param string $rang
	 * @return self
	 */
	public function setRang(string $rang): self
	{
		$this->rang = $rang;

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
	 * @param int $questionNumber
	 * @return self
	 */
	public function setQuestionNumber(int $questionNumber): self
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
	 * @param $subscriberRef
	 * @return self
	 */
	public function setSubscriberReference($subscriberRef): self
	{
		$this->subscriberRef = $subscriberRef;

		return $this;
	}

	/**
	 * @param string $porteurEmail
	 * @return self
	 */
	public function setCustomerEmail(string $porteurEmail): self
	{
		$this->porteurEmail = $porteurEmail;

		return $this;
	}

	/**
	 * @param string $creditCardNumber
	 * @return self
	 */
	public function setCreditCardNumber(string $creditCardNumber): self
	{
		$this->porteur = $creditCardNumber;

		return $this;
	}

	/**
	 * @param string $creditCardToken
	 * @return self
	 */
	public function setCreditCardToken(string $creditCardToken): self
	{
		$this->porteur = $creditCardToken;

		return $this;
	}

	/**
	 * @param string $dateValidite
	 * @return self
	 */
	public function setExpirationDate(string $dateValidite): self
	{
		$this->dateValidite = $dateValidite;

		return $this;
	}

	/**
	 * @param $cvv
	 * @return self
	 */
	public function setCvc($cvv): self
	{
		$this->cvv = $cvv;

		return $this;
	}

	/**
	 * @param string $callOrigin
	 * @return self
	 */
	public function setCallOrigin(string $callOrigin): self
	{
		$this->activite = $callOrigin;

		return $this;
	}

	/**
	 * @param string $archivingReference
	 * @return self
	 */
	public function setArchivingReference(string $archivingReference): self
	{
		$this->archivage = $archivingReference;

		return $this;
	}

	/**
	 * @param int $numberOfDays
	 * @return self
	 */
	public function setNumberOfDaysForPostponedSettlement(int $numberOfDays): self
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
		$this->typeQuestion = self::TYPE_OPERATION_AUTORISATION_SEULE;

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
		return $this->pays;
	}

	/**
	 * @return string|null
	 */
	public function getResponseCode(): ?string
	{
		return $this->codeReponse;
	}

	/**
	 * @return string|null
	 */
	public function getResponseMessage(): ?string
	{
		return $this->libelleReponse;
	}



	// ========== Private function ==========

	/**
	 * @return array|bool|string
	 */
	private function doRequest()
	{
		$this->version = (empty($this->version) ? self::VERSION_PAYBOX_DIRECT_PLUS : $this->version);
		if (!in_array($this->version, [self::VERSION_PAYBOX_DIRECT, self::VERSION_PAYBOX_DIRECT_PLUS], true)) {
			$this->logger ? $this->logger->error('Version invalide : ' . $this->version) : null;
			return false;
		}

		if ($this->isTest) {
			$this->version = self::VERSION_PAYBOX_DIRECT_PLUS;
			$this->numSite = '1999888';
			$this->identifier = '2';
			$this->httpPassword = '1999888I';
			$this->secretKey = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';
			$this->rang = '32';
			//if (!$this->useForm) {
			//	$this->rang = '63';
			//}
		}

		if (empty($this->numSite) || strlen($this->numSite) !== 7) {
			$this->logger ? $this->logger->error('Numéro de TPE invalide : ' . $this->numSite) : null;
			return false;
		}

		if (empty($this->rang) || strlen($this->rang) < 2 || strlen($this->rang) > 3) {
			$this->logger ? $this->logger->error('Numéro de rang invalide : ' . $this->rang) : null;
			return false;
		}

		if ($this->useForm) {
			if (!$this->_checkIdentifier()) {
				$this->logger ? $this->logger->error('Identifiant PayBox invalide : ' . $this->identifier) : null;
				return false;
			}
		} else {
			if (!$this->_checkHttpPassword()) {
				$this->logger ? $this->logger->error('Mot de passe PayBox invalide : ' . $this->httpPassword) : null;
				return false;
			}
		}
		if (!$this->_checkSecretKey()) {
			$this->logger ? $this->logger->error('Clé secrete invalide : ' . $this->secretKey) : null;
			return false;
		}

		$this->typeQuestion = (empty($this->typeQuestion) ? self::TYPE_OPERATION_AUTORISATION_AND_DEBIT : $this->typeQuestion);

		$this->numQuestion = (empty($this->numQuestion) ? date('His') . mt_rand(1, 999) : $this->numQuestion);
		if (!is_numeric($this->numQuestion)) {
			$this->logger ? $this->logger->error('Num question invalide : ' . $this->numQuestion) : null;
			return false;
		}

		if ($this->typeQuestion !== self::TYPE_OPERATION_SUPPRESSION_ABONNE) {
			if (empty($this->montant)) {
				$this->logger ? $this->logger->error('Montant invalide : ' . $this->montant) : null;
				return false;
			}

			if (empty($this->reference) || strlen($this->reference) > 250) {
				$this->logger ? $this->logger->error('Référence invalide.') : null;
				return false;
			}
		}

		if ($this->typeQuestion === self::TYPE_OPERATION_SUPPRESSION_ABONNE) {
			if (empty($this->subscriberRef)) {
				$this->logger ? $this->logger->error('Référence abonné vide.') : null;
				return false;
			}
		}

		if (!empty($this->subscriberRef) && strlen($this->subscriberRef) > 250) {
			$this->logger ? $this->logger->error('Référence abonné invalide.') : null;
			return false;
		}

		$isCaptureAfterAuthorization = in_array($this->typeQuestion, [self::TYPE_OPERATION_DEBIT, self::TYPE_OPERATION_ANNULATION], true);

		if ($this->useForm) {
			// Utilisation Paybox System (formulaire de paiement sur la plateforme Paybox)
			if (empty($this->porteurEmail) || strlen($this->porteurEmail) < 6 || strlen($this->porteurEmail) > 120 || !filter_var($this->porteurEmail, FILTER_VALIDATE_EMAIL)) {
				$this->logger ? $this->logger->error('Email porteur invalide.') : null;
				return false;
			}
		} else {
			// Utilisation Paybox Direct (formulaire de paiement coté client et appel de la plateforme Paybox via requete HTTP)

			if (!empty($this->subscriberRef)) {
				// Utilisation système abonné (via le token de la carte)

				if (empty($this->porteur)) {
					$this->logger ? $this->logger->error('Token porteur vide.') : null;
					return false;
				}
			} else {
				// Utilisation système classique (saisie de la carte)

				if (!empty($this->porteur) && strlen($this->porteur) > 19) {
					$this->logger ? $this->logger->error('Numéro carte invalide.') : null;
					return false;
				}

				if (!empty($this->cvv) && (strlen($this->cvv) < 3 || strlen($this->cvv) > 4)) {
					$this->logger ? $this->logger->error('Cryptogramme visuel invalide.') : null;
					return false;
				}
			}

			if (empty($this->getExpirationDateFormatted())) {
				$this->logger ? $this->logger->error('Date validité invalide.') : null;
				return false;
			}
		}

		if (!$this->useForm && $isCaptureAfterAuthorization) {
			// if (empty($this->numAppel) || strlen($this->numAppel) != 10) {
			if (empty($this->numAppel)) {
				$this->logger ? $this->logger->error('Numéro appel invalide.') : null;
				return false;
			}

			// if (empty($this->numTransaction) || strlen($this->numTransaction) != 10) {
			if (empty($this->numTransaction)) {
				$this->logger ? $this->logger->error('Numéro appel invalide.') : null;
				return false;
			}

			$this->porteur = null;
		}

		// ---------- Méthode form (PayBox System) ----------
		if ($this->useForm) {
			return $this->getHtml();
		}

		// ---------- Méthode HTTP (PayBox Direct) ----------
		if (empty($data = $this->doHttpRequestToPayBox())) {
			return false;
		}
		return $data;
	}

	/**
	 * @return array|null
	 */
	private function doHttpRequestToPayBox(): ?array
	{
		$urlPaiement = ($this->isTest ? self::URL_PAIEMENT_TEST : self::URL_PAIEMENT);

		$postData = [
			'VERSION' => $this->version,
			'TYPE' => $this->getTypeOperationFormatted(),
			'SITE' => $this->numSite,
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

			'ACTIVITE' => $this->activite,
			'ARCHIVAGE' => $this->archivage,
			'DIFFERE' => $this->differe,
			'NUMAPPEL' => $this->numAppel,
			'NUMTRANS' => $this->numTransaction,
			'AUTORISATION' => $this->autorisation,
			'PAYS' => $this->pays,
		];

		foreach ($postData as $cleVar => $value) {
			if ($value === null) {
				$postData[$cleVar] = '';
			} else {
				$postData[$cleVar] = trim($value);
			}
		}

		$queryString = http_build_query($postData);

		// Log
		$this->logger ? $this->logger->info('URL Paiement Paybox : ' . $urlPaiement) : null;
		$this->logger ? $this->logger->info('QueryString envoyée : ' . $queryString) : null;
		$this->logger ? $this->logger->info('Référence achat : ' . $postData['REFERENCE']) : null;

		// Appel de l'URL Paybox avec les arguments POST
		$res = self::post($urlPaiement, $postData);

		if (false === $res) {
			$this->logger ? $this->logger->info('Appel Paybox échoué') : null;
			return null;
		}

		$res = (string)$res->getBody();
		$this->logger ? $this->logger->info('Résultat appel Paybox : ' . $res) : null;

		// Récupération des arguments retour
		parse_str($res, $tabArg);
		$this->codeReponse = $tabArg['CODEREPONSE'] ?? '';

		$responseCodes = array_merge(self::$responseCodes, self::$visaResponseCodes);
		if (array_key_exists($this->codeReponse, $responseCodes)) {
			$this->libelleReponse = $responseCodes[$this->codeReponse];
		} else {
			$this->libelleReponse = 'Erreur inconnue';
		}

		// Log réponse
		$this->logger ? $this->logger->info('Code réponse : ' . $this->codeReponse) : null;
		$this->logger ? $this->logger->info('Libellé réponse : ' . $this->libelleReponse) : null;

		$paiementOk = ($this->codeReponse === '00000');

		if (!$paiementOk) {
			return null;
		}

		return [
			'num_question' => $tabArg['NUMQUESTION'] ?? '',
			'num_appel' => $tabArg['NUMAPPEL'] ?? '',
			'num_transaction' => $tabArg['NUMTRANS'] ?? '',
			'autorisation' => $tabArg['AUTORISATION'] ?? '',
			'ref_client' => $tabArg['REFABONNE'] ?? '',
			'cb_num' => $tabArg['PORTEUR'] ?? '',
			'cb_type' => $tabArg['TYPECARTE'] ?? '',
			'cb_sha1' => $tabArg['SHA-1'] ?? '',
		];
	}

	private function getHtml(): string
	{
		$urlForm = ($this->isTest ? self::URL_FORM_TEST : self::URL_FORM);

		$typePaiement = 'CARTE';
		// $typeCarte = 'CB,VISA,EUROCARD_MASTERCARD,E_CARD';
		$typeCarte = 'CB';

		// $dateTime = date(DATE_ISO8601, $timestamp);
		$dateTime = date('c', $this->getTimestamp());
		$authorizationOnly = (in_array($this->typeQuestion, [self::TYPE_OPERATION_AUTORISATION_SEULE, self::TYPE_OPERATION_INSCRIPTION_ABONNE], true) ? 'O' : 'N');
		//$authorizationOnly = 'O';
		$returnedVars = $this->getReturnedVars();

		// Calcul du HMAC
		$hmac = $this->getHmac([
			'PBX_SITE' => $this->numSite,
			'PBX_RANG' => $this->rang,
			'PBX_IDENTIFIANT' => $this->identifier,
			'PBX_LANGUE' => $this->getLanguageCode(),
			'PBX_TOTAL' => $this->getAmount(),
			'PBX_DEVISE' => $this->getCurrencyCode(),
			'PBX_CMD' => $this->reference,
			'PBX_PORTEUR' => $this->porteurEmail,
			'PBX_REFABONNE' => $this->subscriberRef,
			'PBX_RETOUR' => $returnedVars,
			'PBX_HASH' => 'SHA512',
			'PBX_TIME' => $dateTime,
			'PBX_AUTOSEULE' => $authorizationOnly,
			'PBX_REPONDRE_A' => $this->urlIpn,
			'PBX_RUF1' => 'POST',
			'PBX_EFFECTUE' => $this->urlResponseOk,
			'PBX_REFUSE' => $this->urlResponseRefused,
			'PBX_ANNULE' => $this->urlResponseCanceled,
			'PBX_ATTENTE' => $this->urlResponseWaiting,
			'PBX_TYPEPAIEMENT' => $typePaiement,
			'PBX_TYPECARTE' => $typeCarte,
		]);

		// Construction HTML
		return ''
			. '<form method="POST" action="' . $urlForm . '" class="' . ($this->formCssClass ?? '') . '">'
			. '<input type="hidden" name="PBX_SITE" value="' . $this->numSite . '">'
			. '<input type="hidden" name="PBX_RANG" value="' . $this->rang . '">'
			. '<input type="hidden" name="PBX_IDENTIFIANT" value="' . $this->identifier . '">'
			. '<input type="hidden" name="PBX_LANGUE" value="' . $this->getLanguageCode() . '">'
			. '<input type="hidden" name="PBX_TOTAL" value="' . $this->getAmount() . '">'
			. '<input type="hidden" name="PBX_DEVISE" value="' . $this->getCurrencyCode() . '">'
			. '<input type="hidden" name="PBX_CMD" value="' . $this->reference . '">'
			. '<input type="hidden" name="PBX_PORTEUR" value="' . $this->porteurEmail . '">'
			. '<input type="hidden" name="PBX_REFABONNE" value="' . $this->subscriberRef . '">'
			. '<input type="hidden" name="PBX_RETOUR" value="' . $returnedVars . '">'
			. '<input type="hidden" name="PBX_HASH" value="SHA512">'
			. '<input type="hidden" name="PBX_TIME" value="' . $dateTime . '">'
			. '<input type="hidden" name="PBX_AUTOSEULE" value="' . $authorizationOnly . '">'
			. '<input type="hidden" name="PBX_REPONDRE_A" value="' . $this->urlIpn . '">'
			. '<input type="hidden" name="PBX_RUF1" value="POST">'
			. '<input type="hidden" name="PBX_EFFECTUE" value="' . $this->urlResponseOk . '">'
			. '<input type="hidden" name="PBX_REFUSE" value="' . $this->urlResponseRefused . '">'
			. '<input type="hidden" name="PBX_ANNULE" value="' . $this->urlResponseCanceled . '">'
			. '<input type="hidden" name="PBX_ATTENTE" value="' . $this->urlResponseWaiting . '">'
			. '<input type="hidden" name="PBX_TYPEPAIEMENT" value="' . $typePaiement . '">'
			. '<input type="hidden" name="PBX_TYPECARTE" value="' . $typeCarte . '">'
			. '<input type="hidden" name="PBX_HMAC" value="' . $hmac . '">'
			. '<input type="submit" class="' . ($this->buttonCssClass ?? 'btn btn-primary') . '" value="' . $this->buttonText . '">'
			. '</form>';
	}

	private function getTypeOperationFormatted(): string
	{
		if (!empty($this->subscriberRef)) {
			if ($this->typeQuestion === self::TYPE_OPERATION_AUTORISATION_SEULE) {
				return self::TYPE_OPERATION_AUTORISATION_SEULE_ABONNE;
			}
			if ($this->typeQuestion === self::TYPE_OPERATION_DEBIT) {
				return self::TYPE_OPERATION_DEBIT_ABONNE;
			}
			if ($this->typeQuestion === self::TYPE_OPERATION_AUTORISATION_AND_DEBIT) {
				return self::TYPE_OPERATION_AUTORISATION_AND_DEBIT_ABONNE;
			}
			if ($this->typeQuestion === self::TYPE_OPERATION_CREDIT) {
				return self::TYPE_OPERATION_CREDIT_ABONNE;
			}
			if ($this->typeQuestion === self::TYPE_OPERATION_ANNULATION) {
				return self::TYPE_OPERATION_ANNULATION_ABONNE;
			}
		}
		return $this->typeQuestion;
	}

	private function getReturnedVars(): string
	{
		// $returnedVars = 'amount:M;reference:R;authorization_number:A;call_number:T;transaction_number:S;card_hash:H;card_last_digits:J;card_expiry_date:D;response_code:E';
		$returnedVars = 'amount:M;reference:R;authorization_number:A;call_number:T;transaction_number:S;card_last_digits:J;card_expiry_date:D;response_code:E';
		if ($this->typeQuestion === self::TYPE_OPERATION_INSCRIPTION_ABONNE) {
			//$returnedVars .= ';card_ref:U';
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

	private function getAmount(): int
	{
		return round($this->montant * 100, 2);
	}

	private function getFormattedAmount(): ?string
	{
		$montantFormate = (string) $this->getAmount();
		for ($numChar = strlen($montantFormate); $numChar < 10; $numChar++, $montantFormate = '0' . $montantFormate);
		return $montantFormate;
	}

	private function getCurrencyCode(): ?int
	{
		return Currency::getNumericCode($this->devise);
	}

	private function getExpirationDateFormatted(): ?string
	{
		if (strlen($this->dateValidite) === 10) { // format yyyy-mm-dd
			return substr($this->dateValidite, 5, 2) . substr($this->dateValidite, 2, 2);
		}
		if (strlen($this->dateValidite) === 7) { // format mm/yyyy
			return substr($this->dateValidite, 0, 2) . substr($this->dateValidite, -2);
		}
		if (strlen($this->dateValidite) === 4) { // format mmyy
			return substr($this->dateValidite, 0, 2) . substr($this->dateValidite, -2);
		}
		return null;
	}

	/**
	 * @param string $url
	 * @param array $queryData
	 * @return bool|ResponseInterface
	 */
	private static function post($url, array $queryData = [])
	{
		//var_dump($this->countryService->getCountryByHost());
		//var_dump(\App\Service\Helper\CountryHelper::getLocaleByCountryCode($this->countryService->getCountryByHost()));
		$client = new \GuzzleHttp\Client();
		try {
			$options = [
				'http_errors' => false,
			];
			$options['form_params'] = $queryData;
			$res = $client->request('POST', $url, $options);
		}
		//catch (\GuzzleHttp\Exception\GuzzleException $e) {
		catch (\Exception $e) {
			//var_dump($e->getMessage());
			return false;
		}
		return $res;
	}

}
