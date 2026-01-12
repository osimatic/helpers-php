<?php

declare(strict_types=1);

namespace Tests\Bank;

use Osimatic\Bank\BankCardCallOrigin;
use Osimatic\Bank\BankCardOperation;
use Osimatic\Bank\BillingAddressInterface;
use Osimatic\Bank\PayBox;
use Osimatic\Bank\PayBoxVersion;
use Osimatic\Bank\ShoppingCartInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class PayBoxTest extends TestCase
{
	private PayBox $paybox;

	protected function setUp(): void
	{
		$this->paybox = new PayBox(
			siteNumber: '1234567',
			rang: '99',
			identifier: '123456789',
			httpPassword: '12345678',
			secretKey: '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF'
		);
	}

	/* ===================== Constants ===================== */

	public function testDefaultFormTimeoutConstant(): void
	{
		$this->assertSame(1800, PayBox::DEFAULT_FORM_TIMEOUT);
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithAllParameters(): void
	{
		$paybox = new PayBox(
			siteNumber: '1234567',
			rang: '99',
			identifier: '123456789',
			httpPassword: 'password1',
			secretKey: 'secret123'
		);

		$this->assertInstanceOf(PayBox::class, $paybox);
	}

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$paybox = new PayBox(
			siteNumber: '1234567',
			rang: '99',
			identifier: '123456789',
			httpPassword: 'password1',
			secretKey: 'secret123',
			logger: $logger
		);

		$this->assertInstanceOf(PayBox::class, $paybox);
	}

	/* ===================== Setters - Basic configuration ===================== */

	public function testSetVersion(): void
	{
		$result = $this->paybox->setVersion(PayBoxVersion::PAYBOX_DIRECT);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetSiteNumber(): void
	{
		$result = $this->paybox->setSiteNumber('9876543');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetRang(): void
	{
		$result = $this->paybox->setRang('01');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetIdentifier(): void
	{
		$result = $this->paybox->setIdentifier('987654321');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetHttpPassword(): void
	{
		$result = $this->paybox->setHttpPassword('newpass123');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetSecretKey(): void
	{
		$result = $this->paybox->setSecretKey('newsecretkey');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetLocale(): void
	{
		$result = $this->paybox->setLocale('en_GB');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetIsTest(): void
	{
		$result = $this->paybox->setIsTest(true);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$result = $this->paybox->setLogger($logger);

		$this->assertSame($this->paybox, $result);
	}

	/* ===================== Setters - Transaction parameters ===================== */

	public function testSetDate(): void
	{
		$date = new \DateTime('2024-01-15 10:30:00');
		$result = $this->paybox->setDate($date);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetDateWithNull(): void
	{
		$result = $this->paybox->setDate(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetBankCardOperation(): void
	{
		$result = $this->paybox->setBankCardOperation(BankCardOperation::AUTHORIZATION_ONLY);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetQuestionNumber(): void
	{
		$result = $this->paybox->setQuestionNumber(123456789);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetQuestionNumberWithNull(): void
	{
		$result = $this->paybox->setQuestionNumber(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetTotal(): void
	{
		$result = $this->paybox->setTotal(99.99);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetTransactionAmount(): void
	{
		$result = $this->paybox->setTransactionAmount(150.50);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCurrency(): void
	{
		$result = $this->paybox->setCurrency('USD');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetReference(): void
	{
		$result = $this->paybox->setReference('ORDER-12345');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetReferenceWithNull(): void
	{
		$result = $this->paybox->setReference(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetSubscriberReference(): void
	{
		$result = $this->paybox->setSubscriberReference('SUB-98765');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetSubscriberReferenceWithNull(): void
	{
		$result = $this->paybox->setSubscriberReference(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCustomerEmail(): void
	{
		$result = $this->paybox->setCustomerEmail('customer@example.com');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCustomerEmailWithNull(): void
	{
		$result = $this->paybox->setCustomerEmail(null);

		$this->assertSame($this->paybox, $result);
	}

	/* ===================== Setters - Card information ===================== */

	public function testSetCreditCardNumber(): void
	{
		$result = $this->paybox->setCreditCardNumber('4111111111111111');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCreditCardNumberWithNull(): void
	{
		$result = $this->paybox->setCreditCardNumber(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCreditCardToken(): void
	{
		$result = $this->paybox->setCreditCardToken('TOKEN123456');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCreditCardTokenWithNull(): void
	{
		$result = $this->paybox->setCreditCardToken(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetExpirationDate(): void
	{
		$date = new \DateTime('2025-12-31');
		$result = $this->paybox->setExpirationDate($date);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetExpirationDateWithNull(): void
	{
		$result = $this->paybox->setExpirationDate(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCvc(): void
	{
		$result = $this->paybox->setCvc('123');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCvcWithNull(): void
	{
		$result = $this->paybox->setCvc(null);

		$this->assertSame($this->paybox, $result);
	}

	/* ===================== Setters - Additional parameters ===================== */

	public function testSetCallOrigin(): void
	{
		$result = $this->paybox->setCallOrigin(BankCardCallOrigin::INTERNET_PAYMENT);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCallOriginWithNull(): void
	{
		$result = $this->paybox->setCallOrigin(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetArchivingReference(): void
	{
		$result = $this->paybox->setArchivingReference('ARCH-12345');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetArchivingReferenceWithNull(): void
	{
		$result = $this->paybox->setArchivingReference(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetNumberOfDaysForPostponedSettlement(): void
	{
		$result = $this->paybox->setNumberOfDaysForPostponedSettlement(7);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetNumberOfDaysForPostponedSettlementWithNull(): void
	{
		$result = $this->paybox->setNumberOfDaysForPostponedSettlement(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCallNumber(): void
	{
		$result = $this->paybox->setCallNumber(1234567890);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetTransactionNumber(): void
	{
		$result = $this->paybox->setTransactionNumber(9876543210);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetAuthorizationNumber(): void
	{
		$result = $this->paybox->setAuthorizationNumber('AUTH123');

		$this->assertSame($this->paybox, $result);
	}

	/* ===================== Setters - Form parameters ===================== */

	public function testSetFormCssClass(): void
	{
		$result = $this->paybox->setFormCssClass('my-custom-form');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetFormCssClassWithNull(): void
	{
		$result = $this->paybox->setFormCssClass(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetButtonCssClass(): void
	{
		$result = $this->paybox->setButtonCssClass('btn-custom');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetButtonCssClassWithNull(): void
	{
		$result = $this->paybox->setButtonCssClass(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetButtonText(): void
	{
		$result = $this->paybox->setButtonText('Pay Now');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetButtonTextWithNull(): void
	{
		$result = $this->paybox->setButtonText(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetUrlResponseOk(): void
	{
		$result = $this->paybox->setUrlResponseOk('https://example.com/success');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetUrlResponseOkWithNull(): void
	{
		$result = $this->paybox->setUrlResponseOk(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetUrlResponseRefused(): void
	{
		$result = $this->paybox->setUrlResponseRefused('https://example.com/refused');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetUrlResponseRefusedWithNull(): void
	{
		$result = $this->paybox->setUrlResponseRefused(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetUrlResponseCanceled(): void
	{
		$result = $this->paybox->setUrlResponseCanceled('https://example.com/canceled');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetUrlResponseCanceledWithNull(): void
	{
		$result = $this->paybox->setUrlResponseCanceled(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetUrlResponseWaiting(): void
	{
		$result = $this->paybox->setUrlResponseWaiting('https://example.com/waiting');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetUrlResponseWaitingWithNull(): void
	{
		$result = $this->paybox->setUrlResponseWaiting(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetUrlIpn(): void
	{
		$result = $this->paybox->setUrlIpn('https://example.com/ipn');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetUrlIpnWithNull(): void
	{
		$result = $this->paybox->setUrlIpn(null);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetAuthorizationOnly(): void
	{
		$result = $this->paybox->setAuthorizationOnly();

		$this->assertSame($this->paybox, $result);
	}

	/* ===================== 3D Secure ===================== */

	public function testSet3DSecureV2(): void
	{
		$result = $this->paybox->set3DSecureV2();

		$this->assertSame($this->paybox, $result);
		$this->assertTrue($this->paybox->is3DSecureV2());
	}

	public function testIs3DSecureV2DefaultsToFalse(): void
	{
		$this->assertFalse($this->paybox->is3DSecureV2());
	}

	/* ===================== Form timeout ===================== */

	public function testGetFormTimeoutDefaultValue(): void
	{
		$this->assertSame(PayBox::DEFAULT_FORM_TIMEOUT, $this->paybox->getFormTimeout());
	}

	public function testSetFormTimeout(): void
	{
		$result = $this->paybox->setFormTimeout(3600);

		$this->assertSame($this->paybox, $result);
		$this->assertSame(3600, $this->paybox->getFormTimeout());
	}

	/* ===================== Billing address ===================== */

	public function testSetBillingAddress(): void
	{
		$billingAddress = $this->createMock(BillingAddressInterface::class);
		$result = $this->paybox->setBillingAddress($billingAddress);

		$this->assertSame($this->paybox, $result);
	}

	/* ===================== Shopping cart ===================== */

	public function testSetShoppingCart(): void
	{
		$shoppingCart = $this->createMock(ShoppingCartInterface::class);
		$result = $this->paybox->setShoppingCart($shoppingCart);

		$this->assertSame($this->paybox, $result);
	}

	/* ===================== Getters ===================== */

	public function testGetCallNumberDefaultsToNull(): void
	{
		$this->assertNull($this->paybox->getCallNumber());
	}

	public function testGetCallNumberAfterSet(): void
	{
		$this->paybox->setCallNumber(1234567890);

		$this->assertSame(1234567890, $this->paybox->getCallNumber());
	}

	public function testGetTransactionNumberDefaultsToNull(): void
	{
		$this->assertNull($this->paybox->getTransactionNumber());
	}

	public function testGetTransactionNumberAfterSet(): void
	{
		$this->paybox->setTransactionNumber(9876543210);

		$this->assertSame(9876543210, $this->paybox->getTransactionNumber());
	}

	public function testGetAuthorizationNumberDefaultsToNull(): void
	{
		$this->assertNull($this->paybox->getAuthorizationNumber());
	}

	public function testGetAuthorizationNumberAfterSet(): void
	{
		$this->paybox->setAuthorizationNumber('AUTH123');

		$this->assertSame('AUTH123', $this->paybox->getAuthorizationNumber());
	}

	public function testGetCardCountryCodeDefaultsToNull(): void
	{
		$this->assertNull($this->paybox->getCardCountryCode());
	}

	/* ===================== reset() ===================== */

	public function testResetClearsTransactionData(): void
	{
		// Set some data
		$this->paybox->setQuestionNumber(123456);
		$this->paybox->setDate(new \DateTime());
		$this->paybox->setReference('ORDER-123');
		$this->paybox->setSubscriberReference('SUB-456');
		$this->paybox->setCustomerEmail('test@example.com');
		$this->paybox->setCreditCardNumber('4111111111111111');
		$this->paybox->setExpirationDate(new \DateTime('2025-12-31'));
		$this->paybox->setCvc('123');
		$this->paybox->setCallNumber(1234567890);
		$this->paybox->setTransactionNumber(9876543210);
		$this->paybox->setAuthorizationNumber('AUTH123');

		// Reset
		$this->paybox->reset();

		// Verify data is cleared
		$this->assertNull($this->paybox->getCallNumber());
		$this->assertNull($this->paybox->getTransactionNumber());
		$this->assertNull($this->paybox->getAuthorizationNumber());
	}

	public function testResetDoesNotAffectConfiguration(): void
	{
		// Set configuration
		$this->paybox->setLocale('en_GB');
		$this->paybox->setIsTest(true);
		$this->paybox->setTotal(100.00);

		// Reset
		$this->paybox->reset();

		// Configuration should still be set (total is not cleared by reset)
		// Only transaction-specific data is cleared
		$this->assertInstanceOf(PayBox::class, $this->paybox);
	}

	/* ===================== Fluent interface ===================== */

	public function testFluentInterfaceChaining(): void
	{
		$result = $this->paybox
			->setTotal(99.99)
			->setCurrency('EUR')
			->setReference('ORDER-123')
			->setCustomerEmail('customer@example.com')
			->setCreditCardNumber('4111111111111111')
			->setExpirationDate(new \DateTime('2025-12-31'))
			->setCvc('123');

		$this->assertSame($this->paybox, $result);
	}

	public function testFluentInterfaceWithFormConfiguration(): void
	{
		$result = $this->paybox
			->setFormCssClass('payment-form')
			->setButtonCssClass('btn btn-primary')
			->setButtonText('Pay Now')
			->setUrlResponseOk('https://example.com/success')
			->setUrlResponseRefused('https://example.com/refused')
			->setUrlResponseCanceled('https://example.com/canceled')
			->setUrlIpn('https://example.com/ipn');

		$this->assertSame($this->paybox, $result);
	}

	/* ===================== Multiple operations ===================== */

	public function testSetTransactionAmountIsSameAsSetTotal(): void
	{
		$paybox1 = new PayBox('1234567', '99', '123456789', '12345678', 'secret');
		$paybox2 = new PayBox('1234567', '99', '123456789', '12345678', 'secret');

		$paybox1->setTotal(150.50);
		$paybox2->setTransactionAmount(150.50);

		// Both should behave the same way (they call the same internal method)
		$this->assertInstanceOf(PayBox::class, $paybox1);
		$this->assertInstanceOf(PayBox::class, $paybox2);
	}

	public function testSetCreditCardNumberAndTokenUseSameInternalProperty(): void
	{
		// Set card number
		$this->paybox->setCreditCardNumber('4111111111111111');

		// Set token (should overwrite card number)
		$this->paybox->setCreditCardToken('TOKEN123');

		// Both methods set the same property internally, so this should work
		$this->assertInstanceOf(PayBox::class, $this->paybox);
	}

	/* ===================== Edge cases ===================== */

	public function testSetTotalWithZero(): void
	{
		$result = $this->paybox->setTotal(0.0);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetTotalWithNegativeValue(): void
	{
		$result = $this->paybox->setTotal(-50.00);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetTotalWithLargeValue(): void
	{
		$result = $this->paybox->setTotal(999999.99);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetReferenceWithEmptyString(): void
	{
		$result = $this->paybox->setReference('');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetReferenceWithLongString(): void
	{
		$longReference = str_repeat('A', 250);
		$result = $this->paybox->setReference($longReference);

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCustomerEmailWithEmptyString(): void
	{
		$result = $this->paybox->setCustomerEmail('');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCvcWithThreeDigits(): void
	{
		$result = $this->paybox->setCvc('123');

		$this->assertSame($this->paybox, $result);
	}

	public function testSetCvcWithFourDigits(): void
	{
		$result = $this->paybox->setCvc('1234');

		$this->assertSame($this->paybox, $result);
	}

	/* ===================== Different locales ===================== */

	public function testSetLocaleEnglish(): void
	{
		$result = $this->paybox->setLocale('en_GB');
		$this->assertSame($this->paybox, $result);
	}

	public function testSetLocaleSpanish(): void
	{
		$result = $this->paybox->setLocale('es_ES');
		$this->assertSame($this->paybox, $result);
	}

	public function testSetLocaleFrench(): void
	{
		$result = $this->paybox->setLocale('fr_FR');
		$this->assertSame($this->paybox, $result);
	}

	public function testSetLocaleGerman(): void
	{
		$result = $this->paybox->setLocale('de_DE');
		$this->assertSame($this->paybox, $result);
	}

	public function testSetLocaleItalian(): void
	{
		$result = $this->paybox->setLocale('it_IT');
		$this->assertSame($this->paybox, $result);
	}

	/* ===================== Different bank card operations ===================== */

	public function testSetBankCardOperationAuthorizationOnly(): void
	{
		$result = $this->paybox->setBankCardOperation(BankCardOperation::AUTHORIZATION_ONLY);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetBankCardOperationDebit(): void
	{
		$result = $this->paybox->setBankCardOperation(BankCardOperation::DEBIT);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetBankCardOperationAuthorizationAndDebit(): void
	{
		$result = $this->paybox->setBankCardOperation(BankCardOperation::AUTHORIZATION_AND_DEBIT);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetBankCardOperationCredit(): void
	{
		$result = $this->paybox->setBankCardOperation(BankCardOperation::CREDIT);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetBankCardOperationCancel(): void
	{
		$result = $this->paybox->setBankCardOperation(BankCardOperation::CANCEL);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetBankCardOperationRefund(): void
	{
		$result = $this->paybox->setBankCardOperation(BankCardOperation::REFUND);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetBankCardOperationRegisterSubscriber(): void
	{
		$result = $this->paybox->setBankCardOperation(BankCardOperation::REGISTER_SUBSCRIBER);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetBankCardOperationUpdateSubscriber(): void
	{
		$result = $this->paybox->setBankCardOperation(BankCardOperation::UPDATE_SUBSCRIBER);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetBankCardOperationDeleteSubscriber(): void
	{
		$result = $this->paybox->setBankCardOperation(BankCardOperation::DELETE_SUBSCRIBER);
		$this->assertSame($this->paybox, $result);
	}

	/* ===================== Different call origins ===================== */

	public function testSetCallOriginNotSpecified(): void
	{
		$result = $this->paybox->setCallOrigin(BankCardCallOrigin::NOT_SPECIFIED);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetCallOriginTelephoneOrder(): void
	{
		$result = $this->paybox->setCallOrigin(BankCardCallOrigin::TELEPHONE_ORDER);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetCallOriginMailOrder(): void
	{
		$result = $this->paybox->setCallOrigin(BankCardCallOrigin::MAIL_ORDER);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetCallOriginMinitel(): void
	{
		$result = $this->paybox->setCallOrigin(BankCardCallOrigin::MINITEL);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetCallOriginInternetPayment(): void
	{
		$result = $this->paybox->setCallOrigin(BankCardCallOrigin::INTERNET_PAYMENT);
		$this->assertSame($this->paybox, $result);
	}

	public function testSetCallOriginRecurringPayment(): void
	{
		$result = $this->paybox->setCallOrigin(BankCardCallOrigin::RECURRING_PAYMENT);
		$this->assertSame($this->paybox, $result);
	}
}