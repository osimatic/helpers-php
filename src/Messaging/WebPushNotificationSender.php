<?php

namespace Osimatic\Helpers\Messaging;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class WebPushNotificationSender
{
	const MAX_COMPATIBILITY_PAYLOAD_LENGTH = 3052;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var array
	 */
	private $vapid = [];

	/**
	 * @var WebPushNotificationSubscriptionInterface
	 */
	private $subscription;

	/**
	 * @var WebPushNotification
	 */
	private $payload;

	/**
	 * @var string local generated public key
	 */
	private string $localPublicKey = '';

	/**
	 * @var \GMP local generated private key
	 */
	private \GMP $gmpLocalPrivateKey;

	/**
	 * @var string generated salt
	 */
	private string $salt = '';




	public function __construct()
	{
		$this->logger = new NullLogger();
	}




	public function push() : bool
	{
		if (empty($this->vapid)) {
			$this->logger->error('no VAPID-keys set.');
			return false;
		}

		if (!$this->isVapidValid()) {
			$this->logger->error('VAPID not valid.');
			return false;
		}

		if (null === $this->subscription) {
			$this->logger->error('Subscription not set.');
			return false;
		}

		// payload must be encrypted every time although it does not change, since
		// each subscription has at least his public key and authentication token of its own ...
		//$oEncrypt = new PNEncryption($oSub->getPublicKey(), $oSub->getAuth(), $oSub->getEncoding());
		if (false === ($strContent = $this->getEncryptedPayload())) {
			$this->logger->error('Payload encryption error.');
			return false;

		}

		if (($aHeaders = $this->getHeaders()) === null) {
			$this->logger->error('Headers creating error.');
			return false;
		}

		$aHeaders['Content-Length'] = mb_strlen($strContent, '8bit');
		$aHeaders['TTL'] = 2419200;

		// build Http - Headers
		$aHttpHeader = array();
		foreach ($aHeaders as $strName => $strValue) {
			$aHttpHeader[] = $strName . ': ' . $strValue;
		}

		// and send request with curl
		$curl = curl_init($this->subscription->getEndpoint());

		if ($curl === false) {
			$this->logger->error('curl init error.');
			return false;
		}

		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $strContent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $aHttpHeader);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($curl);
		curl_close($curl);

		if (false === $result) {
			$this->logger->error('curl exec error.');
			return false;
		}

		return true;
	}





	private function getEncoding() : string
	{
		return $this->subscription->getContentEncoding() ?? 'aesgcm';
	}

	private function getPayload() : string
	{
		if (empty($this->payload)) {
			return '';
		}
		return $this->payload->getPayload();
	}

	private function getSubscriptionAuth() : string
	{
		if (empty($this->subscription)) {
			return '';
		}
		return self::decodeBase64URL($this->subscription->getAuthToken());
	}

	private function getSubscriptionKey() : string
	{
		if (empty($this->subscription)) {
			return '';
		}
		return self::decodeBase64URL($this->subscription->getPublicKey());
	}

	/**
	 * Check for valid VAPID.
	 * - subject, public key and private key must be set <br>
	 * - decoded public key must be 65 bytes long  <br>
	 * - no compresed public key supported <br>
	 * - decoded private key must be 32 bytes long <br>
	 * @return bool
	 */
	private function isVapidValid() : bool
	{
		if (count($this->vapid) !== 3) {
			return false;
		}

		[$subject, $publicKey, $privateKey] = $this->vapid;
		if (empty($subject) || empty($publicKey) || empty($privateKey)) {
			return false;
		}

		if (mb_strlen($publicKey, '8bit') !== 65) {
			return false;
		}

		$hexPublicKey = bin2hex($publicKey);
		if (mb_substr($hexPublicKey, 0, 2, '8bit') !== '04') {
			return false;
		}

		if (mb_strlen($privateKey, '8bit') !== 32) {
			return false;
		}
		return true;
	}

	/**
	 * encrypt the payload.
	 * @return string|null encrypted string at success, null on any error
	 */
	public function getEncryptedPayload(): ?string
	{
		$payload = $this->getPayload();

		// there's nothing to encrypt without payload...
		if (empty($payload)) {
			// it's OK - just set content-length of request to 0!
			$this->logger->info('Payload vide.');
			return '';
		}

		if ($this->getEncoding() !== 'aesgcm' && $this->getEncoding() !== 'aes128gcm') {
			$this->logger->error('Encoding "'.$this->getEncoding().'" is not supported.');
			return null;
		}

		if (mb_strlen($this->getSubscriptionKey(), '8bit') !== 65) {
			$this->logger->error('Invalid client public key length.');
			return null;
		}

		try {
			// create random salt and local key pair
			$this->salt = \random_bytes(16);
			if (!$this->createLocalKey()) {
				return null;
			}

			// create shared secret between local private key and public subscription key
			$strSharedSecret = $this->getSharedSecret();

			// context and pseudo random key (PRK) to create content encryption key (CEK) and nonce
			/*
			 * A nonce is a value that prevents replay attacks as it should only be used once.
			 * The content encryption key (CEK) is the key that will ultimately be used toencrypt
			 * our payload.
			 * @link https://en.wikipedia.org/wiki/Cryptographic_nonce
			 */
			$context = $this->createContext();
			$prk = $this->getPRK($strSharedSecret);

			// derive the encryption key
			$cekInfo = $this->createInfo($this->getEncoding(), $context);
			$cek = self::hkdf($this->salt, $prk, $cekInfo, 16);

			// and the nonce
			$nonceInfo = $this->createInfo('nonce', $context);
			$nonce = self::hkdf($this->salt, $prk, $nonceInfo, 12);

			// pad payload ... from now payload converted to binary string
			$payload = $this->padPayload($payload, self::MAX_COMPATIBILITY_PAYLOAD_LENGTH);

			// encrypt
			// "The additional data passed to each invocation of AEAD_AES_128_GCM is a zero-length octet sequence."
			$strTag = '';
			$strEncrypted = openssl_encrypt($payload, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $strTag);

			// base64URL encode salt and local public key
			$this->salt = self::encodeBase64URL($this->salt);
			$this->localPublicKey = self::encodeBase64URL($this->localPublicKey);

			return $this->getContentCodingHeader() . $strEncrypted . $strTag;
		}
		catch (\RuntimeException | \Exception $e) {
			$this->logger->error($e->getMessage());
		}

		return null;
	}

	/**
	 * Get headers for previous encrypted payload.
	 * Already existing headers (e.g. the VAPID-signature) can be passed through the input param
	 * and will be merged with the additional headers for the encryption
	 *
	 * @return array|null
	 */
	private function getHeaders() : ?array
	{
		if (null === ($headers = $this->getVapidHeaders())) {
			return null;
		}

		if (!empty($this->getPayload())) {
			$headers['Content-Type'] = 'application/octet-stream';
			$headers['Content-Encoding'] = $this->getEncoding();
			if ($this->getEncoding() === 'aesgcm') {
				$headers['Encryption'] = 'salt=' . $this->salt;
				if (isset($headers['Crypto-Key'])) {
					$headers['Crypto-Key'] = 'dh=' . $this->localPublicKey . ';' . $headers['Crypto-Key'];
				} else {
					$headers['Crypto-Key'] = 'dh=' . $this->localPublicKey;
				}
			}
		}
		return $headers;
	}

	/**
	 * Create header for endpoint using current timestamp.
	 * @return array|null headers if succeeded, null on error
	 */
	private function getVapidHeaders(): ?array
	{
		// info
		$aJwtInfo = array('typ' => 'JWT', 'alg' => 'ES256');
		$strJwtInfo = self::encodeBase64URL(json_encode($aJwtInfo));

		[$subject, $publicKey, $privateKey] = $this->vapid;

		// data
		// - origin from endpoint
		// - timeout 12h from now
		// - subject (e-mail or URL to invoker of VAPID-keys)
		$aJwtData = [
			'aud' => parse_url($this->subscription->getEndpoint(), PHP_URL_SCHEME) . '://' . parse_url($this->subscription->getEndpoint(), PHP_URL_HOST),
			'exp' => time() + 43200,
			'sub' => $subject
		];
		$strJwtData = self::encodeBase64URL(json_encode($aJwtData));

		// signature
		// ECDSA encrypting "JwtInfo.JwtData" using the P-256 curve and the SHA-256 hash algorithm
		$strData = $strJwtInfo . '.' . $strJwtData;
		$pem = self::getP256PEM($publicKey, $privateKey);

		$strSignature = '';
		if (false === \openssl_sign($strData, $strSignature, $pem, OPENSSL_ALGO_SHA256)) {
			$this->logger->error('Error creating signature.');
			return null;
		}

		if (false === ($sig = self::signatureFromDER($strSignature))) {
			$this->logger->error('Error creating signature.');
			return null;
		}

		$strSignature = self::encodeBase64URL($sig);
		return [
			'Authorization' => 'WebPush ' . $strJwtInfo . '.' . $strJwtData . '.' . $strSignature,
			'Crypto-Key'    => 'p256ecdsa=' . self::encodeBase64URL($publicKey)
		];
	}


	/**
	 * create local public/private key pair using prime256v1 curve
	 * @return bool
	 */
	private function createLocalKey() : bool
	{
		$keyResource = \openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
		if ($keyResource === false) {
			return false;
		}

		$details = \openssl_pkey_get_details($keyResource);
		\openssl_pkey_free($keyResource);

		if ($details === false) {
			$this->logger->error('openssl error: ' . \openssl_error_string());
			return false;
		}

		$this->localPublicKey  = '04';
		$this->localPublicKey .= str_pad(gmp_strval(gmp_init(bin2hex($details['ec']['x']), 16), 16), 64, '0', STR_PAD_LEFT);
		$this->localPublicKey .= str_pad(gmp_strval(gmp_init(bin2hex($details['ec']['y']), 16), 16), 64, '0', STR_PAD_LEFT);
		$this->localPublicKey = hex2bin($this->localPublicKey);

		$this->gmpLocalPrivateKey = gmp_init(bin2hex($details['ec']['d']), 16);

		return true;
	}

	/**
	 * build shared secret from user public key and local private key using prime256v1 curve
	 * @return string
	 */
	private function getSharedSecret() : string
	{
		$curve = NistCurve::curve256();

		$x = '';
		$y = '';
		self::getXYFromPublicKey($this->getSubscriptionKey(), $x, $y);

		$strSubscrKeyPoint = $curve->getPublicKeyFrom(\gmp_init(bin2hex($x), 16), \gmp_init(bin2hex($y), 16));

		// get shared secret from user public key and local private key
		$strSharedSecret = $curve->mul($strSubscrKeyPoint, $this->gmpLocalPrivateKey);
		$strSharedSecret = $strSharedSecret->getX();
		$strSharedSecret = hex2bin(str_pad(\gmp_strval($strSharedSecret, 16), 64, '0', STR_PAD_LEFT));

		return $strSharedSecret;
	}

	/**
	 * get pseudo random key
	 * @param string $strSharedSecret
	 * @return string
	 */
	private function getPRK(string $strSharedSecret) : string
	{
		if (!empty($this->getSubscriptionAuth())) {
			if ($this->getEncoding() === 'aesgcm') {
				$info = 'Content-Encoding: auth' . chr(0);
			} else {
				$info = 'WebPush: info' . chr(0) . $this->getSubscriptionKey() . $this->localPublicKey;
			}
			$strSharedSecret = self::hkdf($this->getSubscriptionAuth(), $strSharedSecret, $info, 32);
		}

		return $strSharedSecret;
	}

	/**
	 * Creates a context for deriving encryption parameters.
	 * See section 4.2 of
	 * {@link https://tools.ietf.org/html/draft-ietf-httpbis-encryption-encoding-00}
	 * From {@link https://github.com/GoogleChrome/push-encryption-node/blob/master/src/encrypt.js}.
	 *
	 * @return null|string
	 */
	private function createContext() : ?string
	{
		if ($this->getEncoding() === 'aes128gcm') {
			return null;
		}

		// This one should never happen, because it's our code that generates the key
		/*
		if (mb_strlen($this->strLocalPublicKey, '8bit') !== 65) {
			throw new \ErrorException('Invalid server public key length');
		}
		*/

		$len = chr(0) . 'A'; // 65 as Uint16BE

		return chr(0) . $len . $this->getSubscriptionKey() . $len . $this->localPublicKey;
	}

	/**
	 * Returns an info record. See sections 3.2 and 3.3 of
	 * {@link https://tools.ietf.org/html/draft-ietf-httpbis-encryption-encoding-00}
	 * From {@link https://github.com/GoogleChrome/push-encryption-node/blob/master/src/encrypt.js}.
	 *
	 * @param string $strType The type of the info record
	 * @param string|null $strContext The context for the record
	 * @return string
	 * @throws \ErrorException
	 */
	private function createInfo(string $strType, ?string $strContext) : string
	{
		if ($this->getEncoding() === 'aesgcm') {
			if (!$strContext) {
				throw new \ErrorException('Context must exist');
			}

			if (mb_strlen($strContext, '8bit') !== 135) {
				throw new \ErrorException('Context argument has invalid size');
			}

			$strInfo = 'Content-Encoding: ' . $strType . chr(0) . 'P-256' . $strContext;
		} else {
			$strInfo = 'Content-Encoding: ' . $strType . chr(0);
		}
		return $strInfo;
	}

	/**
	 * get the content coding header to add to encrypted payload
	 * @return string
	 */
	private function getContentCodingHeader() : string
	{
		$strHeader = '';
		if ($this->getEncoding() === 'aes128gcm') {
			$strHeader = $this->salt
				. pack('N*', 4096)
				. pack('C*', mb_strlen($this->localPublicKey, '8bit'))
				. $this->localPublicKey;
		}
		return $strHeader;
	}

	/**
	 * pad the payload.
	 * Before we encrypt our payload, we need to define how much padding we wish toadd to
	 * the front of the payload. The reason we’d want to add padding is that it prevents
	 * the risk of eavesdroppers being able to determine “types” of messagesbased on the
	 * payload size. We must add two bytes of padding to indicate the length of any
	 * additionalpadding.
	 *
	 * @param string $strPayload
	 * @param int $iMaxLengthToPad
	 * @return string
	 */
	private function padPayload(string $strPayload, int $iMaxLengthToPad = 0) : string
	{
		$iLen = mb_strlen($strPayload, '8bit');
		$iPad = $iMaxLengthToPad ? $iMaxLengthToPad - $iLen : 0;

		if ($this->getEncoding() === 'aesgcm') {
			$strPayload = pack('n*', $iPad) . str_pad($strPayload, $iPad + $iLen, chr(0), STR_PAD_LEFT);
		} elseif ($this->getEncoding() === 'aes128gcm') {
			$strPayload = str_pad($strPayload . chr(2), $iPad + $iLen, chr(0), STR_PAD_RIGHT);
		}
		return $strPayload;
	}

	// static

	/**
	 * HMAC-based Extract-and-Expand Key Derivation Function (HKDF).
	 *
	 * This is used to derive a secure encryption key from a mostly-secure shared
	 * secret.
	 *
	 * This is a partial implementation of HKDF tailored to our specific purposes.
	 * In particular, for us the value of N will always be 1, and thus T always
	 * equals HMAC-Hash(PRK, info | 0x01).
	 *
	 * See {@link https://www.rfc-editor.org/rfc/rfc5869.txt}
	 * From {@link https://github.com/GoogleChrome/push-encryption-node/blob/master/src/encrypt.js}
	 *
	 * @param string $salt   A non-secret random value
	 * @param string $ikm    Input keying material
	 * @param string $info   Application-specific context
	 * @param int    $length The length (in bytes) of the required output key
	 *
	 * @return string
	 */
	private static function hkdf(string $salt, string $ikm, string $info, int $length) : string
	{
		// extract
		$prk = hash_hmac('sha256', $ikm, $salt, true);

		// expand
		return mb_substr(hash_hmac('sha256', $info . chr(1), $prk, true), 0, $length, '8bit');
	}

	/**
	 * Encode data to Base64URL.
	 * @param string $data
	 * @return string   encoded string
	 */
	public static function encodeBase64URL(string $data) : string
	{
		// Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
		$url = strtr(base64_encode($data), '+/', '-_');

		// Remove padding character from the end of line and return the Base64URL result
		return rtrim($url, '=');
	}

	/**
	 * Decode data from Base64URL.
	 * If the strict parameter is set to TRUE then the function will return false
	 * if the input contains character from outside the base64 alphabet. Otherwise
	 * invalid characters will be silently discarded.
	 * @param string $data
	 * @param boolean $strict
	 * @return string
	 */
	public static function decodeBase64URL(string $data, bool $strict = false) : string
	{
		// Convert Base64URL to Base64 by replacing “-” with “+” and “_” with “/”
		$b64 = strtr($data, '-_', '+/');

		// Decode Base64 string and return the original data
		return base64_decode($b64, $strict);
	}

	public static function getP256PEM(string $strPublicKey, string $strPrivateKey) : string
	{
		$der  = self::p256PrivateKey($strPrivateKey);
		$der .= $strPublicKey;

		$pem = '-----BEGIN EC PRIVATE KEY-----' . PHP_EOL;
		$pem .= chunk_split(base64_encode($der), 64, PHP_EOL);
		$pem .= '-----END EC PRIVATE KEY-----' . PHP_EOL;

		return $pem;
	}

	private static function p256PrivateKey(string $strPrivateKey) : string
	{
		$key = unpack('H*', str_pad($strPrivateKey, 32, "\0", STR_PAD_LEFT))[1];

		return pack(
			'H*',
			'3077'                  // SEQUENCE, length 87+length($d)=32
			. '020101'              // INTEGER, 1
			. '0420'                // OCTET STRING, length($d) = 32
			. $key
			. 'a00a'                // TAGGED OBJECT #0, length 10
			. '0608'                // OID, length 8
			. '2a8648ce3d030107'    // 1.3.132.0.34 = P-256 Curve
			. 'a144'                //  TAGGED OBJECT #1, length 68
			. '0342'                // BIT STRING, length 66
			. '00'                  // prepend with NUL - pubkey will follow
		);
	}

	/**
	 * @param string $der
	 * @return bool|string
	 */
	public static function signatureFromDER(string $der)
	{
		$sig = false;
		$R = false;
		$S = false;
		$hex = \unpack('H*', $der)[1];
		if ('30' === \mb_substr($hex, 0, 2, '8bit')) {
			// SEQUENCE
			if ('81' === \mb_substr($hex, 2, 2, '8bit')) {
				// LENGTH > 128
				$hex = \mb_substr($hex, 6, null, '8bit');
			} else {
				$hex = \mb_substr($hex, 4, null, '8bit');
			}
			if ('02' === \mb_substr($hex, 0, 2, '8bit')) {
				// INTEGER
				$Rl = (int) \hexdec(\mb_substr($hex, 2, 2, '8bit'));
				$R = self::retrievePosInt(\mb_substr($hex, 4, $Rl * 2, '8bit'));
				$R = \str_pad($R, 64, '0', STR_PAD_LEFT);

				$hex = \mb_substr($hex, 4 + $Rl * 2, null, '8bit');
				if ('02' === \mb_substr($hex, 0, 2, '8bit')) {
					// INTEGER
					$Sl = (int) \hexdec(\mb_substr($hex, 2, 2, '8bit'));
					$S = self::retrievePosInt(\mb_substr($hex, 4, $Sl * 2, '8bit'));
					$S = \str_pad($S, 64, '0', STR_PAD_LEFT);
				}
			}
		}

		if ($R !== false && $S !== false) {
			$sig = \pack('H*', $R . $S);
		}

		return $sig;
	}

	private static function retrievePosInt(string $data) : string
	{
		while ('00' === \mb_substr($data, 0, 2, '8bit') && \mb_substr($data, 2, 2, '8bit') > '7f') {
			$data = \mb_substr($data, 2, null, '8bit');
		}

		return $data;
	}

	public static function getXYFromPublicKey(string $strKey, string &$x, string &$y) : bool
	{
		$bSucceeded = false;
		$hexData = bin2hex($strKey);
		if (mb_substr($hexData, 0, 2, '8bit') === '04') {
			$hexData = mb_substr($hexData, 2, null, '8bit');
			$dataLength = mb_strlen($hexData, '8bit');

			$x = hex2bin(mb_substr($hexData, 0, $dataLength / 2, '8bit'));
			$y = hex2bin(mb_substr($hexData, $dataLength / 2, null, '8bit'));
		}
		return $bSucceeded;
	}
}