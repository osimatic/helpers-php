<?php

namespace Osimatic\Bank;

use Psr\Log\LoggerInterface;
use Osimatic\Network\HTTPRequest;

class Revolut
{
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
     * @return self
     */
    public function setSecretKey(string $secretKey): self
    {
        $this->secretKey = $secretKey;

        return $this;
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
    public function newPayment(string $captureMode): ?RevolutResponse
    {
        $this->captureMode = $captureMode;
        $paymentUrl = $this->isTest ? self::URL_SANDBOX_PAYMENT : self::URL_PROD_PAYMENT;
        $payload = [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'capture_mode' => $this->captureMode,
            'merchant_order_ext_ref' => $this->purchaseReference,
        ];

        return $this->doRequest($paymentUrl, $payload, 'POST');
    }

    /**
     * Débit final d'une commande dont la capture est définie sur CAPTURE_MODE_MANUAL (autorisation seule)
     * @param string $orderId
     * 
     * @return RevolutResponse|null
     */
    public function capture(string $orderId): ?RevolutResponse
    {
        if (null === $orderId) {
            return null;
        }

        $paymentUrl = ($this->isTest ? self::URL_SANDBOX_PAYMENT : self::URL_PROD_PAYMENT) . '/' . $orderId . '/capture';

        return $this->doRequest($paymentUrl, ['amount' => $this->amount], 'POST');
    }

    /**
     * @param string $orderId
     *
     * @return RevolutResponse|null
     */
    public function getOrder(string $orderId): ?RevolutResponse
    {
        if (null === $orderId) {
            return null;
        }

        $url = ($this->isTest ? self::URL_SANDBOX_PAYMENT : self::URL_PROD_PAYMENT) . '/' . $orderId;

        return $this->doRequest($url, [], 'GET');
    }

    /**
     * @param string $requestUrl
     * @param array $payload
     * @param string $httpMethod
     *
     * @return RevolutResponse|null
     */
    private function doRequest(string $requestUrl, array $payload, string $httpMethod): ?RevolutResponse
    {
        // Log
        $this->logger?->info('URL : ' . $requestUrl);
        $this->logger?->info('Payload : ' . json_encode($payload));

        if ('GET' === $httpMethod) {
            // Appel de l'URL Revolut via GET (get order)
            $res = HTTPRequest::get($requestUrl, [], $this->logger, ['Authorization' => 'Bearer ' . $this->secretKey]);
        } else {
            // Appel de l'URL Revolut via POST (authorize / pay order)
            $res = HTTPRequest::post($requestUrl, $payload, $this->logger, ['Authorization' => 'Bearer ' . $this->secretKey], true);
        }

        if (null === $res) {
            $this->logger?->info('Appel Revolut échoué');
            return null;
        }

        $statusCode = $res->getStatusCode();

        if (401 === $statusCode) {
            $this->logger?->info('Appel Revolut échoué : auth token incorrect ou expiré');
            return null;
        }

        if (400 !== $statusCode && 201 !== $statusCode && 200 !== $statusCode) {
            $this->logger?->info('Appel Revolut échoué : erreur inconnue');
            return null;
        }

        if (400 === $statusCode) {
            $this->logger?->info('Appel Revolut échoué : format paramètres incorrect');
        }

        $res = (string) $res->getBody();
        $this->logger?->info('Résultat appel Revolut : ' . $res);

        return RevolutResponse::getFromRequest(json_decode($res, true));
    }
}