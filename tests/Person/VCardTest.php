<?php

declare(strict_types=1);

namespace Tests\Person;

use Osimatic\Person\VCard;
use PHPUnit\Framework\TestCase;

final class VCardTest extends TestCase
{
	/* ===================== Constants ===================== */

	public function testFileExtensionConstant(): void
	{
		$this->assertSame('.vcf', VCard::FILE_EXTENSION);
	}

	public function testLineBreakConstant(): void
	{
		$this->assertSame("\r\n", VCard::LN);
	}

	/* ===================== addName() ===================== */

	public function testAddNameWithFullDetails(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John', 'Michael', 'Mr.', 'Jr.');
		$content = $vcard->getContent();

		$this->assertStringContainsString('N;CHARSET=utf-8:Doe;John;Michael;Mr.;Jr.', $content);
		$this->assertStringContainsString('FN;CHARSET=utf-8:Mr. John Michael Doe Jr.', $content);
	}

	public function testAddNameWithLastNameAndFirstNameOnly(): void
	{
		$vcard = new VCard();
		$vcard->addName('Smith', 'Jane');
		$content = $vcard->getContent();

		$this->assertStringContainsString('N;CHARSET=utf-8:Smith;Jane;;;', $content);
		$this->assertStringContainsString('FN;CHARSET=utf-8:Jane Smith', $content);
	}

	public function testAddNameWithLastNameOnly(): void
	{
		$vcard = new VCard();
		$vcard->addName('Johnson');
		$content = $vcard->getContent();

		$this->assertStringContainsString('N;CHARSET=utf-8:Johnson;;;;', $content);
		$this->assertStringContainsString('FN;CHARSET=utf-8:Johnson', $content);
	}

	public function testAddNameWithEmptyValues(): void
	{
		$vcard = new VCard();
		$vcard->addName('', '', '', '', '');
		$content = $vcard->getContent();

		$this->assertStringContainsString('N;CHARSET=utf-8:;;;;', $content);
	}

	/* ===================== addNickname() ===================== */

	public function testAddNickname(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addNickname('Johnny');
		$content = $vcard->getContent();

		$this->assertStringContainsString('NICKNAME:Johnny', $content);
	}

	/* ===================== addBirthday() ===================== */

	public function testAddBirthday(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$birthday = new \DateTime('1990-05-15');
		$vcard->addBirthday($birthday);
		$content = $vcard->getContent();

		$this->assertStringContainsString('BDAY:1990-05-15', $content);
	}

	/* ===================== addAddress() ===================== */

	public function testAddAddressWithAllFields(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addAddress('Home', 'Apt 5', '123 Main St', 'New York', 'NY', '10001', 'USA', 'HOME');
		$content = $vcard->getContent();

		$this->assertStringContainsString('ADR;TYPE=HOME;CHARSET=utf-8:Home;Apt 5;123 Main St;New York;NY;10001;USA', $content);
	}

	public function testAddAddressWithMinimalFields(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addAddress('', '', '456 Oak Ave', 'Boston', '', '02101', '');
		$content = $vcard->getContent();

		$this->assertStringContainsString('ADR;TYPE=HOME;CHARSET=utf-8:;;456 Oak Ave;Boston;;02101;', $content);
	}

	public function testAddAddressWithWorkType(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addAddress('', '', '789 Business Rd', 'Chicago', 'IL', '60601', 'USA', 'WORK');
		$content = $vcard->getContent();

		$this->assertStringContainsString('ADR;TYPE=WORK;CHARSET=utf-8:;;789 Business Rd;Chicago;IL;60601;USA', $content);
	}

	public function testAddMultipleAddresses(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addAddress('', '', '123 Main St', 'City1', '', '10001', '', 'HOME');
		$vcard->addAddress('', '', '456 Work Ave', 'City2', '', '20002', '', 'WORK');
		$content = $vcard->getContent();

		$this->assertStringContainsString('123 Main St;City1', $content);
		$this->assertStringContainsString('456 Work Ave;City2', $content);
	}

	/* ===================== addLocation() ===================== */

	public function testAddLocation(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addLocation('40.7128,-74.0060');
		$content = $vcard->getContent();

		$this->assertStringContainsString('GEO:40.7128,-74.0060', $content);
	}

	/* ===================== addEmail() ===================== */

	public function testAddEmailWithoutType(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addEmail('john.doe@example.com');
		$content = $vcard->getContent();

		$this->assertStringContainsString('EMAIL:john.doe@example.com', $content);
	}

	public function testAddEmailWithType(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addEmail('work@example.com', 'WORK');
		$content = $vcard->getContent();

		$this->assertStringContainsString('EMAIL;TYPE=WORK:work@example.com', $content);
	}

	public function testAddMultipleEmails(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addEmail('personal@example.com', 'HOME');
		$vcard->addEmail('work@example.com', 'WORK');
		$content = $vcard->getContent();

		$this->assertStringContainsString('personal@example.com', $content);
		$this->assertStringContainsString('work@example.com', $content);
	}

	/* ===================== addPhoneNumber() ===================== */

	public function testAddPhoneNumberWithoutType(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addPhoneNumber('+1-555-123-4567');
		$content = $vcard->getContent();

		$this->assertStringContainsString('TEL:+1-555-123-4567', $content);
	}

	public function testAddPhoneNumberWithType(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addPhoneNumber('+1-555-987-6543', 'CELL');
		$content = $vcard->getContent();

		$this->assertStringContainsString('TEL;TYPE=CELL:+1-555-987-6543', $content);
	}

	public function testAddMultiplePhoneNumbers(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addPhoneNumber('+1-555-111-2222', 'HOME');
		$vcard->addPhoneNumber('+1-555-333-4444', 'WORK');
		$vcard->addPhoneNumber('+1-555-555-6666', 'CELL');
		$content = $vcard->getContent();

		$this->assertStringContainsString('+1-555-111-2222', $content);
		$this->assertStringContainsString('+1-555-333-4444', $content);
		$this->assertStringContainsString('+1-555-555-6666', $content);
	}

	/* ===================== addCompany() ===================== */

	public function testAddCompanyWithNameOnly(): void
	{
		$vcard = new VCard();
		$vcard->addCompany('ACME Corporation');
		$content = $vcard->getContent();

		$this->assertStringContainsString('ORG;CHARSET=utf-8:ACME Corporation;', $content);
		$this->assertStringContainsString('FN;CHARSET=utf-8:ACME Corporation', $content);
	}

	public function testAddCompanyWithUnits(): void
	{
		$vcard = new VCard();
		$vcard->addCompany('Tech Inc', 'Engineering Department');
		$content = $vcard->getContent();

		$this->assertStringContainsString('ORG;CHARSET=utf-8:Tech Inc;Engineering Department', $content);
		$this->assertStringContainsString('FN;CHARSET=utf-8:Tech Inc Engineering Department', $content);
	}

	/* ===================== addRole() ===================== */

	public function testAddRole(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addRole('Software Developer');
		$content = $vcard->getContent();

		$this->assertStringContainsString('ROLE;CHARSET=utf-8:Software Developer', $content);
	}

	/* ===================== addJobTitle() ===================== */

	public function testAddJobTitle(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addJobTitle('Senior Engineer');
		$content = $vcard->getContent();

		$this->assertStringContainsString('TITLE;CHARSET=utf-8:Senior Engineer', $content);
	}

	/* ===================== addURL() ===================== */

	public function testAddURLWithoutType(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addURL('https://example.com');
		$content = $vcard->getContent();

		$this->assertStringContainsString('URL:https://example.com', $content);
	}

	public function testAddURLWithType(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addURL('https://work.example.com', 'WORK');
		$content = $vcard->getContent();

		$this->assertStringContainsString('URL;TYPE=WORK:https://work.example.com', $content);
	}

	public function testAddMultipleURLs(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addURL('https://personal.com', 'HOME');
		$vcard->addURL('https://work.com', 'WORK');
		$content = $vcard->getContent();

		$this->assertStringContainsString('https://personal.com', $content);
		$this->assertStringContainsString('https://work.com', $content);
	}

	/* ===================== addTimeZone() ===================== */

	public function testAddTimeZone(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addTimeZone('America/New_York');
		$content = $vcard->getContent();

		$this->assertStringContainsString('TZ;CHARSET=utf-8:America/New_York', $content);
	}

	/* ===================== addUniqueIdentifier() ===================== */

	public function testAddUniqueIdentifier(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addUniqueIdentifier('550e8400-e29b-41d4-a716-446655440000');
		$content = $vcard->getContent();

		$this->assertStringContainsString('UID;CHARSET=utf-8:550e8400-e29b-41d4-a716-446655440000', $content);
	}

	/* ===================== addSource() ===================== */

	public function testAddSource(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addSource('https://example.com/vcard.vcf');
		$content = $vcard->getContent();

		$this->assertStringContainsString('SOURCE;CHARSET=utf-8:https://example.com/vcard.vcf', $content);
	}

	/* ===================== addPublicKey() ===================== */

	public function testAddPublicKey(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addPublicKey('ssh-rsa AAAAB3NzaC1...', 'SSH');
		$content = $vcard->getContent();

		$this->assertStringContainsString('KEY;TYPE=SSH;CHARSET=utf-8:ssh-rsa AAAAB3NzaC1...', $content);
	}

	/* ===================== addLang() ===================== */

	public function testAddLang(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addLang('en-US');
		$content = $vcard->getContent();

		$this->assertStringContainsString('LANG;CHARSET=utf-8:en-US', $content);
	}

	/* ===================== addNote() ===================== */

	public function testAddNote(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addNote('This is a test note about the contact.');
		$content = $vcard->getContent();

		$this->assertStringContainsString('NOTE;CHARSET=utf-8:This is a test note about the contact.', $content);
	}

	/* ===================== getContent() structure ===================== */

	public function testGetContentHasRequiredStructure(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$content = $vcard->getContent();

		$this->assertStringStartsWith('BEGIN:VCARD', $content);
		$this->assertStringContainsString('VERSION:3.0', $content);
		$this->assertStringContainsString('REV:', $content);
		$this->assertStringEndsWith('END:VCARD', $content);
	}

	public function testGetContentHasCorrectLineBreaks(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$content = $vcard->getContent();

		$lines = explode("\r\n", $content);
		$this->assertGreaterThan(3, count($lines));
		$this->assertSame('BEGIN:VCARD', $lines[0]);
		$this->assertSame('END:VCARD', $lines[count($lines) - 1]);
	}

	/* ===================== Charset ===================== */

	public function testSetCharset(): void
	{
		$vcard = new VCard();
		$vcard->setCharset('iso-8859-1');

		$this->assertSame('iso-8859-1', $vcard->getCharset());
	}

	public function testDefaultCharsetIsUtf8(): void
	{
		$vcard = new VCard();

		$this->assertSame('utf-8', $vcard->getCharset());
	}

	public function testCharsetInContent(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$content = $vcard->getContent();

		$this->assertStringContainsString('CHARSET=utf-8', $content);
	}

	/* ===================== File extension ===================== */

	public function testGetFileExtension(): void
	{
		$vcard = new VCard();

		$this->assertSame('.vcf', $vcard->getFileExtension());
	}

	/* ===================== Complex scenarios ===================== */

	public function testCompleteVCard(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John', 'Michael', 'Dr.', 'PhD');
		$vcard->addNickname('Johnny');
		$vcard->addBirthday(new \DateTime('1985-03-20'));
		$vcard->addAddress('', '', '123 Main St', 'New York', 'NY', '10001', 'USA', 'HOME');
		$vcard->addEmail('john.doe@example.com', 'WORK');
		$vcard->addPhoneNumber('+1-555-123-4567', 'CELL');
		$vcard->addCompany('ACME Corp', 'Engineering');
		$vcard->addJobTitle('Senior Developer');
		$vcard->addURL('https://johndoe.com');
		$vcard->addNote('Important contact');

		$content = $vcard->getContent();

		$this->assertStringContainsString('N;CHARSET=utf-8:Doe;John;Michael;Dr.;PhD', $content);
		$this->assertStringContainsString('NICKNAME:Johnny', $content);
		$this->assertStringContainsString('BDAY:1985-03-20', $content);
		$this->assertStringContainsString('123 Main St', $content);
		$this->assertStringContainsString('john.doe@example.com', $content);
		$this->assertStringContainsString('+1-555-123-4567', $content);
		$this->assertStringContainsString('ACME Corp', $content);
		$this->assertStringContainsString('Senior Developer', $content);
		$this->assertStringContainsString('https://johndoe.com', $content);
		$this->assertStringContainsString('Important contact', $content);
	}

	public function testVCardWithMinimalInfo(): void
	{
		$vcard = new VCard();
		$vcard->addName('Smith');
		$content = $vcard->getContent();

		$this->assertStringContainsString('BEGIN:VCARD', $content);
		$this->assertStringContainsString('N;CHARSET=utf-8:Smith', $content);
		$this->assertStringContainsString('END:VCARD', $content);
	}

	/* ===================== Special characters ===================== */

	public function testNameWithSpecialCharacters(): void
	{
		$vcard = new VCard();
		$vcard->addName('O\'Brien', 'Seán');
		$content = $vcard->getContent();

		$this->assertStringContainsString('O\'Brien', $content);
		$this->assertStringContainsString('Seán', $content);
	}

	public function testEmailWithPlusSign(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addEmail('john.doe+test@example.com');
		$content = $vcard->getContent();

		$this->assertStringContainsString('john.doe+test@example.com', $content);
	}

	public function testNoteWithMultipleLines(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addNote("Line 1\nLine 2\nLine 3");
		$content = $vcard->getContent();

		$this->assertStringContainsString('NOTE;CHARSET=utf-8:Line 1', $content);
	}

	/* ===================== setFilename() ===================== */

	public function testSetFilenameDoesNotCrash(): void
	{
		$vcard = new VCard();
		$vcard->setFilename('test_filename');

		// If we reach here without exception, the test passes
		$this->assertTrue(true);
	}

	public function testSetFilenameWithArray(): void
	{
		$vcard = new VCard();
		$vcard->setFilename(['John', 'Doe']);

		// If we reach here without exception, the test passes
		$this->assertTrue(true);
	}

	/* ===================== Duplicate prevention ===================== */

	public function testCannotAddNicknameMultipleTimes(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addNickname('Johnny');
		$vcard->addNickname('JD'); // Should be ignored
		$content = $vcard->getContent();

		$nicknameCount = substr_count($content, 'NICKNAME:');
		$this->assertSame(1, $nicknameCount);
	}

	public function testCannotAddRoleMultipleTimes(): void
	{
		$vcard = new VCard();
		$vcard->addName('Doe', 'John');
		$vcard->addRole('Developer');
		$vcard->addRole('Manager'); // Should be ignored
		$content = $vcard->getContent();

		$roleCount = substr_count($content, 'ROLE;CHARSET=utf-8:');
		$this->assertSame(1, $roleCount);
	}
}