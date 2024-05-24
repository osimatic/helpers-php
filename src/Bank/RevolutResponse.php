<?php 

namespace Osimatic\Bank;

/**
 * https://developer.revolut.com/docs/api-reference/merchant/#tag/Orders/operation/createOrder
 */
class RevolutResponse {
    /**
     * @var string|null
     */
    private ?string $id = null;

    /**
     * @var string|null
     */
    private ?string $publicId = null;

    /**
     * @var string|null
     */
    private ?string $type = null;

    /**
     * @var string|null
     */
    private ?string $state = null;

    /**
     * @var \DateTime|null
     */
    private ?\DateTime $creationDate = null;

    /**
     * @var \DateTime|null
     */
    private ?\DateTime $updateDate = null;

    /**
     * @var string|null
     */
    private ?string $captureMode = null;

    /**
     * @var string|null
     */
    private ?string $merchantOrderExtRef = null;

    /**
     * @var int|null
     */
    private ?int $amount = null;

    /**
     * @var string|null
     */
    private ?string $currency = null;

    /**
     * @var string|null
     */
    private ?string $checkoutUrl = null;

	/**
	 * @var string|null
	 */
	private ?string $errorId = null;

    /**
     * @var string|null
     */
    private ?string $cardLastDigits = null;

    /**
     * @var string|null
     */
    private ?string $cardExpiration = null;


    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getPublicId(): ?string
    {
        return $this->publicId;
    }

    /**
     * @param string|null $publicId
     */
    public function setPublicId(?string $publicId): void
    {
        $this->publicId = $publicId;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime|null $creationDate
     */
    public function setCreationDate(?\DateTime $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdateDate(): ?\DateTime
    {
        return $this->updateDate;
    }

    /**
     * @param \DateTime|null $updateDate
     */
    public function setUpdateDate(?\DateTime $updateDate): void
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return string|null
     */
    public function getCaptureMode(): ?string
    {
        return $this->captureMode;
    }

    /**
     * @param string|null $captureMode
     */
    public function setCaptureMode(?string $captureMode): void
    {
        $this->captureMode = $captureMode;
    }

    /**
     * @return string|null
     */
    public function getMerchantOrderExtRef(): ?string
    {
        return $this->merchantOrderExtRef;
    }

    /**
     * @param string|null $merchantOrderExtRef
     */
    public function setMerchantOrderExtRef(?string $merchantOrderExtRef): void
    {
        $this->merchantOrderExtRef = $merchantOrderExtRef;
    }

    /**
     * @return integer|null
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @param integer|null $amount
     */
    public function setAmount(?int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string|null $currency
     */
    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string|null
     */
    public function getCheckoutUrl(): ?string
    {
        return $this->checkoutUrl;
    }
    
    /**
     * @param string|null $checkoutUrl
     */
    public function setCheckoutUrl(?string $checkoutUrl): void
    {
        $this->checkoutUrl = $checkoutUrl;
    }
    
	/**
	 * @return string|null
	 */
	public function getErrorId(): ?string
	{
		return $this->errorId;
	}

	/**
	 * @param string|null $errorId
	 */
	public function setErrorId(?string $errorId): void
	{
		$this->errorId = $errorId;
	}

    /**
     * @return string|null
     */
    public function getCardLastDigits(): ?string
    {
        return $this->cardLastDigits;
    }

    public function setCardLastDigits(?string $cardLastDigits): void
    {
        $this->cardLastDigits = $cardLastDigits;
    }

    /**
     * @return string|null $cardExpiration
     */
    public function getCardExpiration(): ?string
    {
        return $this->cardExpiration;
    }
    
    /**
     * @param string|null $cardExpiration
     */
    public function setCardExpiration(?string $cardExpiration): void
    {
        $this->cardExpiration = $cardExpiration;
    }

	/**
	 * @return bool
	 */
	public function isSuccess(): bool
	{
		return $this->getErrorId() === null;
	}

    /**
	 * @return string|null
	 */
	public function getTransactionNumber(): ?string
	{
		return $this->id ?? null;
	}
    
    /**
     * @param array $request
     *
     * @return RevolutResponse
     */
    public static function getFromRequest(array $request): RevolutResponse
    {
        $orderAmount = array_key_exists('order_amount', $request) ? $request['order_amount'] : null;
        $cardData = array_key_exists('payments', $request) ? $request['payments'][0]['payment_method']['card'] : null;

        $revolutResponse = new RevolutResponse();
        $revolutResponse->setErrorId(!empty($request['errorId']) ? urldecode($request['errorId']) : null);
        $revolutResponse->setId(!empty($request['id']) ? urldecode($request['id']) : null);
        $revolutResponse->setPublicId(!empty($request['public_id']) ? urldecode($request['public_id']) : null);
        $revolutResponse->setType(!empty($request['type']) ? urldecode($request['type']) : null);
        $revolutResponse->setState(!empty($request['state']) ? urldecode($request['state']) : null);
        $revolutResponse->setCreationDate(!empty($request['created_at']) ? new \DateTime($request['created_at']) : null);
        $revolutResponse->setUpdateDate(!empty($request['updated_at']) ? new \DateTime($request['updated_at']) : null);
        $revolutResponse->setCaptureMode(!empty($request['capture_mode']) ? urldecode($request['capture_mode']) : null);
        $revolutResponse->setMerchantOrderExtRef(!empty($request['merchant_order_ext_ref']) ? urldecode($request['merchant_order_ext_ref']) : null);
        $revolutResponse->setAmount(!empty($orderAmount) ? urldecode($orderAmount['value']) : null);
        $revolutResponse->setCurrency(!empty($orderAmount) ? urldecode($orderAmount['currency']) : null);
        $revolutResponse->setCheckoutUrl(!empty($request['checkout_url']) ? urldecode($request['checkout_url']) : null);
        $revolutResponse->setCardLastDigits(!empty($cardData['card_last_four']) ? urldecode($cardData['card_last_four']) : null);
        $revolutResponse->setCardExpiration(!empty($cardData['card_expiry']) ? urldecode($cardData['card_expiry']) : null);
        
        return $revolutResponse;
    }

}