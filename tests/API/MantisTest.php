<?php

declare(strict_types=1);

namespace Tests\API;

use Osimatic\API\Mantis;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class MantisTest extends TestCase
{
	/* ===================== Constructor ===================== */

	public function testConstructorWithAllParameters(): void
	{
		$mantis = new Mantis('https://mantis.example.com/', 'user123', 'username', 'password123');

		$this->assertInstanceOf(Mantis::class, $mantis);
	}

	public function testConstructorWithoutParameters(): void
	{
		$mantis = new Mantis();

		$this->assertInstanceOf(Mantis::class, $mantis);
	}

	public function testConstructorWithPartialParameters(): void
	{
		$mantis = new Mantis('https://mantis.example.com/');

		$this->assertInstanceOf(Mantis::class, $mantis);
	}

	public function testConstructorWithUrlAndUserId(): void
	{
		$mantis = new Mantis('https://mantis.example.com/', 'user123');

		$this->assertInstanceOf(Mantis::class, $mantis);
	}

	/* ===================== Setters ===================== */

	public function testSetUrl(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUrl('https://mantis.example.com/');

		$this->assertSame($mantis, $result);
	}

	public function testSetUserId(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUserId('user123');

		$this->assertSame($mantis, $result);
	}

	public function testSetUserName(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUserName('username');

		$this->assertSame($mantis, $result);
	}

	public function testSetUserPassword(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUserPassword('password123');

		$this->assertSame($mantis, $result);
	}

	public function testFluentInterface(): void
	{
		$mantis = new Mantis();

		$result = $mantis
			->setUrl('https://mantis.example.com/')
			->setUserId('user123')
			->setUserName('username')
			->setUserPassword('password123');

		$this->assertSame($mantis, $result);
	}

	/* ===================== addIssue() - Validation ===================== */

	public function testAddIssueWithoutUrl(): void
	{
		$mantis = new Mantis(null, 'user123', 'username', 'password123');

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueWithoutUserId(): void
	{
		$mantis = new Mantis('https://mantis.example.com/', null, 'username', 'password123');

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueWithoutUserName(): void
	{
		$mantis = new Mantis('https://mantis.example.com/', 'user123', null, 'password123');

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueWithoutUserPassword(): void
	{
		$mantis = new Mantis('https://mantis.example.com/', 'user123', 'username');

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueWithEmptyUrl(): void
	{
		$mantis = new Mantis('', 'user123', 'username', 'password123');

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueWithEmptyUserId(): void
	{
		$mantis = new Mantis('https://mantis.example.com/', '', 'username', 'password123');

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueWithEmptyUserName(): void
	{
		$mantis = new Mantis('https://mantis.example.com/', 'user123', '', 'password123');

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueWithEmptyUserPassword(): void
	{
		$mantis = new Mantis('https://mantis.example.com/', 'user123', 'username', '');

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	/* ===================== addIssue() - Parameters ===================== */

	/**
	 * Note: Ce test nécessiterait de mocker SoapClient, ce qui n'est pas possible
	 * sans refactorer la classe Mantis pour injecter SoapClient.
	 * Pour l'instant, nous testons uniquement les validations.
	 */
	#[Group("integration")]
	public function testAddIssueWithMinimalParameters(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithAllParameters(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithProjectName(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithPriority(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithReproducibility(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithCustomFields(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithEmptyCustomFields(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithCustomCategory(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	/* ===================== addIssue() - Severity Levels ===================== */

	#[Group("integration")]
	public function testAddIssueWithDifferentSeverityLevels(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	/* ===================== Method Chaining ===================== */

	#[Group("integration")]
	public function testCompleteWorkflowWithMethodChaining(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testSettersAfterConstruction(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	/* ===================== Edge Cases ===================== */

	#[Group("integration")]
	public function testAddIssueWithZeroProjectId(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithEmptyTitle(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithEmptyDescription(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithLongTitle(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithLongDescription(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithNegativeProjectId(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithSpecialCharactersInTitle(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	#[Group("integration")]
	public function testAddIssueWithSpecialCharactersInDescription(): void
	{
		$this->markTestSkipped('Requires SoapClient mocking or integration testing');
	}

	/* ===================== URL Variations ===================== */

	public function testSetUrlWithTrailingSlash(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUrl('https://mantis.example.com/');

		$this->assertSame($mantis, $result);
	}

	public function testSetUrlWithoutTrailingSlash(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUrl('https://mantis.example.com');

		$this->assertSame($mantis, $result);
	}

	public function testSetUrlWithSubdirectory(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUrl('https://example.com/mantis/');

		$this->assertSame($mantis, $result);
	}

	public function testSetUrlWithPort(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUrl('https://mantis.example.com:8080/');

		$this->assertSame($mantis, $result);
	}
}