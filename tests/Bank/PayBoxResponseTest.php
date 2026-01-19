<?php

declare(strict_types=1);

namespace Tests\Bank;

use Osimatic\Bank\PayBoxResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class PayBoxResponseTest extends TestCase
{
	/* ===================== Constants ===================== */

	public function testConstant3DSecureVersion1(): void
	{
		$this->assertSame(1, PayBoxResponse::_3D_SECURE_VERSION_1);
	}

	public function testConstant3DSecureVersion2(): void
	{
		$this->assertSame(2, PayBoxResponse::_3D_SECURE_VERSION_2);
	}

	/* ===================== Constructor and basic getters ===================== */

	public function testConstructorInitializesWithDefaults(): void
	{
		$response = new PayBoxResponse();

		$this->assertNull($response->getReference());
		$this->assertNull($response->getResponseCode());
		$this->assertNull($response->getCallNumber());
		$this->assertNull($response->getTransactionNumber());
		$this->assertNull($response->getAuthorisationNumber());
		$this->assertFalse($response->is3DSecureEnabled());
	}

	/* ===================== Setters and getters ===================== */

	public function testSetAndGetReference(): void
	{
		$response = new PayBoxResponse();
		$response->setReference('REF-12345');

		$this->assertSame('REF-12345', $response->getReference());
	}

	public function testSetAndGetResponseCode(): void
	{
		$response = new PayBoxResponse();
		$response->setResponseCode('00000');

		$this->assertSame('00000', $response->getResponseCode());
	}

	public function testSetAndGetCallNumber(): void
	{
		$response = new PayBoxResponse();
		$response->setCallNumber('1234567890');

		$this->assertSame('1234567890', $response->getCallNumber());
	}

	public function testSetAndGetTransactionNumber(): void
	{
		$response = new PayBoxResponse();
		$response->setTransactionNumber('9876543210');

		$this->assertSame('9876543210', $response->getTransactionNumber());
	}

	public function testSetAndGetAuthorisationNumber(): void
	{
		$response = new PayBoxResponse();
		$response->setAuthorisationNumber('AUTH-001');

		$this->assertSame('AUTH-001', $response->getAuthorisationNumber());
	}

	public function testSetAndGetCardType(): void
	{
		$response = new PayBoxResponse();
		$response->setCardType('VISA');

		$this->assertSame('VISA', $response->getCardType());
	}

	public function testSetAndGetCardNumber(): void
	{
		$response = new PayBoxResponse();
		$response->setCardNumber('4111********1111');

		$this->assertSame('4111********1111', $response->getCardNumber());
	}

	public function testSetAndGetCardLastDigits(): void
	{
		$response = new PayBoxResponse();
		$response->setCardLastDigits('1234');

		$this->assertSame('1234', $response->getCardLastDigits());
	}

	public function testSetAndGetCardExpirationDateTime(): void
	{
		$response = new PayBoxResponse();
		$date = new \DateTime('2025-12-31');
		$response->setCardExpirationDateTime($date);

		$this->assertSame($date, $response->getCardExpirationDateTime());
	}

	public function testSetAndGetCardHash(): void
	{
		$response = new PayBoxResponse();
		$response->setCardHash('abc123hash');

		$this->assertSame('abc123hash', $response->getCardHash());
	}

	public function testSetAndGet3DSecureEnabled(): void
	{
		$response = new PayBoxResponse();
		$response->set3DSecureEnabled(true);

		$this->assertTrue($response->is3DSecureEnabled());
	}

	public function testSetAndGet3DSecureVersion(): void
	{
		$response = new PayBoxResponse();
		$response->set3DSecureVersion(PayBoxResponse::_3D_SECURE_VERSION_2);

		$this->assertSame(PayBoxResponse::_3D_SECURE_VERSION_2, $response->get3DSecureVersion());
	}

	public function testSetAndGet3DSecureAuthentication(): void
	{
		$response = new PayBoxResponse();
		$response->set3DSecureAuthentication('Y');

		$this->assertSame('Y', $response->get3DSecureAuthentication());
	}

	/* ===================== Interface methods ===================== */

	public function testGetOrderReference(): void
	{
		$response = new PayBoxResponse();
		$response->setReference('ORDER-12345');

		$this->assertSame('ORDER-12345', $response->getOrderReference());
	}

	public function testGetCardReference(): void
	{
		$response = new PayBoxResponse();
		$response->setCardHash('card_hash_value');

		$this->assertSame('card_hash_value', $response->getCardReference());
	}

	/* ===================== isSuccess() tests ===================== */

	public function testIsSuccessWithSuccessCode(): void
	{
		$response = new PayBoxResponse();
		$response->setResponseCode('00000');

		$this->assertTrue($response->isSuccess());
	}

	public function testIsSuccessWithFailureCode(): void
	{
		$response = new PayBoxResponse();
		$response->setResponseCode('00001');

		$this->assertFalse($response->isSuccess());
	}

	public function testIsSuccessWithNullCode(): void
	{
		$response = new PayBoxResponse();

		$this->assertFalse($response->isSuccess());
	}

	public function testIsSuccessWithEmptyCode(): void
	{
		$response = new PayBoxResponse();
		$response->setResponseCode('');

		$this->assertFalse($response->isSuccess());
	}

	/* ===================== getFromRequest() static factory ===================== */

	public function testGetFromRequestWithCompleteData(): void
	{
		$data = [
			'ref' => 'REF-001',
			'response_code' => '00000',
			'call_nb' => '123456',
			'transact_nb' => '789012',
			'authorizt_nb' => 'AUTH-001',
			'bc_type' => 'CB',
			'bin6' => '411111',
			'bc_ldigit' => '1111',
			'bc_expdate' => '2512',
		];

		$response = PayBoxResponse::getFromRequest($data);

		$this->assertInstanceOf(PayBoxResponse::class, $response);
		$this->assertSame('REF-001', $response->getReference());
		$this->assertSame('00000', $response->getResponseCode());
		$this->assertSame('123456', $response->getCallNumber());
		$this->assertSame('789012', $response->getTransactionNumber());
		$this->assertSame('AUTH-001', $response->getAuthorisationNumber());
		$this->assertSame('CB', $response->getCardType());
		$this->assertSame('411111********1111', $response->getCardNumber());
		$this->assertSame('1111', $response->getCardLastDigits());
	}

	public function testGetFromRequestWithMinimalData(): void
	{
		$data = [
			'ref' => 'REF-MIN',
			'response_code' => '00000',
		];

		$response = PayBoxResponse::getFromRequest($data);

		$this->assertInstanceOf(PayBoxResponse::class, $response);
		$this->assertSame('REF-MIN', $response->getReference());
		$this->assertSame('00000', $response->getResponseCode());
		$this->assertNull($response->getCallNumber());
		$this->assertNull($response->getTransactionNumber());
	}

	public function testGetFromRequestWith3DSecureV1(): void
	{
		$data = [
			'ref' => 'REF-3DS1',
			'response_code' => '00000',
			'3ds' => 'O',
			'3ds_auth' => 'Y',
		];

		$response = PayBoxResponse::getFromRequest($data);

		$this->assertTrue($response->is3DSecureEnabled());
		$this->assertSame(PayBoxResponse::_3D_SECURE_VERSION_1, $response->get3DSecureVersion());
		$this->assertSame('Y', $response->get3DSecureAuthentication());
	}

	public function testGetFromRequestWith3DSecureV2(): void
	{
		$data = [
			'ref' => 'REF-3DS2',
			'response_code' => '00000',
			'3ds' => 'O',
			'3ds_auth' => 'Y',
			'3ds_v' => '2',
		];

		$response = PayBoxResponse::getFromRequest($data);

		$this->assertTrue($response->is3DSecureEnabled());
		$this->assertSame(PayBoxResponse::_3D_SECURE_VERSION_2, $response->get3DSecureVersion());
		$this->assertSame('Y', $response->get3DSecureAuthentication());
	}

	public function testGetFromRequestWithCardHash(): void
	{
		$data = [
			'ref' => 'REF-HASH',
			'response_code' => '00000',
			'card_ref' => 'hash_abc123  2206  ---',
		];

		$response = PayBoxResponse::getFromRequest($data);

		$this->assertSame('hash_abc123', $response->getCardHash());
		$this->assertSame('hash_abc123', $response->getCardReference());
	}

	public function testGetFromRequestParsesExpirationDate(): void
	{
		$data = [
			'ref' => 'REF-DATE',
			'response_code' => '00000',
			'bc_expdate' => '2512',
		];

		$response = PayBoxResponse::getFromRequest($data);

		$expiration = $response->getCardExpirationDateTime();
		$this->assertInstanceOf(\DateTime::class, $expiration);
		$this->assertSame('2025', $expiration->format('Y'));
		$this->assertSame('12', $expiration->format('m'));
	}

	/* ===================== getFromHttpRequest() static factory ===================== */

	public function testGetFromHttpRequestWithQueryParameters(): void
	{
		$request = Request::create(
			'https://example.com/callback',
			'GET',
			[
				'ref' => 'HTTP-REF-001',
				'response_code' => '00000',
				'call_nb' => '111111',
				'transact_nb' => '222222',
			]
		);

		$response = PayBoxResponse::getFromHttpRequest($request);

		$this->assertInstanceOf(PayBoxResponse::class, $response);
		$this->assertSame('HTTP-REF-001', $response->getReference());
		$this->assertSame('00000', $response->getResponseCode());
		$this->assertSame('111111', $response->getCallNumber());
		$this->assertSame('222222', $response->getTransactionNumber());
	}

	public function testGetFromHttpRequestWithPostData(): void
	{
		$request = Request::create(
			'https://example.com/callback',
			'POST',
			[
				'ref' => 'POST-REF-001',
				'response_code' => '00001',
			]
		);

		$response = PayBoxResponse::getFromHttpRequest($request);

		$this->assertInstanceOf(PayBoxResponse::class, $response);
		$this->assertSame('POST-REF-001', $response->getReference());
		$this->assertSame('00001', $response->getResponseCode());
		$this->assertFalse($response->isSuccess());
	}

	/* ===================== Card type scenarios ===================== */

	public function testGetFromRequestWithVisaCardType(): void
	{
		$data = [
			'ref' => 'REF-VISA',
			'response_code' => '00000',
			'bc_type' => 'VISA',
		];

		$response = PayBoxResponse::getFromRequest($data);
		$this->assertSame('VISA', $response->getCardType());
	}

	public function testGetFromRequestWithMastercardCardType(): void
	{
		$data = [
			'ref' => 'REF-MC',
			'response_code' => '00000',
			'bc_type' => 'EUROCARD_MASTERCARD',
		];

		$response = PayBoxResponse::getFromRequest($data);
		$this->assertSame('EUROCARD_MASTERCARD', $response->getCardType());
	}

	public function testGetFromRequestWithAmexCardType(): void
	{
		$data = [
			'ref' => 'REF-AMEX',
			'response_code' => '00000',
			'bc_type' => 'AMEX',
		];

		$response = PayBoxResponse::getFromRequest($data);
		$this->assertSame('AMEX', $response->getCardType());
	}

	public function testGetFromRequestWithCBCardType(): void
	{
		$data = [
			'ref' => 'REF-CB',
			'response_code' => '00000',
			'bc_type' => 'CB',
		];

		$response = PayBoxResponse::getFromRequest($data);
		$this->assertSame('CB', $response->getCardType());
	}

	/* ===================== Edge cases ===================== */

	public function testGetFromRequestWithEmptyArray(): void
	{
		$response = PayBoxResponse::getFromRequest([]);

		$this->assertInstanceOf(PayBoxResponse::class, $response);
		$this->assertNull($response->getReference());
		$this->assertNull($response->getResponseCode());
	}

	public function test3DSecureNotEnabledByDefault(): void
	{
		$response = new PayBoxResponse();
		$this->assertFalse($response->is3DSecureEnabled());
	}

	public function testSet3DSecureEnabledToFalse(): void
	{
		$response = new PayBoxResponse();
		$response->set3DSecureEnabled(true);
		$response->set3DSecureEnabled(false);

		$this->assertFalse($response->is3DSecureEnabled());
	}

	/* ===================== Card number with missing bin6 ===================== */

	public function testGetFromRequestWithoutBin6(): void
	{
		$data = [
			'ref' => 'REF-NO-BIN',
			'response_code' => '00000',
			'bc_ldigit' => '4242',
		];

		$response = PayBoxResponse::getFromRequest($data);
		$this->assertSame('4242', $response->getCardLastDigits());
		$this->assertNull($response->getCardNumber());
	}

	/* ===================== Multiple setters ===================== */

	public function testSetCardTypeMultipleTimes(): void
	{
		$response = new PayBoxResponse();
		$response->setCardType('VISA');
		$this->assertSame('VISA', $response->getCardType());

		$response->setCardType('MASTERCARD');
		$this->assertSame('MASTERCARD', $response->getCardType());
	}

	/* ===================== Different response codes ===================== */

	public function testIsSuccessWithAuthorizationRefused(): void
	{
		$response = new PayBoxResponse();
		$response->setResponseCode('00003');

		$this->assertFalse($response->isSuccess());
	}

	public function testIsSuccessWithInvalidMerchant(): void
	{
		$response = new PayBoxResponse();
		$response->setResponseCode('00008');

		$this->assertFalse($response->isSuccess());
	}

	/* ===================== Set null values ===================== */

	public function testSetReferenceToNull(): void
	{
		$response = new PayBoxResponse();
		$response->setReference('REF-001');
		$response->setReference(null);

		$this->assertNull($response->getReference());
	}

	public function testSetResponseCodeToNull(): void
	{
		$response = new PayBoxResponse();
		$response->setResponseCode('00000');
		$response->setResponseCode(null);

		$this->assertNull($response->getResponseCode());
	}

	/* ===================== 3D Secure authentication statuses ===================== */

	public function testSet3DSecureAuthenticationY(): void
	{
		$response = new PayBoxResponse();
		$response->set3DSecureAuthentication('Y');

		$this->assertSame('Y', $response->get3DSecureAuthentication());
	}

	public function testSet3DSecureAuthenticationN(): void
	{
		$response = new PayBoxResponse();
		$response->set3DSecureAuthentication('N');

		$this->assertSame('N', $response->get3DSecureAuthentication());
	}

	public function testSet3DSecureAuthenticationU(): void
	{
		$response = new PayBoxResponse();
		$response->set3DSecureAuthentication('U');

		$this->assertSame('U', $response->get3DSecureAuthentication());
	}

	public function testSet3DSecureAuthenticationA(): void
	{
		$response = new PayBoxResponse();
		$response->set3DSecureAuthentication('A');

		$this->assertSame('A', $response->get3DSecureAuthentication());
	}

	/* ===================== 3D Secure version scenarios ===================== */

	public function testSet3DSecureVersionV1(): void
	{
		$response = new PayBoxResponse();
		$response->set3DSecureVersion(PayBoxResponse::_3D_SECURE_VERSION_1);

		$this->assertSame(PayBoxResponse::_3D_SECURE_VERSION_1, $response->get3DSecureVersion());
	}

	public function testSet3DSecureVersionToNull(): void
	{
		$response = new PayBoxResponse();
		$response->set3DSecureVersion(PayBoxResponse::_3D_SECURE_VERSION_2);
		$response->set3DSecureVersion(null);

		$this->assertNull($response->get3DSecureVersion());
	}

	/* ===================== Card expiration scenarios ===================== */

	public function testSetCardExpirationDateTimeToNull(): void
	{
		$response = new PayBoxResponse();
		$date = new \DateTime('2025-12-31');
		$response->setCardExpirationDateTime($date);
		$response->setCardExpirationDateTime(null);

		$this->assertNull($response->getCardExpirationDateTime());
	}

	/* ===================== Card hash scenarios ===================== */

	public function testSetCardHashToNull(): void
	{
		$response = new PayBoxResponse();
		$response->setCardHash('hash123');
		$response->setCardHash(null);

		$this->assertNull($response->getCardHash());
	}

	/* ===================== Deprecated method ===================== */

	public function testDeprecatedGetAuthorizationNumber(): void
	{
		$response = new PayBoxResponse();
		$response->setAuthorisationNumber('AUTH-123');

		$this->assertSame('AUTH-123', $response->getAuthorizationNumber());
	}

	public function testDeprecatedSetAuthorizationNumber(): void
	{
		$response = new PayBoxResponse();
		$response->setAuthorizationNumber('AUTH-456');

		$this->assertSame('AUTH-456', $response->getAuthorisationNumber());
	}
}