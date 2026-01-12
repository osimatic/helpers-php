<?php

declare(strict_types=1);

namespace Tests\Network;

use Osimatic\Network\IniResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class IniResponseTest extends TestCase
{
	/* ===================== get() - Basic functionality ===================== */

	public function testGetWithSuccessResult(): void
	{
		$response = IniResponse::get('success');

		$this->assertInstanceOf(Response::class, $response);
		$expected = "[data]\r\nresult=success\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetWithErrorResult(): void
	{
		$response = IniResponse::get('error');

		$this->assertInstanceOf(Response::class, $response);
		$expected = "[data]\r\nresult=error\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetWithEmptyResult(): void
	{
		$response = IniResponse::get('');

		$this->assertInstanceOf(Response::class, $response);
		$expected = "[data]\r\nresult=\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	/* ===================== get() - With additional data ===================== */

	public function testGetWithSingleDataField(): void
	{
		$response = IniResponse::get('success', ['message' => 'Operation completed']);

		$expected = "[data]\r\nresult=success\r\nmessage=Operation completed\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetWithMultipleDataFields(): void
	{
		$data = [
			'message' => 'Operation completed',
			'code' => '200',
			'timestamp' => '2024-01-15'
		];
		$response = IniResponse::get('success', $data);

		$expected = "[data]\r\nresult=success\r\nmessage=Operation completed\r\ncode=200\r\ntimestamp=2024-01-15\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetWithNumericKeys(): void
	{
		$data = [
			0 => 'first',
			1 => 'second',
			2 => 'third'
		];
		$response = IniResponse::get('success', $data);

		$expected = "[data]\r\nresult=success\r\n0=first\r\n1=second\r\n2=third\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetWithSpecialCharactersInValues(): void
	{
		$data = [
			'email' => 'user@example.com',
			'url' => 'https://example.com/path?query=value',
			'description' => 'A description with spaces and punctuation!'
		];
		$response = IniResponse::get('success', $data);

		$expected = "[data]\r\nresult=success\r\nemail=user@example.com\r\nurl=https://example.com/path?query=value\r\ndescription=A description with spaces and punctuation!\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetWithEmptyData(): void
	{
		$response = IniResponse::get('success', []);

		$expected = "[data]\r\nresult=success\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetWithEmptyStringValues(): void
	{
		$data = [
			'field1' => '',
			'field2' => 'value',
			'field3' => ''
		];
		$response = IniResponse::get('success', $data);

		$expected = "[data]\r\nresult=success\r\nfield1=\r\nfield2=value\r\nfield3=\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetWithNumericValues(): void
	{
		$data = [
			'count' => '42',
			'price' => '19.99',
			'quantity' => '0'
		];
		$response = IniResponse::get('success', $data);

		$expected = "[data]\r\nresult=success\r\ncount=42\r\nprice=19.99\r\nquantity=0\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	/* ===================== getList() - Basic functionality ===================== */

	public function testGetListWithEmptyData(): void
	{
		$response = IniResponse::getList([]);

		$this->assertInstanceOf(Response::class, $response);
		$expected = "[data]\r\ncount=0\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetListWithSingleSection(): void
	{
		$data = [
			'user1' => [
				'name' => 'John Doe',
				'email' => 'john@example.com'
			]
		];
		$response = IniResponse::getList($data);

		$expected = "[data]\r\ncount=1\r\n[user1]\r\nname=John Doe\r\nemail=john@example.com\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetListWithMultipleSections(): void
	{
		$data = [
			'user1' => [
				'name' => 'John Doe',
				'age' => '30'
			],
			'user2' => [
				'name' => 'Jane Smith',
				'age' => '25'
			],
			'user3' => [
				'name' => 'Bob Johnson',
				'age' => '35'
			]
		];
		$response = IniResponse::getList($data);

		$expected = "[data]\r\ncount=3\r\n[user1]\r\nname=John Doe\r\nage=30\r\n[user2]\r\nname=Jane Smith\r\nage=25\r\n[user3]\r\nname=Bob Johnson\r\nage=35\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetListWithCustomCountFieldName(): void
	{
		$data = [
			'item1' => [
				'title' => 'First Item'
			]
		];
		$response = IniResponse::getList($data, 'total');

		$expected = "[data]\r\ntotal=1\r\n[item1]\r\ntitle=First Item\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetListWithEmptySections(): void
	{
		$data = [
			'section1' => [],
			'section2' => [
				'key' => 'value'
			],
			'section3' => []
		];
		$response = IniResponse::getList($data);

		$expected = "[data]\r\ncount=3\r\n[section1]\r\n[section2]\r\nkey=value\r\n[section3]\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetListWithNumericSectionNames(): void
	{
		$data = [
			0 => [
				'value' => 'first'
			],
			1 => [
				'value' => 'second'
			]
		];
		$response = IniResponse::getList($data);

		$expected = "[data]\r\ncount=2\r\n[0]\r\nvalue=first\r\n[1]\r\nvalue=second\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	/* ===================== getList() - Complex scenarios ===================== */

	public function testGetListWithSpecialCharactersInSectionNames(): void
	{
		$data = [
			'section-1' => [
				'key' => 'value1'
			],
			'section_2' => [
				'key' => 'value2'
			],
			'section.3' => [
				'key' => 'value3'
			]
		];
		$response = IniResponse::getList($data);

		$expected = "[data]\r\ncount=3\r\n[section-1]\r\nkey=value1\r\n[section_2]\r\nkey=value2\r\n[section.3]\r\nkey=value3\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetListWithMixedDataTypes(): void
	{
		$data = [
			'product1' => [
				'name' => 'Product A',
				'price' => '19.99',
				'stock' => '100',
				'available' => 'true'
			]
		];
		$response = IniResponse::getList($data);

		$expected = "[data]\r\ncount=1\r\n[product1]\r\nname=Product A\r\nprice=19.99\r\nstock=100\r\navailable=true\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetListWithEmptyFieldName(): void
	{
		$data = [
			'section1' => [
				'field1' => 'value1'
			]
		];
		$response = IniResponse::getList($data, '');

		$expected = "[data]\r\n=1\r\n[section1]\r\nfield1=value1\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	/* ===================== Response object validation ===================== */

	public function testGetReturnsResponseWithCorrectType(): void
	{
		$response = IniResponse::get('success');

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testGetListReturnsResponseWithCorrectType(): void
	{
		$response = IniResponse::getList([]);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(200, $response->getStatusCode());
	}

	/* ===================== Real-world examples ===================== */

	public function testGetWithApiSuccessResponse(): void
	{
		$data = [
			'id' => '12345',
			'status' => 'completed',
			'timestamp' => '2024-01-15T10:30:00Z'
		];
		$response = IniResponse::get('success', $data);

		$expected = "[data]\r\nresult=success\r\nid=12345\r\nstatus=completed\r\ntimestamp=2024-01-15T10:30:00Z\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetListWithUsersList(): void
	{
		$data = [
			'user_1' => [
				'username' => 'john_doe',
				'email' => 'john@example.com',
				'role' => 'admin'
			],
			'user_2' => [
				'username' => 'jane_smith',
				'email' => 'jane@example.com',
				'role' => 'user'
			]
		];
		$response = IniResponse::getList($data, 'total_users');

		$expected = "[data]\r\ntotal_users=2\r\n[user_1]\r\nusername=john_doe\r\nemail=john@example.com\r\nrole=admin\r\n[user_2]\r\nusername=jane_smith\r\nemail=jane@example.com\r\nrole=user\r\n";
		$this->assertSame($expected, $response->getContent());
	}

	public function testGetListWithConfigurationSections(): void
	{
		$data = [
			'database' => [
				'host' => 'localhost',
				'port' => '3306',
				'name' => 'mydb'
			],
			'cache' => [
				'enabled' => 'true',
				'ttl' => '3600'
			]
		];
		$response = IniResponse::getList($data, 'sections');

		$expected = "[data]\r\nsections=2\r\n[database]\r\nhost=localhost\r\nport=3306\r\nname=mydb\r\n[cache]\r\nenabled=true\r\nttl=3600\r\n";
		$this->assertSame($expected, $response->getContent());
	}
}