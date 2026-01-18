<?php

declare(strict_types=1);

namespace Tests\Bank;

use Osimatic\Bank\BankCardOperation;
use Osimatic\Bank\Revolut;
use Osimatic\Bank\RevolutResponse;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class RevolutTest extends TestCase
{
	private Revolut $revolut;
	private string $testPublicKey = 'pk_test_1234567890';
	private string $testSecretKey = 'sk_test_0987654321';

	protected function setUp(): void
	{
		$this->revolut = new Revolut(
			publicKey: $this->testPublicKey,
			secretKey: $this->testSecretKey
		);
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithRequiredParameters(): void
	{
		$revolut = new Revolut(
			publicKey: 'pk_test_abc',
			secretKey: 'sk_test_xyz'
		);

		$this->assertInstanceOf(Revolut::class, $revolut);
	}

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$revolut = new Revolut(
			publicKey: 'pk_test_abc',
			secretKey: 'sk_test_xyz',
			logger: $logger
		);

		$this->assertInstanceOf(Revolut::class, $revolut);
	}

	/* ===================== Setters - Basic configuration ===================== */

	public function testSetPublicKey(): void
	{
		$result = $this->revolut->setPublicKey('pk_live_new_key');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetSecretKey(): void
	{
		$result = $this->revolut->setSecretKey('sk_live_new_key');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$result = $this->revolut->setLogger($logger);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetIsTest(): void
	{
		$result = $this->revolut->setIsTest(true);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetIsTestToFalse(): void
	{
		$result = $this->revolut->setIsTest(false);

		$this->assertSame($this->revolut, $result);
	}

	/* ===================== Setters - Transaction parameters ===================== */

	public function testSetAmount(): void
	{
		$result = $this->revolut->setAmount(9999);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetAmountWithZero(): void
	{
		$result = $this->revolut->setAmount(0);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetAmountWithLargeValue(): void
	{
		$result = $this->revolut->setAmount(99999999);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetPurchaseReference(): void
	{
		$result = $this->revolut->setPurchaseReference('ORDER-12345');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetPurchaseReferenceWithLongString(): void
	{
		$longRef = str_repeat('A', 250);
		$result = $this->revolut->setPurchaseReference($longRef);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetPurchaseReferenceWithSpecialCharacters(): void
	{
		$result = $this->revolut->setPurchaseReference('ORDER-2025-#123@ABC');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetBankCardOperation(): void
	{
		$result = $this->revolut->setBankCardOperation(BankCardOperation::AUTHORIZATION_ONLY);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetBankCardOperationAuthorizationAndDebit(): void
	{
		$result = $this->revolut->setBankCardOperation(BankCardOperation::AUTHORIZATION_AND_DEBIT);

		$this->assertSame($this->revolut, $result);
	}

	/* ===================== Fluent interface ===================== */

	public function testFluentInterfaceChaining(): void
	{
		$result = $this->revolut
			->setIsTest(true)
			->setPublicKey('pk_test_new')
			->setSecretKey('sk_test_new')
			->setAmount(4999)
			->setPurchaseReference('ORDER-789')
			->setBankCardOperation(BankCardOperation::AUTHORIZATION_ONLY);

		$this->assertSame($this->revolut, $result);
	}

	/* ===================== Bank card operations ===================== */

	public function testSetBankCardOperationAuthorizationOnly(): void
	{
		$result = $this->revolut->setBankCardOperation(BankCardOperation::AUTHORIZATION_ONLY);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetBankCardOperationDebit(): void
	{
		$result = $this->revolut->setBankCardOperation(BankCardOperation::DEBIT);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetBankCardOperationCredit(): void
	{
		$result = $this->revolut->setBankCardOperation(BankCardOperation::CREDIT);

		$this->assertSame($this->revolut, $result);
	}

	/* ===================== Configuration scenarios ===================== */

	public function testSandboxConfiguration(): void
	{
		$revolut = new Revolut(
			publicKey: 'pk_sandbox_test',
			secretKey: 'sk_sandbox_test'
		);

		$result = $revolut
			->setIsTest(true)
			->setAmount(1000)
			->setPurchaseReference('TEST-SANDBOX-001');

		$this->assertInstanceOf(Revolut::class, $result);
	}

	public function testProductionConfiguration(): void
	{
		$revolut = new Revolut(
			publicKey: 'pk_live_production',
			secretKey: 'sk_live_production'
		);

		$result = $revolut
			->setIsTest(false)
			->setAmount(29999)
			->setPurchaseReference('PROD-ORDER-001');

		$this->assertInstanceOf(Revolut::class, $result);
	}

	public function testAuthorizationOnlyConfiguration(): void
	{
		$result = $this->revolut
			->setIsTest(true)
			->setBankCardOperation(BankCardOperation::AUTHORIZATION_ONLY)
			->setAmount(15000)
			->setPurchaseReference('AUTH-ONLY-001');

		$this->assertSame($this->revolut, $result);
	}

	public function testAuthorizationAndDebitConfiguration(): void
	{
		$result = $this->revolut
			->setIsTest(true)
			->setBankCardOperation(BankCardOperation::AUTHORIZATION_AND_DEBIT)
			->setAmount(25000)
			->setPurchaseReference('AUTH-DEBIT-001');

		$this->assertSame($this->revolut, $result);
	}

	/* ===================== Edge cases ===================== */

	public function testSetAmountMinimum(): void
	{
		// Minimum amount should be 1 cent
		$result = $this->revolut->setAmount(1);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetPurchaseReferenceEmpty(): void
	{
		$result = $this->revolut->setPurchaseReference('');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetPurchaseReferenceWithUnicode(): void
	{
		$result = $this->revolut->setPurchaseReference('COMMANDE-€-123');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetPurchaseReferenceWithWhitespace(): void
	{
		$result = $this->revolut->setPurchaseReference('ORDER 123 ABC');

		$this->assertSame($this->revolut, $result);
	}

	/* ===================== Multiple configurations ===================== */

	public function testCompletePaymentConfiguration(): void
	{
		$logger = $this->createMock(LoggerInterface::class);

		$result = $this->revolut
			->setLogger($logger)
			->setIsTest(false)
			->setPublicKey('pk_live_complete')
			->setSecretKey('sk_live_complete')
			->setAmount(9999)
			->setPurchaseReference('COMPLETE-ORDER-123')
			->setBankCardOperation(BankCardOperation::AUTHORIZATION_AND_DEBIT);

		$this->assertSame($this->revolut, $result);
	}

	public function testAuthorizationWorkflow(): void
	{
		$result = $this->revolut
			->setIsTest(true)
			->setBankCardOperation(BankCardOperation::AUTHORIZATION_ONLY)
			->setAmount(50000)
			->setPurchaseReference('AUTH-WORKFLOW-001');

		$this->assertSame($this->revolut, $result);
	}

	/* ===================== Validation scenarios ===================== */

	public function testCaptureWithEmptyOrderId(): void
	{
		$result = $this->revolut->capture('');

		$this->assertNull($result);
	}

	public function testGetOrderWithEmptyOrderId(): void
	{
		$result = $this->revolut->getOrder('');

		$this->assertNull($result);
	}

	/* ===================== Different amounts ===================== */

	public function testSetAmountOneEuro(): void
	{
		// 100 cents = 1.00 EUR
		$result = $this->revolut->setAmount(100);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetAmountTenEuros(): void
	{
		// 1000 cents = 10.00 EUR
		$result = $this->revolut->setAmount(1000);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetAmountOneHundredEuros(): void
	{
		// 10000 cents = 100.00 EUR
		$result = $this->revolut->setAmount(10000);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetAmountWithOddCents(): void
	{
		// 1234 cents = 12.34 EUR
		$result = $this->revolut->setAmount(1234);

		$this->assertSame($this->revolut, $result);
	}

	/* ===================== Different purchase references ===================== */

	public function testSetPurchaseReferenceNumeric(): void
	{
		$result = $this->revolut->setPurchaseReference('123456789');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetPurchaseReferenceAlphanumeric(): void
	{
		$result = $this->revolut->setPurchaseReference('ABC123XYZ789');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetPurchaseReferenceWithDashes(): void
	{
		$result = $this->revolut->setPurchaseReference('ORDER-2025-01-18-001');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetPurchaseReferenceWithUnderscores(): void
	{
		$result = $this->revolut->setPurchaseReference('order_ref_12345');

		$this->assertSame($this->revolut, $result);
	}

	/* ===================== API key formats ===================== */

	public function testSetPublicKeyWithTestPrefix(): void
	{
		$result = $this->revolut->setPublicKey('pk_test_1234567890abcdef');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetPublicKeyWithLivePrefix(): void
	{
		$result = $this->revolut->setPublicKey('pk_live_1234567890abcdef');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetSecretKeyWithTestPrefix(): void
	{
		$result = $this->revolut->setSecretKey('sk_test_1234567890abcdef');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetSecretKeyWithLivePrefix(): void
	{
		$result = $this->revolut->setSecretKey('sk_live_1234567890abcdef');

		$this->assertSame($this->revolut, $result);
	}

	public function testSetPublicKeyWithLongString(): void
	{
		$longKey = 'pk_test_' . str_repeat('a', 128);
		$result = $this->revolut->setPublicKey($longKey);

		$this->assertSame($this->revolut, $result);
	}

	public function testSetSecretKeyWithLongString(): void
	{
		$longKey = 'sk_test_' . str_repeat('b', 128);
		$result = $this->revolut->setSecretKey($longKey);

		$this->assertSame($this->revolut, $result);
	}

	/* ===================== Logger scenarios ===================== */

	public function testSetLoggerMultipleTimes(): void
	{
		$logger1 = $this->createMock(LoggerInterface::class);
		$logger2 = $this->createMock(LoggerInterface::class);

		$result1 = $this->revolut->setLogger($logger1);
		$this->assertSame($this->revolut, $result1);

		$result2 = $this->revolut->setLogger($logger2);
		$this->assertSame($this->revolut, $result2);
	}

	/* ===================== Test mode scenarios ===================== */

	public function testSwitchBetweenTestAndProduction(): void
	{
		// Start in test mode
		$this->revolut->setIsTest(true);
		$this->assertInstanceOf(Revolut::class, $this->revolut);

		// Switch to production
		$this->revolut->setIsTest(false);
		$this->assertInstanceOf(Revolut::class, $this->revolut);

		// Switch back to test
		$this->revolut->setIsTest(true);
		$this->assertInstanceOf(Revolut::class, $this->revolut);
	}

	/* ===================== Complete payment workflows ===================== */

	public function testCompleteAuthorizationAndCaptureWorkflow(): void
	{
		// Step 1: Configure for authorization only
		$revolut = $this->revolut
			->setIsTest(true)
			->setBankCardOperation(BankCardOperation::AUTHORIZATION_ONLY)
			->setAmount(50000)
			->setPurchaseReference('AUTH-CAPTURE-WORKFLOW-001');

		$this->assertInstanceOf(Revolut::class, $revolut);

		// Step 2: Test capture with empty order ID (should fail)
		$captureResult = $revolut->capture('');
		$this->assertNull($captureResult);
	}

	public function testCompleteDirectPaymentWorkflow(): void
	{
		// Configure for direct payment (authorization + debit)
		$revolut = $this->revolut
			->setIsTest(true)
			->setBankCardOperation(BankCardOperation::AUTHORIZATION_AND_DEBIT)
			->setAmount(29999)
			->setPurchaseReference('DIRECT-PAYMENT-001');

		$this->assertInstanceOf(Revolut::class, $revolut);
	}

	/* ===================== Configuration validation ===================== */

	public function testMinimumValidConfiguration(): void
	{
		$revolut = new Revolut(
			publicKey: 'pk',
			secretKey: 'sk'
		);

		$result = $revolut
			->setAmount(1)
			->setPurchaseReference('MIN-CONFIG');

		$this->assertInstanceOf(Revolut::class, $result);
	}

	public function testMaximumConfiguration(): void
	{
		$logger = $this->createMock(LoggerInterface::class);

		$revolut = new Revolut(
			publicKey: 'pk_live_' . str_repeat('x', 100),
			secretKey: 'sk_live_' . str_repeat('y', 100),
			logger: $logger
		);

		$result = $revolut
			->setIsTest(false)
			->setAmount(99999999)
			->setPurchaseReference(str_repeat('Z', 250))
			->setBankCardOperation(BankCardOperation::AUTHORIZATION_ONLY);

		$this->assertInstanceOf(Revolut::class, $result);
	}

	/* ===================== Immutability and state ===================== */

	public function testConfigurationChangesReturnSameInstance(): void
	{
		$original = $this->revolut;

		$result1 = $this->revolut->setAmount(1000);
		$result2 = $this->revolut->setPurchaseReference('TEST');
		$result3 = $this->revolut->setIsTest(true);

		$this->assertSame($original, $result1);
		$this->assertSame($original, $result2);
		$this->assertSame($original, $result3);
	}

	/* ===================== Order ID validation ===================== */

	public function testCaptureValidatesOrderId(): void
	{
		$this->revolut->setAmount(5000);

		// Empty order ID should return null
		$result = $this->revolut->capture('');
		$this->assertNull($result);
	}

	public function testGetOrderValidatesOrderId(): void
	{
		// Empty order ID should return null
		$result = $this->revolut->getOrder('');
		$this->assertNull($result);
	}
}