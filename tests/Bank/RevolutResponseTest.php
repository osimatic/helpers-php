<?php

declare(strict_types=1);

namespace Tests\Bank;

use Osimatic\Bank\RevolutResponse;
use PHPUnit\Framework\TestCase;

final class RevolutResponseTest extends TestCase
{
	/* ===================== Constructor and basic getters ===================== */

	public function testConstructorInitializesWithDefaults(): void
	{
		$response = new RevolutResponse();

		$this->assertNull($response->getId());
		$this->assertNull($response->getPublicId());
		$this->assertNull($response->getType());
		$this->assertNull($response->getState());
		$this->assertNull($response->getErrorId());
	}

	/* ===================== Setters and getters ===================== */

	public function testSetAndGetId(): void
	{
		$response = new RevolutResponse();
		$response->setId('order_id_123456');

		$this->assertSame('order_id_123456', $response->getId());
	}

	public function testSetAndGetPublicId(): void
	{
		$response = new RevolutResponse();
		$response->setPublicId('pub_id_789012');

		$this->assertSame('pub_id_789012', $response->getPublicId());
	}

	public function testSetAndGetType(): void
	{
		$response = new RevolutResponse();
		$response->setType('PAYMENT');

		$this->assertSame('PAYMENT', $response->getType());
	}

	public function testSetAndGetState(): void
	{
		$response = new RevolutResponse();
		$response->setState('COMPLETED');

		$this->assertSame('COMPLETED', $response->getState());
	}

	public function testSetAndGetCreationDate(): void
	{
		$response = new RevolutResponse();
		$date = new \DateTime('2025-01-18 10:30:00');
		$response->setCreationDate($date);

		$this->assertSame($date, $response->getCreationDate());
	}

	public function testSetAndGetUpdateDate(): void
	{
		$response = new RevolutResponse();
		$date = new \DateTime('2025-01-18 11:45:00');
		$response->setUpdateDate($date);

		$this->assertSame($date, $response->getUpdateDate());
	}

	public function testSetAndGetCaptureMode(): void
	{
		$response = new RevolutResponse();
		$response->setCaptureMode('AUTOMATIC');

		$this->assertSame('AUTOMATIC', $response->getCaptureMode());
	}

	public function testSetAndGetMerchantOrderExtRef(): void
	{
		$response = new RevolutResponse();
		$response->setMerchantOrderExtRef('ORDER-12345');

		$this->assertSame('ORDER-12345', $response->getMerchantOrderExtRef());
	}

	public function testSetAndGetAmount(): void
	{
		$response = new RevolutResponse();
		$response->setAmount(9999);

		$this->assertSame(9999, $response->getAmount());
	}

	public function testSetAndGetCurrency(): void
	{
		$response = new RevolutResponse();
		$response->setCurrency('EUR');

		$this->assertSame('EUR', $response->getCurrency());
	}

	public function testSetAndGetCheckoutUrl(): void
	{
		$response = new RevolutResponse();
		$url = 'https://checkout.revolut.com/payment/abc123';
		$response->setCheckoutUrl($url);

		$this->assertSame($url, $response->getCheckoutUrl());
	}

	public function testSetAndGetErrorId(): void
	{
		$response = new RevolutResponse();
		$response->setErrorId('ERROR-001');

		$this->assertSame('ERROR-001', $response->getErrorId());
	}

	public function testSetAndGetCardLastDigits(): void
	{
		$response = new RevolutResponse();
		$response->setCardLastDigits('4242');

		$this->assertSame('4242', $response->getCardLastDigits());
	}

	public function testSetAndGetCardExpirationDateTime(): void
	{
		$response = new RevolutResponse();
		$date = new \DateTime('2025-12-31');
		$response->setCardExpirationDateTime($date);

		$this->assertSame($date, $response->getCardExpirationDateTime());
	}

	/* ===================== Interface methods ===================== */

	public function testGetAuthorisationNumber(): void
	{
		$response = new RevolutResponse();
		$response->setId('auth_12345');

		$this->assertNull($response->getAuthorisationNumber());
	}

	public function testGetOrderReference(): void
	{
		$response = new RevolutResponse();
		$response->setMerchantOrderExtRef('ORDER-67890');

		$this->assertSame('ORDER-67890', $response->getOrderReference());
	}

	public function testGetCardReference(): void
	{
		$response = new RevolutResponse();
		$response->setCardLastDigits('9876');

		$this->assertNull($response->getCardReference());
	}

	public function testGetCallNumber(): void
	{
		$response = new RevolutResponse();
		$response->setPublicId('call_11111');

		$this->assertNull($response->getCallNumber());
	}

	public function testGetTransactionNumber(): void
	{
		$response = new RevolutResponse();
		$response->setId('transaction_99999');

		$this->assertSame('transaction_99999', $response->getTransactionNumber());
	}

	/* ===================== isSuccess() tests ===================== */

	public function testIsSuccessWithNoError(): void
	{
		$response = new RevolutResponse();
		$response->setState('COMPLETED');

		$this->assertTrue($response->isSuccess());
	}

	public function testIsSuccessWithError(): void
	{
		$response = new RevolutResponse();
		$response->setErrorId('PAYMENT_FAILED');

		$this->assertFalse($response->isSuccess());
	}

	public function testIsSuccessWithPendingState(): void
	{
		$response = new RevolutResponse();
		$response->setState('PENDING');

		$this->assertTrue($response->isSuccess());
	}

	public function testIsSuccessWithProcessingState(): void
	{
		$response = new RevolutResponse();
		$response->setState('PROCESSING');

		$this->assertTrue($response->isSuccess());
	}

	/* ===================== getFromRequest() static factory ===================== */

	public function testGetFromRequestWithCompleteData(): void
	{
		$data = [
			'id' => 'order_abc123',
			'public_id' => 'pub_def456',
			'type' => 'PAYMENT',
			'state' => 'COMPLETED',
			'created_at' => '2025-01-18T10:30:00.000Z',
			'updated_at' => '2025-01-18T11:45:00.000Z',
			'capture_mode' => 'AUTOMATIC',
			'merchant_order_ext_ref' => 'ORDER-12345',
			'order_amount' => ['value' => 9999, 'currency' => 'EUR'],
			'checkout_url' => 'https://checkout.revolut.com/payment/test',
		];

		$response = RevolutResponse::getFromRequest($data);

		$this->assertInstanceOf(RevolutResponse::class, $response);
		$this->assertSame('order_abc123', $response->getId());
		$this->assertSame('pub_def456', $response->getPublicId());
		$this->assertSame('PAYMENT', $response->getType());
		$this->assertSame('COMPLETED', $response->getState());
		$this->assertSame('AUTOMATIC', $response->getCaptureMode());
		$this->assertSame('ORDER-12345', $response->getMerchantOrderExtRef());
		$this->assertSame(9999, $response->getAmount());
		$this->assertSame('EUR', $response->getCurrency());
		$this->assertSame('https://checkout.revolut.com/payment/test', $response->getCheckoutUrl());
	}

	public function testGetFromRequestWithMinimalData(): void
	{
		$data = [
			'id' => 'order_minimal',
			'state' => 'PENDING',
		];

		$response = RevolutResponse::getFromRequest($data);

		$this->assertInstanceOf(RevolutResponse::class, $response);
		$this->assertSame('order_minimal', $response->getId());
		$this->assertSame('PENDING', $response->getState());
		$this->assertNull($response->getPublicId());
		$this->assertNull($response->getAmount());
	}

	public function testGetFromRequestWithError(): void
	{
		$data = [
			'id' => 'order_error',
			'state' => 'FAILED',
			'errorId' => 'INSUFFICIENT_FUNDS',
		];

		$response = RevolutResponse::getFromRequest($data);

		$this->assertSame('INSUFFICIENT_FUNDS', $response->getErrorId());
		$this->assertFalse($response->isSuccess());
	}

	public function testGetFromRequestParsesCreationDate(): void
	{
		$data = [
			'id' => 'order_date_test',
			'state' => 'PENDING',
			'created_at' => '2025-01-18T10:30:45.123Z',
		];

		$response = RevolutResponse::getFromRequest($data);

		$creationDate = $response->getCreationDate();
		$this->assertInstanceOf(\DateTime::class, $creationDate);
		$this->assertSame('2025-01-18', $creationDate->format('Y-m-d'));
	}

	public function testGetFromRequestParsesUpdateDate(): void
	{
		$data = [
			'id' => 'order_update_test',
			'state' => 'COMPLETED',
			'updated_at' => '2025-01-18T15:20:30.456Z',
		];

		$response = RevolutResponse::getFromRequest($data);

		$updateDate = $response->getUpdateDate();
		$this->assertInstanceOf(\DateTime::class, $updateDate);
		$this->assertSame('2025-01-18', $updateDate->format('Y-m-d'));
	}

	public function testGetFromRequestWithPayments(): void
	{
		$data = [
			'id' => 'order_payments',
			'state' => 'COMPLETED',
			'payments' => [
				[
					'payment_method' => [
						'card' => [
							'card_last_four' => '4242',
							'card_expiry' => '12/2025',
						],
					],
				],
			],
		];

		$response = RevolutResponse::getFromRequest($data);

		$this->assertSame('4242', $response->getCardLastDigits());

		$cardExpiration = $response->getCardExpirationDateTime();
		$this->assertInstanceOf(\DateTime::class, $cardExpiration);
		$this->assertSame('2025', $cardExpiration->format('Y'));
		$this->assertSame('12', $cardExpiration->format('m'));
	}

	public function testGetFromRequestWithEmptyPayments(): void
	{
		$data = [
			'id' => 'order_no_payments',
			'state' => 'PENDING',
			'payments' => [],
		];

		$response = RevolutResponse::getFromRequest($data);

		$this->assertNull($response->getCardLastDigits());
		$this->assertNull($response->getCardExpirationDateTime());
	}

	public function testGetFromRequestWithMissingCardData(): void
	{
		$data = [
			'id' => 'order_no_card',
			'state' => 'COMPLETED',
			'payments' => [
				['payment_method' => []],
			],
		];

		$response = RevolutResponse::getFromRequest($data);

		$this->assertNull($response->getCardLastDigits());
		$this->assertNull($response->getCardExpirationDateTime());
	}

	/* ===================== Order states ===================== */

	public function testGetFromRequestWithPendingState(): void
	{
		$data = ['id' => 'order_1', 'state' => 'PENDING'];
		$response = RevolutResponse::getFromRequest($data);

		$this->assertSame('PENDING', $response->getState());
	}

	public function testGetFromRequestWithProcessingState(): void
	{
		$data = ['id' => 'order_2', 'state' => 'PROCESSING'];
		$response = RevolutResponse::getFromRequest($data);

		$this->assertSame('PROCESSING', $response->getState());
	}

	public function testGetFromRequestWithCompletedState(): void
	{
		$data = ['id' => 'order_3', 'state' => 'COMPLETED'];
		$response = RevolutResponse::getFromRequest($data);

		$this->assertSame('COMPLETED', $response->getState());
	}

	public function testGetFromRequestWithCancelledState(): void
	{
		$data = ['id' => 'order_4', 'state' => 'CANCELLED'];
		$response = RevolutResponse::getFromRequest($data);

		$this->assertSame('CANCELLED', $response->getState());
	}

	public function testGetFromRequestWithFailedState(): void
	{
		$data = ['id' => 'order_5', 'state' => 'FAILED'];
		$response = RevolutResponse::getFromRequest($data);

		$this->assertSame('FAILED', $response->getState());
	}

	/* ===================== Capture modes ===================== */

	public function testSetCaptureModeManual(): void
	{
		$response = new RevolutResponse();
		$response->setCaptureMode('MANUAL');

		$this->assertSame('MANUAL', $response->getCaptureMode());
	}

	public function testSetCaptureModeAutomatic(): void
	{
		$response = new RevolutResponse();
		$response->setCaptureMode('AUTOMATIC');

		$this->assertSame('AUTOMATIC', $response->getCaptureMode());
	}

	/* ===================== Amount edge cases ===================== */

	public function testSetAmountZero(): void
	{
		$response = new RevolutResponse();
		$response->setAmount(0);

		$this->assertSame(0, $response->getAmount());
	}

	public function testSetAmountLarge(): void
	{
		$response = new RevolutResponse();
		$response->setAmount(99999999);

		$this->assertSame(99999999, $response->getAmount());
	}

	public function testGetFromRequestWithOrderAmountObject(): void
	{
		$data = [
			'id' => 'order_amount',
			'state' => 'PENDING',
			'order_amount' => [
				'value' => 12345,
				'currency' => 'GBP',
			],
		];

		$response = RevolutResponse::getFromRequest($data);

		$this->assertSame(12345, $response->getAmount());
		$this->assertSame('GBP', $response->getCurrency());
	}

	public function testGetFromRequestWithMissingOrderAmount(): void
	{
		$data = [
			'id' => 'order_no_amount',
			'state' => 'PENDING',
		];

		$response = RevolutResponse::getFromRequest($data);

		$this->assertNull($response->getAmount());
		$this->assertNull($response->getCurrency());
	}

	/* ===================== Different currencies ===================== */

	public function testSetCurrencyUSD(): void
	{
		$response = new RevolutResponse();
		$response->setCurrency('USD');

		$this->assertSame('USD', $response->getCurrency());
	}

	public function testSetCurrencyGBP(): void
	{
		$response = new RevolutResponse();
		$response->setCurrency('GBP');

		$this->assertSame('GBP', $response->getCurrency());
	}

	public function testSetCurrencyJPY(): void
	{
		$response = new RevolutResponse();
		$response->setCurrency('JPY');

		$this->assertSame('JPY', $response->getCurrency());
	}

	/* ===================== Edge cases ===================== */

	public function testGetFromRequestWithEmptyArray(): void
	{
		$response = RevolutResponse::getFromRequest([]);

		$this->assertInstanceOf(RevolutResponse::class, $response);
		$this->assertNull($response->getId());
		$this->assertNull($response->getState());
	}

	public function testGetFromRequestWithInvalidDateFormat(): void
	{
		$data = [
			'id' => 'order_bad_date',
			'state' => 'PENDING',
			'created_at' => 'INVALID_DATE',
		];

		$response = RevolutResponse::getFromRequest($data);
		$this->assertNull($response->getCreationDate());
	}

	public function testSetErrorIdToNull(): void
	{
		$response = new RevolutResponse();
		$response->setErrorId('ERROR-001');
		$response->setErrorId(null);

		$this->assertNull($response->getErrorId());
		$this->assertTrue($response->isSuccess());
	}

	public function testGetFromRequestWithMissingPaymentMethodCard(): void
	{
		$data = [
			'id' => 'order_no_payment_method',
			'state' => 'COMPLETED',
			'payments' => [
				['other_data' => 'value'],
			],
		];

		$response = RevolutResponse::getFromRequest($data);

		$this->assertNull($response->getCardLastDigits());
	}

	/* ===================== Checkout URL scenarios ===================== */

	public function testSetCheckoutUrlWithQueryParams(): void
	{
		$response = new RevolutResponse();
		$url = 'https://checkout.revolut.com/payment/abc123?token=xyz&lang=en';
		$response->setCheckoutUrl($url);

		$this->assertSame($url, $response->getCheckoutUrl());
	}

	public function testSetCheckoutUrlEmpty(): void
	{
		$response = new RevolutResponse();
		$response->setCheckoutUrl('');

		$this->assertSame('', $response->getCheckoutUrl());
	}

	/* ===================== Set null values ===================== */

	public function testSetIdToNull(): void
	{
		$response = new RevolutResponse();
		$response->setId('order_123');
		$response->setId(null);

		$this->assertNull($response->getId());
	}

	public function testSetPublicIdToNull(): void
	{
		$response = new RevolutResponse();
		$response->setPublicId('pub_456');
		$response->setPublicId(null);

		$this->assertNull($response->getPublicId());
	}

	public function testSetStateToNull(): void
	{
		$response = new RevolutResponse();
		$response->setState('COMPLETED');
		$response->setState(null);

		$this->assertNull($response->getState());
	}

	public function testSetCreationDateToNull(): void
	{
		$response = new RevolutResponse();
		$date = new \DateTime();
		$response->setCreationDate($date);
		$response->setCreationDate(null);

		$this->assertNull($response->getCreationDate());
	}

	public function testSetUpdateDateToNull(): void
	{
		$response = new RevolutResponse();
		$date = new \DateTime();
		$response->setUpdateDate($date);
		$response->setUpdateDate(null);

		$this->assertNull($response->getUpdateDate());
	}

	public function testSetAmountToNull(): void
	{
		$response = new RevolutResponse();
		$response->setAmount(5000);
		$response->setAmount(null);

		$this->assertNull($response->getAmount());
	}

	public function testSetCurrencyToNull(): void
	{
		$response = new RevolutResponse();
		$response->setCurrency('EUR');
		$response->setCurrency(null);

		$this->assertNull($response->getCurrency());
	}

	public function testSetCardLastDigitsToNull(): void
	{
		$response = new RevolutResponse();
		$response->setCardLastDigits('1234');
		$response->setCardLastDigits(null);

		$this->assertNull($response->getCardLastDigits());
	}

	/* ===================== Deprecated methods ===================== */

	public function testDeprecatedGetCardExpiration(): void
	{
		$response = new RevolutResponse();
		$date = new \DateTime('2025-12-31');
		$response->setCardExpirationDateTime($date);

		$this->assertSame($date, $response->getCardExpiration());
	}

	public function testDeprecatedSetCardExpiration(): void
	{
		$response = new RevolutResponse();
		$date = new \DateTime('2025-06-30');
		$response->setCardExpiration($date);

		$this->assertSame($date, $response->getCardExpirationDateTime());
	}
}