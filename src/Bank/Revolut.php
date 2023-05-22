<?php

namespace Osimatic\Helpers\Bank;

use Psr\Log\LoggerInterface;
use Osimatic\Helpers\Network\HTTPRequest;

class Revolut {
    public const URL_SANDBOX_PAYMENT = 'https://sandbox-merchant.revolut.com/api/1.0/orders';
    public const URL_PROD_PAYMENT = 'https://merchant.revolut.com/api/1.0/orders';

    public const CAPTURE_MODE_MANUAL = 'MANUAL'; //Autorisation seule
    public const CAPTURE_MODE_AUTO = 'AUTOMATIC'; //Autorisation & Débit

    private ?LoggerInterface $logger = null;

    private string $publicKey;
    private string $secretKey;
    private bool $isTest = false;
    private int $amount = 0;
    private string $currency = 'EUR';
    private string $captureMode = Revolut::CAPTURE_MODE_AUTO;
    private ?string $purchaseReference = null;

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
     * @param string $secretKey
     * 
     * @return [type]
     */
    public function setSecretKey(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @param bool $isTest
     * 
     * @return self
     */
    public function setIsTest(bool $isTest): self
    {
        $this->isTest = $isTest;

        return $this;
    }

    /**
     * @param int $amount
     * 
     * @return self
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param string $purchaseReference
     * 
     * @return self
     */
    public function setPurchaseReference(string $purchaseReference): self
    {
        $this->purchaseReference = $purchaseReference;

        return $this;
    }

    /**
     * Authorisation seule (CAPTURE_MODE_MANUAL) OU débit immédiat (CAPTURE_MODE_AUTO)
     * => dans le premier cas le débit final peut être effectuée via doDebit ci-dessous
     * @param string $captureMode
     *
     * @return RevolutResponse|null
     */
    public function doRequestWithSpecifiedCaptureMode(string $captureMode): ?RevolutResponse 
    {
        $this->captureMode = $captureMode;
        $paymentUrl = $this->isTest ? self::URL_SANDBOX_PAYMENT : self::URL_PROD_PAYMENT;
        $payload = [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'capture_mode' => $this->captureMode,
            'merchant_order_ext_ref' => $this->purchaseReference,
        ];

        return $this->doRequest($paymentUrl, $payload);
    }

    /**
     * Débit final d'une commande dont la capture est définie sur CAPTURE_MODE_MANUAL (autorisation seule)
     * @param string $orderId
     * 
     * @return RevolutResponse|null
     */
    public function doDebit(string $orderId): ?RevolutResponse
    {
        if (null === $orderId) {
            return null;
        }

        $paymentUrl = ($this->isTest ? self::URL_SANDBOX_PAYMENT : self::URL_PROD_PAYMENT) .'/'. $orderId .'/capture';

        return $this->doRequest($paymentUrl, ['amount' => $this->amount]);
    }

    /**
     * @param string $paymentUrl
     * @param array $payload
     *
     * @return RevolutResponse|null
     */
    private function doRequest(string $paymentUrl, array $payload): ?RevolutResponse
    {
        foreach ($payload as $cleVar => $value) {
			if ($value === null) {
				$postData[$cleVar] = '';
			} else {
				$postData[$cleVar] = trim($value);
			}
		}

		$queryString = http_build_query($postData);

		// Log
		$this->logger?->info('URL Paiement Revolut : ' . $paymentUrl);
		$this->logger?->info('QueryString envoyée : ' . $queryString);

		// Appel de l'URL Revolut avec les arguments POST (body JSON)
        $res = HTTPRequest::post($paymentUrl, $payload, $this->logger, ['Authorization' => 'Bearer '.$this->secretKey], true);
		if (null === $res) {
            $this->logger?->info('Appel Revolut échoué');
			return null;
		}

        $statusCode = $res->getStatusCode();

        if (401 === $statusCode) {
            $this->logger?->info('Appel Revolut échoué : auth token incorrect ou expiré');
            return null;
        }

        if (400 !== $statusCode && 201 !== $statusCode) {
            $this->logger?->info('Appel Revolut échoué : erreur inconnue');
            return null;
        }
        
        if (400 === $statusCode) {
            $this->logger?->info('Appel Revolut échoué : format body incorrect');
        }

		$res = (string) $res->getBody();
		$this->logger?->info('Résultat appel Revolut : ' . $res);

        return RevolutResponse::getFromRequest(json_decode($res, true));
    }

}