<?php 

namespace Osimatic\Helpers\Bank;

/**
 * https://developer.revolut.com/docs/api-reference/merchant/#tag/Orders/operation/createOrder
 */
class RevolutResponse {
    private ?string $id = null;
    private ?string $publicId = null;
    private ?string $type = null;
    private ?string $state = null;
    private ?\DateTime $creationDate = null;
    private ?\DateTime $updateDate = null;
    private ?string $captureMode = null;
    private ?string $merchantOrderExtRef = null;
    private ?int $amount = null;
    private ?string $currency = null;
    private ?string $checkoutUrl = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getPublicId(): ?string
    {
        return $this->publicId;
    }

    public function setPublicId(string $publicId)
    {
        $this->publicId = $publicId;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state)
    {
        $this->state = $state;
    }

    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getUpdateDate(): ?\DateTime
    {
        return $this->updateDate;
    }

    public function setUpdateDate(\DateTime $updateDate)
    {
        $this->updateDate = $updateDate;
    }

    public function getCaptureMode(): ?string
    {
        return $this->captureMode;
    }

    public function setCaptureMode(string $captureMode)
    {
        $this->captureMode = $captureMode;
    }

    public function getMerchantOrderExtRef(): ?string
    {
        return $this->merchantOrderExtRef;
    }

    public function setMerchantOrderExtRef(string $merchantOrderExtRef)
    {
        $this->merchantOrderExtRef = $merchantOrderExtRef;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount)
    {
        $this->amount = $amount;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency)
    {
        $this->currency = $currency;
    }

    public function getCheckoutUrl(): ?string
    {
        return $this->checkoutUrl;
    }

    public function setCheckoutUrl(string $checkoutUrl)
    {
        $this->checkoutUrl = $checkoutUrl;
    }

    public static function getFromRequest(array $request): RevolutOrder
    {
        $revolutOrder = new RevolutOrder();
        
        return $revolutOrder;
    }

}