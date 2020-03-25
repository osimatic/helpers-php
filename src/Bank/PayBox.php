<?php

namespace Osimatic\Helpers\Bank;

use Psr\Log\LoggerInterface;
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

	private $version;
	private $typeQuestion;
	private $numSite;
	private $rang;
	private $identifier;
	private $httpPassword;
	private $secretKey;
	private $isTest = false;
	private $useForm = false;

	private $numQuestion;
	private $montant;
	private $devise = 'EUR';
	private $reference;
	private $subscriberRef;
	private $date;

	private $porteurEmail;
	private $porteur;
	private $dateValidite;
	private $cvv;

	private $activite;
	private $archivage;
	private $differe;
	private $numAppel;
	private $numTransaction;
	private $autorisation;
	private $pays;

	private $formCssClass;
	private $buttonCssClass;
	private $buttonText;
	private $urlResponseOk;
	private $urlResponseRefused;
	private $urlResponseCanceled;
	private $urlResponseWaiting;
	private $urlIpn;

	private $codeReponse;
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

	private $logger;

	public function __construct()
	{

	}

	public function newPayment(): void
	{
		$this->codeReponse = '99999';
	}

	public function doAuthorization()
	{
		$this->typeQuestion = self::TYPE_OPERATION_AUTORISATION_SEULE;
		return $this->doRequest();
	}

	public function doDebit()
	{
		$this->typeQuestion = self::TYPE_OPERATION_DEBIT;
		return $this->doRequest();
	}

	public function doAuthorizationAndDebit()
	{
		$this->typeQuestion = self::TYPE_OPERATION_AUTORISATION_AND_DEBIT;
		return $this->doRequest();
	}

	public function addSubscriber()
	{
		$this->typeQuestion = self::TYPE_OPERATION_INSCRIPTION_ABONNE;
		return $this->doRequest();
	}

	public function deleteSubscriber()
	{
		$this->typeQuestion = self::TYPE_OPERATION_SUPPRESSION_ABONNE;
		return $this->doRequest();
	}

	public function getFormSubscriberRegister()
	{
		$this->typeQuestion = self::TYPE_OPERATION_INSCRIPTION_ABONNE;
		$this->useForm = true;
		return $this->doRequest();
	}

	public function getForm()
	{
		$this->useForm = true;
		return $this->doRequest();
	}


	public function getNumQuestion()
	{
		return $this->numQuestion;
	}

	public function getVersion()
	{
		return $this->version;
	}

	public function getIsTest()
	{
		return $this->isTest;
	}

	public function getRang()
	{
		return $this->rang;
	}

	public function getDate()
	{
		return $this->date;
	}

	public function getNumSite()
	{
		return $this->numSite;
	}

	public function getReference()
	{
		return $this->reference;
	}

	public function getSubscriberRef()
	{
		return $this->subscriberRef;
	}

	public function getMontant()
	{
		return $this->montant;
	}

	public function getDevise()
	{
		return $this->devise;
	}

	public function getPorteurEmail()
	{
		return $this->porteurEmail;
	}

	public function getPorteur()
	{
		return $this->porteur;
	}

	public function getDateValidite()
	{
		return $this->dateValidite;
	}

	public function getCvv()
	{
		return $this->cvv;
	}

	public function getActivite()
	{
		return $this->activite;
	}

	public function getArchivage()
	{
		return $this->archivage;
	}

	public function getDiffere()
	{
		return $this->differe;
	}

	public function getNumAppel()
	{
		return $this->numAppel;
	}

	public function getNumTransaction()
	{
		return $this->numTransaction;
	}

	public function getAutorisation()
	{
		return $this->autorisation;
	}

	public function getPays()
	{
		return $this->pays;
	}

	public function getCodeReponse()
	{
		return $this->codeReponse;
	}

	public function getLibelleReponse()
	{
		return $this->libelleReponse;
	}


	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	public function setNumQuestion($numQuestion): void
	{
		$this->numQuestion = $numQuestion;
	}

	public function setVersion($version): void
	{
		$this->version = $version;
	}

	public function setIdentifier($identifier): void
	{
		$this->identifier = $identifier;
	}

	public function setHttpPassword($httpPassword): void
	{
		$this->httpPassword = $httpPassword;
	}

	public function setSecretKey($secretKey): void
	{
		$this->secretKey = $secretKey;
	}

	public function setIsTest($isTest): void
	{
		$this->isTest = $isTest;
	}

	public function setIdentifiant($identifiant): void
	{
		$this->identifiant = $identifiant;
	}

	public function setRang($rang): void
	{
		$this->rang = $rang;
	}

	public function setDate($date): void
	{
		$this->date = $date;
	}

	public function setNumSite($numSite): void
	{
		$this->numSite = $numSite;
	}

	public function setReference($reference): void
	{
		$this->reference = $reference;
	}

	public function setSubscriberRef($subscriberRef): void
	{
		$this->subscriberRef = $subscriberRef;
	}

	public function setMontant($montant): void
	{
		$this->montant = round($montant, 2);
	}

	public function setDevise($devise): void
	{
		$this->devise = $devise;
	}

	public function setPorteurEmail($porteurEmail): void
	{
		$this->porteurEmail = $porteurEmail;
	}

	public function setPorteur($porteur): void
	{
		$this->porteur = $porteur;
	}

	public function setDateValidite($dateValidite): void
	{
		$this->dateValidite = $dateValidite;
	}

	public function setCvv($cvv): void
	{
		$this->cvv = $cvv;
	}

	public function setActivite($activite): void
	{
		$this->activite = $activite;
	}

	public function setArchivage($archivage): void
	{
		$this->archivage = $archivage;
	}

	public function setDiffere($differe): void
	{
		$this->differe = $differe;
	}

	public function setNumAppel($numAppel): void
	{
		$this->numAppel = $numAppel;
	}

	public function setNumTransaction($numTransaction): void
	{
		$this->numTransaction = $numTransaction;
	}

	public function setAutorisation($autorisation): void
	{
		$this->autorisation = $autorisation;
	}

	public function setPays($pays): void
	{
		$this->pays = $pays;
	}

	public function setFormCssClass($formCssClass): void
	{
		$this->formCssClass = $formCssClass;
	}

	public function setButtonCssClass($buttonCssClass): void
	{
		$this->buttonCssClass = $buttonCssClass;
	}

	public function setButtonText($buttonText): void
	{
		$this->buttonText = $buttonText;
	}

	public function setUrlResponseOk($urlResponseOk): void
	{
		$this->urlResponseOk = $urlResponseOk;
	}

	public function setUrlResponseRefused($urlResponseRefused): void
	{
		$this->urlResponseRefused = $urlResponseRefused;
	}

	public function setUrlResponseCanceled($urlResponseCanceled): void
	{
		$this->urlResponseCanceled = $urlResponseCanceled;
	}

	public function setUrlResponseWaiting($urlResponseWaiting): void
	{
		$this->urlResponseWaiting = $urlResponseWaiting;
	}

	public function setUrlIpn($urlIpn): void
	{
		$this->urlIpn = $urlIpn;
	}

	public function setCodeReponse($codeReponse): void
	{
		$this->codeReponse = $codeReponse;
	}

	public function setLibelleReponse($libelleReponse): void
	{
		$this->libelleReponse = $libelleReponse;
	}


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
			if (!$this->useForm) {
				// $this->rang = '63';
			}
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
			'MONTANT' => $this->getAmountFormated(),
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
		return ($this->date !== null ? strtotime($this->date) : time());
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

	private function getAmount(): int
	{
		return $this->montant * 100;
	}

	private function getAmountFormated(): ?string
	{
		$montantFormate = $this->getAmount();
		for ($numChar = strlen((string)$montantFormate); $numChar < 10; $numChar++, $montantFormate = '0' . $montantFormate) ;
		return $montantFormate;
	}

	// Formatage de la devise
	private function getCurrencyCode(): ?int
	{
		$currencyCode = null;
		switch ($this->devise) {
			case 'EUR' :
				$currencyCode = 978;
				break;
			case 'USD' :
				$currencyCode = 840;
				break;
			case 'CFA' :
				$currencyCode = 952;
				break;
		}
		return $currencyCode;
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
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			var_dump($e->getMessage());
			return false;
		}
		return $res;
	}

}
