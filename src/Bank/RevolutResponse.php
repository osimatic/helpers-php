<?php 

namespace Osimatic\Helpers\Bank;

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
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id):void
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
     * @param string $publicId
     */
    public function setPublicId(string $publicId)
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
     * @param string $type
     */
    public function setType(string $type)
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
     * @param string $state
     */
    public function setState(string $state)
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
     * @param \DateTime $creationDate
     */
    public function setCreationDate(\DateTime $creationDate)
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
     * @param \DateTime $updateDate
     */
    public function setUpdateDate(\DateTime $updateDate)
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
     * @param string $captureMode
     */
    public function setCaptureMode(string $captureMode)
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
     * @param string $merchantOrderExtRef
     */
    public function setMerchantOrderExtRef(string $merchantOrderExtRef)
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
     * @param integer $amount
     */
    public function setAmount(int $amount)
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
     * @param string $currency
     */
    public function setCurrency(string $currency)
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
     * @param string $checkoutUrl
     */
    public function setCheckoutUrl(string $checkoutUrl)
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
	 * @return bool
	 */
	public function isSuccess(): bool
	{
		return $this->getErrorId() === null;
	}
    
    /**
     * @param array $request
     *
     * @return RevolutResponse
     */
    public static function getFromRequest(array $request): RevolutResponse
    {
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
        $revolutResponse->setAmount(!empty($request['order_amount']) ? urldecode($request['order_amount']['value']) : null);
        $revolutResponse->setCurrency(!empty($request['order_amount']) ? urldecode($request['order_amount']['currency']) : null);
        $revolutResponse->setCheckoutUrl(!empty($request['checkout_url']) ? urldecode($request['checkout_url']) : null);

        return $revolutResponse;
    }

}