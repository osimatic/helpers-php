<?php

namespace Osimatic\Helpers\Bank;

use Psr\Log\LoggerInterface;
use Osimatic\Helpers\Network\HTTPRequest;

class Revolut {
    public const URL_SANDBOX_PAYMENT = 'https://sandbox-merchant.revolut.com/api/1.0/orders';
    public const URL_PROD_PAYMENT = 'https://merchant.revolut.com/api/1.0/orders';

    public const CAPTURE_MODE_MANUAL = 'MANUAL'; //Autorisation seule
    public const CAPTURE_MODE_AUTO = 'AUTOMATIC'; //Autorisation & Débit

    private LoggerInterface $logger;

    private string $publicKey;
    private string $secretKey;
    private bool $isTest = false;
    private float $amount = 0;
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
     * @param bool $amount
     * 
     * @return self
     */
    public function setAmount(bool $amount): self
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

    public function doAuthorization(): ?RevolutResponse
    {
        $this->captureMode = self::CAPTURE_MODE_MANUAL;
        if (false === ($result = $this->doRequest())) {
            return null;
        }

        return $result;
    }

    public function doDebit()
    {
        //todo
    }

    public function doAuthorizationAndDebit(): ?RevolutResponse
    {
        $this->captureMode = self::CAPTURE_MODE_AUTO;
        if (false === ($result = $this->doRequest())) {
            return null;
        }

        return $result;
    }

    private function doRequest(): ?RevolutResponse
    {
        $paymentUrl = $this->isTest ? self::URL_SANDBOX_PAYMENT : self::URL_PROD_PAYMENT;
        $payload = [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'capture_mode' => $this->captureMode,
            'merchant_order_ext_ref' => $this->purchaseReference,
        ];

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
		$this->logger?->info('Référence achat : ' . $postData['merchant_order_ext_ref']);

		// Appel de l'URL Revolut avec les arguments POST (body JSON)
        $res = HTTPRequest::post($paymentUrl, $payload, $this->logger, ['Authorization' => 'Bearer '.$this->secretKey], true);

		if (null === $res) {
			$this->logger?->info('Appel Revolut échoué');
			return null;
		}

        if (401 === $res->getStatusCode()) {
            $this->logger?->info('Appel Revolut échoué : auth token incorrect ou expiré');
            return null;
        }

        if (400 === $res->getStatusCode()) {
            $this->logger?->info('Appel Revolut échoué : format body incorrect');
            return null;
        }

		$res = (string) $res->getBody();
		$this->logger?->info('Résultat appel Revolut : ' . $res);

        // Récupération des arguments retour
		$tabArg = json_decode($res, true);

        $revolutResponse = new RevolutResponse();
        $revolutResponse->setId(!empty($tabArg['id']) ? urldecode($tabArg['id']) : null);
        $revolutResponse->setPublicId(!empty($tabArg['public_id']) ? urldecode($tabArg['public_id']) : null);
        $revolutResponse->setType(!empty($tabArg['type']) ? urldecode($tabArg['type']) : null);
        $revolutResponse->setState(!empty($tabArg['state']) ? urldecode($tabArg['state']) : null);
        $revolutResponse->setCreationDate(!empty($tabArg['created_at']) ? new \DateTime($tabArg['created_at']) : null);
        $revolutResponse->setUpdateDate(!empty($tabArg['updated_at']) ? new \DateTime($tabArg['updated_at']) : null);
        $revolutResponse->setCaptureMode(!empty($tabArg['capture_mode']) ? urldecode($tabArg['capture_mode']) : null);
        $revolutResponse->setMerchantOrderExtRef(!empty($tabArg['merchant_order_ext_ref']) ? urldecode($tabArg['merchant_order_ext_ref']) : null);
        $revolutResponse->setAmount(!empty($tabArg['order_amount']) ? urldecode($tabArg['order_amount']['value']) : null);
        $revolutResponse->setCurrency(!empty($tabArg['order_amount']) ? urldecode($tabArg['order_amount']['currency']) : null);
        $revolutResponse->setCheckoutUrl(!empty($tabArg['checkout_url']) ? urldecode($tabArg['checkout_url']) : null);

        return $revolutResponse;
    }

}