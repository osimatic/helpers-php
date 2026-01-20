<?php

namespace Tests\Data;

use Osimatic\Data\Table;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
	// ========== Constructor Tests ==========

	public function testConstructorWithAllParameters(): void
	{
		$header = ['Name', 'Email', 'Role'];
		$body = [
			['John Doe', 'john@example.com', 'Admin'],
			['Jane Smith', 'jane@example.com', 'User'],
		];
		$footer = ['Total', '2 users', ''];

		$table = new Table($header, $body, $footer);

		self::assertSame($header, $table->getTableHead());
		self::assertSame($body, $table->getTableBody());
		self::assertSame($footer, $table->getTableFoot());
	}

	public function testConstructorWithNullHeaderAndFooter(): void
	{
		$body = [['John', 'john@example.com']];

		$table = new Table(null, $body, null);

		self::assertSame([], $table->getTableHead());
		self::assertSame($body, $table->getTableBody());
		self::assertSame([], $table->getTableFoot());
	}

	public function testConstructorWithEmptyArrays(): void
	{
		$table = new Table([], [], []);

		self::assertSame([], $table->getTableHead());
		self::assertSame([], $table->getTableBody());
		self::assertSame([], $table->getTableFoot());
	}

	// ========== Header Tests ==========

	public function testGetTableHead(): void
	{
		$header = ['ID', 'Name', 'Email'];
		$table = new Table($header, [], null);

		$result = $table->getTableHead();

		self::assertSame($header, $result);
	}

	public function testSetTableHead(): void
	{
		$table = new Table(null, [], null);
		$newHeader = ['Column1', 'Column2', 'Column3'];

		$result = $table->setTableHead($newHeader);

		self::assertSame($table, $result); // Test fluent interface
		self::assertSame($newHeader, $table->getTableHead());
	}

	public function testSetTableHeadReplacesExistingHeader(): void
	{
		$table = new Table(['Old1', 'Old2'], [], null);
		$newHeader = ['New1', 'New2', 'New3'];

		$table->setTableHead($newHeader);

		self::assertSame($newHeader, $table->getTableHead());
	}

	public function testAddTableHeadCell(): void
	{
		$table = new Table(null, [], null);

		$table->addTableHeadCell('Column1')
			  ->addTableHeadCell('Column2')
			  ->addTableHeadCell('Column3');

		self::assertSame(['Column1', 'Column2', 'Column3'], $table->getTableHead());
	}

	public function testSetTableHeadCell(): void
	{
		$table = new Table(null, [], null);

		$table->setTableHeadCell('A')
			  ->setTableHeadCell('B');

		self::assertSame(['A', 'B'], $table->getTableHead());
	}

	public function testHasTableHeadReturnsTrueWhenHeaderExists(): void
	{
		$table = new Table(['Name'], [], null);

		self::assertTrue($table->hasTableHead());
	}

	public function testHasTableHeadReturnsFalseWhenHeaderIsEmpty(): void
	{
		$table = new Table(null, [], null);

		self::assertFalse($table->hasTableHead());
	}

	// ========== Body Tests ==========

	public function testGetTableBody(): void
	{
		$body = [[1, 'John'], [2, 'Jane']];
		$table = new Table(null, $body, null);

		$result = $table->getTableBody();

		self::assertSame($body, $result);
	}

	public function testSetTableBodyReplacesExistingData(): void
	{
		$table = new Table(null, [[1, 'Old']], null);
		$newBody = [[1, 'New'], [2, 'Data']];

		$result = $table->setTableBody($newBody);

		self::assertSame($table, $result); // Test fluent interface
		self::assertSame($newBody, $table->getTableBody());
	}

	public function testAddTableLine(): void
	{
		$table = new Table(null, [], null);

		$table->addTableLine([1, 'John', 'john@example.com'])
			  ->addTableLine([2, 'Jane', 'jane@example.com']);

		$expected = [
			[1, 'John', 'john@example.com'],
			[2, 'Jane', 'jane@example.com'],
		];

		self::assertSame($expected, $table->getTableBody());
	}

	public function testAddTableLines(): void
	{
		$table = new Table(null, [], null);
		$lines = [
			[1, 'Product A', 10.00],
			[2, 'Product B', 20.00],
			[3, 'Product C', 30.00],
		];

		$result = $table->addTableLines($lines);

		self::assertSame($table, $result); // Test fluent interface
		self::assertSame($lines, $table->getTableBody());
	}

	public function testAddTableLinesAppendsToExistingData(): void
	{
		$table = new Table(null, [[1, 'Existing']], null);
		$newLines = [[2, 'New1'], [3, 'New2']];

		$table->addTableLines($newLines);

		$expected = [
			[1, 'Existing'],
			[2, 'New1'],
			[3, 'New2'],
		];

		self::assertSame($expected, $table->getTableBody());
	}

	// ========== Footer Tests ==========

	public function testGetTableFoot(): void
	{
		$footer = ['Total', '', '100.00'];
		$table = new Table(null, [], $footer);

		$result = $table->getTableFoot();

		self::assertSame($footer, $result);
	}

	public function testSetTableFoot(): void
	{
		$table = new Table(null, [], null);
		$newFooter = ['Summary', 'Total: $100'];

		$result = $table->setTableFoot($newFooter);

		self::assertSame($table, $result); // Test fluent interface
		self::assertSame($newFooter, $table->getTableFoot());
	}

	public function testAddTableFootCell(): void
	{
		$table = new Table(null, [], null);

		$table->addTableFootCell('Total')
			  ->addTableFootCell('')
			  ->addTableFootCell('$125.50');

		self::assertSame(['Total', '', '$125.50'], $table->getTableFoot());
	}

	public function testSetTableFootCell(): void
	{
		$table = new Table(null, [], null);

		$table->setTableFootCell('A')
			  ->setTableFootCell('B');

		self::assertSame(['A', 'B'], $table->getTableFoot());
	}

	public function testHasTableFootReturnsTrueWhenFooterExists(): void
	{
		$table = new Table(null, [], ['Total']);

		self::assertTrue($table->hasTableFoot());
	}

	public function testHasTableFootReturnsFalseWhenFooterIsEmpty(): void
	{
		$table = new Table(null, [], null);

		self::assertFalse($table->hasTableFoot());
	}

	// ========== getTableData() Tests ==========

	public function testGetTableDataWithAllSections(): void
	{
		$header = ['Name', 'Age'];
		$body = [['John', 30], ['Jane', 25]];
		$footer = ['Total', '2 users'];

		$table = new Table($header, $body, $footer);
		$data = $table->getTableData();

		$expected = [
			['Name', 'Age'],
			['John', 30],
			['Jane', 25],
			['Total', '2 users'],
		];

		self::assertSame($expected, $data);
	}

	public function testGetTableDataWithOnlyBody(): void
	{
		$body = [['John', 30], ['Jane', 25]];
		$table = new Table(null, $body, null);

		$data = $table->getTableData();

		self::assertSame($body, $data);
	}

	public function testGetTableDataWithHeaderAndBody(): void
	{
		$header = ['Name', 'Age'];
		$body = [['John', 30]];
		$table = new Table($header, $body, null);

		$data = $table->getTableData();

		$expected = [
			['Name', 'Age'],
			['John', 30],
		];

		self::assertSame($expected, $data);
	}

	public function testGetTableDataConvertsAssociativeArraysToNumeric(): void
	{
		$header = ['col1' => 'Name', 'col2' => 'Age'];
		$body = [['col1' => 'John', 'col2' => 30]];
		$table = new Table($header, $body, null);

		$data = $table->getTableData();

		$expected = [
			['Name', 'Age'],
			['John', 30],
		];

		self::assertSame($expected, $data);
	}

	public function testGetTableDataWithEmptyTable(): void
	{
		$table = new Table(null, [], null);

		$data = $table->getTableData();

		self::assertSame([], $data);
	}

	// ========== Utility Methods Tests ==========

	public function testIsEmptyReturnsTrueForCompletelyEmptyTable(): void
	{
		$table = new Table(null, [], null);

		self::assertTrue($table->isEmpty());
	}

	public function testIsEmptyReturnsFalseWhenHeaderExists(): void
	{
		$table = new Table(['Name'], [], null);

		self::assertFalse($table->isEmpty());
	}

	public function testIsEmptyReturnsFalseWhenBodyExists(): void
	{
		$table = new Table(null, [['John']], null);

		self::assertFalse($table->isEmpty());
	}

	public function testIsEmptyReturnsFalseWhenFooterExists(): void
	{
		$table = new Table(null, [], ['Total']);

		self::assertFalse($table->isEmpty());
	}

	public function testGetRowCountWithAllSections(): void
	{
		$table = new Table(
			['Name'],
			[['John'], ['Jane'], ['Bob']],
			['Total']
		);

		$count = $table->getRowCount();

		self::assertSame(5, $count); // 1 header + 3 body + 1 footer
	}

	public function testGetRowCountWithOnlyBody(): void
	{
		$table = new Table(null, [['A'], ['B'], ['C']], null);

		$count = $table->getRowCount();

		self::assertSame(3, $count);
	}

	public function testGetRowCountWithEmptyTable(): void
	{
		$table = new Table(null, [], null);

		$count = $table->getRowCount();

		self::assertSame(0, $count);
	}

	public function testGetBodyRowCount(): void
	{
		$table = new Table(
			['Name'],
			[['John'], ['Jane'], ['Bob']],
			['Total']
		);

		$count = $table->getBodyRowCount();

		self::assertSame(3, $count);
	}

	public function testGetBodyRowCountWithEmptyBody(): void
	{
		$table = new Table(['Name'], [], ['Total']);

		$count = $table->getBodyRowCount();

		self::assertSame(0, $count);
	}

	public function testGetColumnCount(): void
	{
		$table = new Table(['Name', 'Email', 'Role'], [], null);

		$count = $table->getColumnCount();

		self::assertSame(3, $count);
	}

	public function testGetColumnCountWithNoHeader(): void
	{
		$table = new Table(null, [['A', 'B', 'C']], null);

		$count = $table->getColumnCount();

		self::assertSame(0, $count);
	}

	public function testClear(): void
	{
		$table = new Table(
			['Name'],
			[['John'], ['Jane']],
			['Total']
		);

		$result = $table->clear();

		self::assertSame($table, $result); // Test fluent interface
		self::assertTrue($table->isEmpty());
		self::assertSame([], $table->getTableHead());
		self::assertSame([], $table->getTableBody());
		self::assertSame([], $table->getTableFoot());
	}

	public function testClearBody(): void
	{
		$header = ['Name'];
		$footer = ['Total'];
		$table = new Table($header, [['John'], ['Jane']], $footer);

		$result = $table->clearBody();

		self::assertSame($table, $result); // Test fluent interface
		self::assertSame([], $table->getTableBody());
		self::assertSame($header, $table->getTableHead());
		self::assertSame($footer, $table->getTableFoot());
	}

	// ========== Fluent Interface Tests ==========

	public function testFluentInterfaceChaining(): void
	{
		$table = new Table(null, [], null);

		$result = $table
			->addTableHeadCell('ID')
			->addTableHeadCell('Name')
			->addTableLine([1, 'Product A'])
			->addTableLine([2, 'Product B'])
			->addTableFootCell('Total: 2');

		self::assertSame($table, $result);
		self::assertSame(['ID', 'Name'], $table->getTableHead());
		self::assertCount(2, $table->getTableBody());
		self::assertSame(['Total: 2'], $table->getTableFoot());
	}

	// ========== Edge Cases Tests ==========

	public function testTableWithMixedDataTypes(): void
	{
		$table = new Table(
			['String', 'Integer', 'Float', 'Boolean', 'Null'],
			[['text', 42, 3.14, true, null]],
			null
		);

		$data = $table->getTableData();

		self::assertCount(2, $data); // header + 1 body row
		self::assertSame('text', $data[1][0]);
		self::assertSame(42, $data[1][1]);
		self::assertSame(3.14, $data[1][2]);
		self::assertTrue($data[1][3]);
		self::assertNull($data[1][4]);
	}

	public function testTableWithDifferentRowLengths(): void
	{
		$table = new Table(
			['A', 'B', 'C'],
			[
				[1, 2, 3],
				[4, 5],      // shorter row
				[6, 7, 8, 9], // longer row
			],
			null
		);

		$body = $table->getTableBody();

		self::assertCount(3, $body);
		self::assertCount(3, $body[0]);
		self::assertCount(2, $body[1]);
		self::assertCount(4, $body[2]);
	}
}