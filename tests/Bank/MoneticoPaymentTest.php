<?php

declare(strict_types=1);

namespace Tests\Bank;

use Osimatic\Bank\MoneticoPayment;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class MoneticoPaymentTest extends TestCase
{
	private const int TEST_TPE_NUMBER = 1234567;
	private const string TEST_COMPANY_CODE = 'testcompany';
	private const string TEST_KEY = 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'; // 40 chars

	private MoneticoPayment $cicPayment;

	protected function setUp(): void
	{
		$this->cicPayment = new MoneticoPayment(
			tpeNumber: self::TEST_TPE_NUMBER,
			companyCode: self::TEST_COMPANY_CODE,
			key: self::TEST_KEY
		);
	}

	/* ===================== Constants ===================== */

	public function testVersionConstant(): void
	{
		$this->assertSame('3.0', MoneticoPayment::CMCIC_VERSION);
	}

	public function testProductionUrlConstant(): void
	{
		$this->assertSame('https://ssl.paiement.cic-banques.fr/paiement.cgi', MoneticoPayment::CMCIC_URL_PAIEMENT);
	}

	public function testTestUrlConstant(): void
	{
		$this->assertSame('https://ssl.paiement.cic-banques.fr/test/paiement.cgi', MoneticoPayment::CMCIC_URL_PAIEMENT_TEST);
	}

	/* ===================== Constructor ===================== */

	public function testConstructorCreatesInstance(): void
	{
		$cicPayment = new MoneticoPayment(
			tpeNumber: self::TEST_TPE_NUMBER,
			companyCode: self::TEST_COMPANY_CODE,
			key: self::TEST_KEY
		);

		$this->assertInstanceOf(MoneticoPayment::class, $cicPayment);
	}

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);

		$cicPayment = new MoneticoPayment(
			tpeNumber: self::TEST_TPE_NUMBER,
			companyCode: self::TEST_COMPANY_CODE,
			key: self::TEST_KEY,
			logger: $logger
		);

		$this->assertInstanceOf(MoneticoPayment::class, $cicPayment);
	}

	/* ===================== Setters - Basic configuration ===================== */

	public function testSetTpeNumber(): void
	{
		$result = $this->cicPayment->setTpeNumber(1234567);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetTpeNumberWithLargeValue(): void
	{
		$result = $this->cicPayment->setTpeNumber(9999999);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetCompanyCode(): void
	{
		$result = $this->cicPayment->setCompanyCode('mycompany');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetCompanyCodeWithLongString(): void
	{
		$longCode = str_repeat('A', 100);
		$result = $this->cicPayment->setCompanyCode($longCode);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetKey(): void
	{
		$result = $this->cicPayment->setKey('0123456789ABCDEF0123456789ABCDEF01234567');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetKeyWith40Characters(): void
	{
		// CIC keys are typically 40 characters hexadecimal
		$key = str_repeat('A', 40);
		$result = $this->cicPayment->setKey($key);

		$this->assertSame($this->cicPayment, $result);
	}

	/* ===================== Setters - Transaction parameters ===================== */

	public function testSetAllTaxesInclAmount(): void
	{
		$result = $this->cicPayment->setAllTaxesInclAmount(99.99);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetAllTaxesInclAmountWithZero(): void
	{
		$result = $this->cicPayment->setAllTaxesInclAmount(0.0);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetAllTaxesInclAmountWithLargeValue(): void
	{
		$result = $this->cicPayment->setAllTaxesInclAmount(999999.99);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetAllTaxesInclAmountWithDecimals(): void
	{
		$result = $this->cicPayment->setAllTaxesInclAmount(12.34);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetCurrency(): void
	{
		$result = $this->cicPayment->setCurrency('EUR');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetCurrencyUSD(): void
	{
		$result = $this->cicPayment->setCurrency('USD');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetCurrencyGBP(): void
	{
		$result = $this->cicPayment->setCurrency('GBP');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReference(): void
	{
		$result = $this->cicPayment->setReference('ORDER-12345');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReferenceWithNull(): void
	{
		$result = $this->cicPayment->setReference(null);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReferenceMaxLength(): void
	{
		// Max 12 alphanumeric characters
		$result = $this->cicPayment->setReference('ABCDEF123456');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetTexteLibre(): void
	{
		$result = $this->cicPayment->setTexteLibre('Additional information');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetTexteLibreWithNull(): void
	{
		$result = $this->cicPayment->setTexteLibre(null);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetTexteLibreWithLongString(): void
	{
		// Max 3200 characters
		$longText = str_repeat('A', 3200);
		$result = $this->cicPayment->setTexteLibre($longText);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetLanguage(): void
	{
		$result = $this->cicPayment->setLanguage('FR');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetLanguageWithNull(): void
	{
		$result = $this->cicPayment->setLanguage(null);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetLanguageEN(): void
	{
		$result = $this->cicPayment->setLanguage('EN');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetLanguageDE(): void
	{
		$result = $this->cicPayment->setLanguage('DE');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetLanguageIT(): void
	{
		$result = $this->cicPayment->setLanguage('IT');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetLanguageES(): void
	{
		$result = $this->cicPayment->setLanguage('ES');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetCustomerEmail(): void
	{
		$result = $this->cicPayment->setCustomerEmail('customer@example.com');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetCustomerEmailWithNull(): void
	{
		$result = $this->cicPayment->setCustomerEmail(null);

		$this->assertSame($this->cicPayment, $result);
	}

	/* ===================== Setters - Form customization ===================== */

	public function testSetButtonTagText(): void
	{
		$result = $this->cicPayment->setButtonTagText('Pay Now');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetButtonTagTextWithNull(): void
	{
		$result = $this->cicPayment->setButtonTagText(null);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetFormTagClass(): void
	{
		$result = $this->cicPayment->setFormTagClass('payment-form');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetFormTagClassWithNull(): void
	{
		$result = $this->cicPayment->setFormTagClass(null);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetButtonTagClass(): void
	{
		$result = $this->cicPayment->setButtonTagClass('btn-primary');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetButtonTagClassWithNull(): void
	{
		$result = $this->cicPayment->setButtonTagClass(null);

		$this->assertSame($this->cicPayment, $result);
	}

	/* ===================== Setters - Mode and URLs ===================== */

	public function testSetPaiementTest(): void
	{
		$result = $this->cicPayment->setPaiementTest(true);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetPaiementTestToFalse(): void
	{
		$result = $this->cicPayment->setPaiementTest(false);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReturnUrlHome(): void
	{
		$result = $this->cicPayment->setReturnUrlHome('https://example.com/');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReturnUrlHomeWithNull(): void
	{
		$result = $this->cicPayment->setReturnUrlHome(null);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReturnUrlOk(): void
	{
		$result = $this->cicPayment->setReturnUrlOk('https://example.com/success');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReturnUrlOkWithNull(): void
	{
		$result = $this->cicPayment->setReturnUrlOk(null);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReturnUrlNotOk(): void
	{
		$result = $this->cicPayment->setReturnUrlNotOk('https://example.com/error');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReturnUrlNotOkWithNull(): void
	{
		$result = $this->cicPayment->setReturnUrlNotOk(null);

		$this->assertSame($this->cicPayment, $result);
	}

	/* ===================== Fluent interface ===================== */

	public function testFluentInterfaceChaining(): void
	{
		$result = $this->cicPayment
			->setTpeNumber(1234567)
			->setCompanyCode('testcompany')
			->setKey(str_repeat('A', 40))
			->setAllTaxesInclAmount(99.99)
			->setCurrency('EUR')
			->setReference('ORDER-001')
			->setLanguage('FR')
			->setCustomerEmail('test@example.com');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testFluentInterfaceWithFormConfiguration(): void
	{
		$result = $this->cicPayment
			->setFormTagClass('payment-form')
			->setButtonTagClass('btn-pay')
			->setButtonTagText('Payer maintenant')
			->setReturnUrlHome('https://example.com/')
			->setReturnUrlOk('https://example.com/success')
			->setReturnUrlNotOk('https://example.com/error');

		$this->assertSame($this->cicPayment, $result);
	}

	/* ===================== Form generation tests ===================== */

	public function testGetFormThrowsExceptionWithoutAmount(): void
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Amount is required for CIC payment');

		$this->cicPayment
			->setTpeNumber(1234567)
			->setCompanyCode('test')
			->setKey(str_repeat('A', 40))
			->setCurrency('EUR')
			->getForm();
	}

	public function testGetFormThrowsExceptionWithoutCurrency(): void
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Currency is required for CIC payment');

		$this->cicPayment
			->setTpeNumber(1234567)
			->setCompanyCode('test')
			->setKey(str_repeat('A', 40))
			->setAllTaxesInclAmount(100.0)
			->getForm();
	}

	public function testGetFormReturnsHtmlString(): void
	{
		$html = $this->cicPayment
			->setTpeNumber(1234567)
			->setCompanyCode('testcompany')
			->setKey(str_repeat('A', 40))
			->setAllTaxesInclAmount(99.99)
			->setCurrency('EUR')
			->setReference('TEST-001')
			->setPaiementTest(true)
			->getForm();

		$this->assertIsString($html);
		$this->assertStringContainsString('<form', $html);
		$this->assertStringContainsString('</form>', $html);
		$this->assertStringContainsString('name="version"', $html);
		$this->assertStringContainsString('name="TPE"', $html);
		$this->assertStringContainsString('name="societe"', $html);
		$this->assertStringContainsString('name="montant"', $html);
		$this->assertStringContainsString('name="reference"', $html);
		$this->assertStringContainsString('name="MAC"', $html);
	}

	public function testGetFormWithCustomClasses(): void
	{
		$html = $this->cicPayment
			->setTpeNumber(1234567)
			->setCompanyCode('testcompany')
			->setKey(str_repeat('A', 40))
			->setAllTaxesInclAmount(49.99)
			->setCurrency('EUR')
			->setReference('CSS-TEST')
			->setFormTagClass('custom-form')
			->setButtonTagClass('custom-button')
			->setButtonTagText('Payer')
			->getForm();

		$this->assertStringContainsString('class="custom-form"', $html);
		$this->assertStringContainsString('class="btn btn-default custom-button"', $html);
		$this->assertStringContainsString('>Payer</button>', $html);
	}

	public function testGetFormUsesTestUrl(): void
	{
		$html = $this->cicPayment
			->setTpeNumber(1234567)
			->setCompanyCode('testcompany')
			->setKey(str_repeat('A', 40))
			->setAllTaxesInclAmount(99.99)
			->setCurrency('EUR')
			->setReference('TEST-URL')
			->setPaiementTest(true)
			->getForm();

		$this->assertStringContainsString(MoneticoPayment::CMCIC_URL_PAIEMENT_TEST, $html);
	}

	public function testGetFormUsesProductionUrl(): void
	{
		$html = $this->cicPayment
			->setTpeNumber(1234567)
			->setCompanyCode('testcompany')
			->setKey(str_repeat('A', 40))
			->setAllTaxesInclAmount(99.99)
			->setCurrency('EUR')
			->setReference('PROD-URL')
			->setPaiementTest(false)
			->getForm();

		$this->assertStringContainsString(MoneticoPayment::CMCIC_URL_PAIEMENT, $html);
	}

	/* ===================== URL generation tests ===================== */

	public function testGetUrlReturnsUrlString(): void
	{
		$url = $this->cicPayment
			->setTpeNumber(1234567)
			->setCompanyCode('testcompany')
			->setKey(str_repeat('A', 40))
			->setAllTaxesInclAmount(99.99)
			->setCurrency('EUR')
			->setReference('URL-TEST')
			->getUrl();

		$this->assertIsString($url);
		$this->assertStringStartsWith('https://', $url);
		$this->assertStringContainsString('version=', $url);
		$this->assertStringContainsString('TPE=', $url);
		$this->assertStringContainsString('montant=', $url);
		$this->assertStringContainsString('MAC=', $url);
	}

	/* ===================== Control string tests ===================== */

	public function testGetControlStringForSupportReturnsString(): void
	{
		$controlString = $this->cicPayment
			->setTpeNumber(1234567)
			->setKey(str_repeat('A', 40))
			->getControlStringForSupport();

		$this->assertIsString($controlString);
		$this->assertStringContainsString('V1.04.sha1.php--[CtlHmac', $controlString);
		$this->assertStringContainsString(MoneticoPayment::CMCIC_VERSION, $controlString);
		$this->assertStringContainsString('1234567', $controlString);
	}

	/* ===================== Configuration scenarios ===================== */

	public function testCompleteTestModeConfiguration(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$cicPayment = new MoneticoPayment(
			tpeNumber: 9999999,
			companyCode: 'initial',
			key: str_repeat('Z', 40),
			logger: $logger
		);

		$result = $cicPayment
			->setTpeNumber(1234567)
			->setCompanyCode('testcompany')
			->setKey(str_repeat('A', 40))
			->setPaiementTest(true)
			->setAllTaxesInclAmount(199.99)
			->setCurrency('EUR')
			->setReference('TEST-ORDER')
			->setLanguage('FR')
			->setCustomerEmail('test@example.com')
			->setTexteLibre('Order details')
			->setFormTagClass('form-payment')
			->setButtonTagClass('btn-submit')
			->setButtonTagText('Payer')
			->setReturnUrlHome('https://example.com/')
			->setReturnUrlOk('https://example.com/success')
			->setReturnUrlNotOk('https://example.com/error');

		$this->assertInstanceOf(MoneticoPayment::class, $result);
	}

	public function testCompleteProductionConfiguration(): void
	{
		$result = $this->cicPayment
			->setTpeNumber(9876543)
			->setCompanyCode('prodcompany')
			->setKey(str_repeat('B', 40))
			->setPaiementTest(false)
			->setAllTaxesInclAmount(499.99)
			->setCurrency('USD')
			->setReference('PROD-12345')
			->setLanguage('EN')
			->setCustomerEmail('customer@example.com');

		$this->assertInstanceOf(MoneticoPayment::class, $result);
	}

	/* ===================== Edge cases ===================== */

	public function testSetAmountOneEuro(): void
	{
		$result = $this->cicPayment->setAllTaxesInclAmount(1.00);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetAmountWithManyDecimals(): void
	{
		$result = $this->cicPayment->setAllTaxesInclAmount(123.456789);

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReferenceWithNumbers(): void
	{
		$result = $this->cicPayment->setReference('123456789012');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReferenceWithMixedCase(): void
	{
		$result = $this->cicPayment->setReference('AbC123XyZ');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetTexteLibreWithSpecialCharacters(): void
	{
		$result = $this->cicPayment->setTexteLibre('Order: €100.00 - Client: John Doe');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetCustomerEmailWithPlus(): void
	{
		$result = $this->cicPayment->setCustomerEmail('user+tag@example.com');

		$this->assertSame($this->cicPayment, $result);
	}

	public function testSetReturnUrlWithQueryParameters(): void
	{
		$result = $this->cicPayment->setReturnUrlOk('https://example.com/success?session=abc123');

		$this->assertSame($this->cicPayment, $result);
	}

	/* ===================== Multiple configurations ===================== */

	public function testSwitchBetweenTestAndProduction(): void
	{
		// Start in test mode
		$this->cicPayment->setPaiementTest(true);
		$this->assertInstanceOf(MoneticoPayment::class, $this->cicPayment);

		// Switch to production
		$this->cicPayment->setPaiementTest(false);
		$this->assertInstanceOf(MoneticoPayment::class, $this->cicPayment);
	}

	public function testMultipleLanguageChanges(): void
	{
		$this->cicPayment->setLanguage('FR');
		$this->assertInstanceOf(MoneticoPayment::class, $this->cicPayment);

		$this->cicPayment->setLanguage('EN');
		$this->assertInstanceOf(MoneticoPayment::class, $this->cicPayment);

		$this->cicPayment->setLanguage('DE');
		$this->assertInstanceOf(MoneticoPayment::class, $this->cicPayment);
	}

	/* ===================== Immutability tests ===================== */

	public function testConfigurationChangesReturnSameInstance(): void
	{
		$original = $this->cicPayment;

		$result1 = $this->cicPayment->setTpeNumber(123456);
		$result2 = $this->cicPayment->setAllTaxesInclAmount(100.0);
		$result3 = $this->cicPayment->setCurrency('EUR');

		$this->assertSame($original, $result1);
		$this->assertSame($original, $result2);
		$this->assertSame($original, $result3);
	}
}