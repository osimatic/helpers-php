<?php

namespace Osimatic\Data;

/**
 * Represents a structured data table with header, body, and footer sections.
 *
 * This class provides a flexible way to build and manipulate tabular data structures,
 * commonly used for generating HTML tables, CSV exports, or console table outputs.
 * It supports method chaining for fluent interface and includes utility methods for
 * table inspection and manipulation.
 *
 * Key features:
 * - Three distinct sections: header, body, and footer
 * - Fluent interface with method chaining
 * - Type-safe operations with proper validation
 * - Utility methods for table inspection
 * - Easy data export to various formats
 *
 * Usage examples:
 * ```php
 * // Simple table creation
 * $table = new Table(
 *     ['Name', 'Email', 'Role'],
 *     [
 *         ['John Doe', 'john@example.com', 'Admin'],
 *         ['Jane Smith', 'jane@example.com', 'User'],
 *     ],
 *     ['Total', '2 users', '']
 * );
 *
 * // Using fluent interface
 * $table = new Table(null, [], null);
 * $table->addTableHeadCell('ID')
 *       ->addTableHeadCell('Name')
 *       ->addTableLine([1, 'Product A'])
 *       ->addTableLine([2, 'Product B'])
 *       ->addTableFootCell('Total: 2');
 *
 * // Get complete table data
 * $data = $table->getTableData(); // Returns 2D array with all sections
 * ```
 *
 * @see Table::getTableData() Get complete table as 2D array
 * @see Table::addTableLine() Add a single row to table body
 * @see Table::addTableLines() Add multiple rows at once
 * @see Table::getRowCount() Get total number of rows
 * @see Table::isEmpty() Check if table has no data
 */
class Table
{
	/** @var array The table header row (column names/titles) */
	private array $tableHead = [];

	/** @var array The table body rows (data rows) */
	private array $tableBody = [];

	/** @var array The table footer row (summary/totals) */
	private array $tableFoot = [];

	/**
	 * Creates a new Table instance with optional header, body, and footer data.
	 *
	 * Example:
	 * ```php
	 * // Complete table
	 * $table = new Table(
	 *     ['ID', 'Name', 'Price'],
	 *     [[1, 'Item A', 10.50], [2, 'Item B', 15.00]],
	 *     ['Total', '', 25.50]
	 * );
	 *
	 * // Empty table (to be populated later)
	 * $table = new Table(null, [], null);
	 * ```
	 *
	 * @param array|null $tableHead Optional array of header cells
	 * @param array $tableBody Array of body rows (each row is an array of cells)
	 * @param array|null $tableFoot Optional array of footer cells
	 */
	public function __construct(?array $tableHead, array $tableBody, ?array $tableFoot)
	{
		if (!empty($tableHead)) {
			$this->tableHead = $tableHead;
		}

		$this->tableBody = $tableBody;

		if (!empty($tableFoot)) {
			$this->tableFoot = $tableFoot;
		}
	}

	/**
	 * Gets the table header row.
	 *
	 * Example:
	 * ```php
	 * $header = $table->getTableHead();
	 * // Returns: ['Name', 'Email', 'Role']
	 * ```
	 *
	 * @return array Array of header cells
	 */
	public function getTableHead(): array
	{
		return $this->tableHead;
	}

	/**
	 * Replaces the entire table header with a new row.
	 *
	 * Example:
	 * ```php
	 * $table->setTableHead(['ID', 'Name', 'Email']);
	 * ```
	 *
	 * @param array $tableHead Array of header cells to set
	 * @return self Returns this instance for method chaining
	 */
	public function setTableHead(array $tableHead): self
	{
		$this->tableHead = $tableHead;

		return $this;
	}

	/**
	 * Adds a single cell to the table header.
	 *
	 * Note: This method is an alias of addTableHeadCell() for backward compatibility.
	 * Consider using addTableHeadCell() for clarity.
	 *
	 * Example:
	 * ```php
	 * $table->setTableHeadCell('Name')
	 *       ->setTableHeadCell('Email');
	 * // Header: ['Name', 'Email']
	 * ```
	 *
	 * @param mixed $tableHead The cell value to add to the header
	 * @return self Returns this instance for method chaining
	 */
	public function setTableHeadCell(mixed $tableHead): self
	{
		$this->tableHead[] = $tableHead;

		return $this;
	}

	/**
	 * Adds a single cell to the table header.
	 *
	 * Example:
	 * ```php
	 * $table->addTableHeadCell('ID')
	 *       ->addTableHeadCell('Name')
	 *       ->addTableHeadCell('Price');
	 * // Header: ['ID', 'Name', 'Price']
	 * ```
	 *
	 * @param mixed $cell The cell value to add to the header
	 * @return self Returns this instance for method chaining
	 */
	public function addTableHeadCell(mixed $cell): self
	{
		$this->tableHead[] = $cell;

		return $this;
	}

	/**
	 * Checks if the table has a header row.
	 *
	 * Example:
	 * ```php
	 * if ($table->hasTableHead()) {
	 *     echo 'Table has a header';
	 * }
	 * ```
	 *
	 * @return bool True if header exists and is not empty, false otherwise
	 */
	public function hasTableHead(): bool
	{
		return !empty($this->tableHead);
	}

	/**
	 * Gets all table body rows.
	 *
	 * Example:
	 * ```php
	 * $rows = $table->getTableBody();
	 * // Returns: [[1, 'John'], [2, 'Jane'], [3, 'Bob']]
	 * ```
	 *
	 * @return array Array of body rows (each row is an array of cells)
	 */
	public function getTableBody(): array
	{
		return $this->tableBody;
	}

	/**
	 * Replaces all table body rows with new data.
	 *
	 * IMPORTANT: This method replaces ALL existing body data.
	 * To add rows instead, use addTableLine() or addTableLines().
	 *
	 * Example:
	 * ```php
	 * $table->setTableBody([
	 *     [1, 'Product A', 10.00],
	 *     [2, 'Product B', 20.00]
	 * ]);
	 * ```
	 *
	 * @param array $tableBody Array of rows to set as body (each row is an array of cells)
	 * @return self Returns this instance for method chaining
	 */
	public function setTableBody(array $tableBody): self
	{
		$this->tableBody = $tableBody;

		return $this;
	}

	/**
	 * Adds a single row to the table body.
	 *
	 * Example:
	 * ```php
	 * $table->addTableLine([1, 'John Doe', 'john@example.com'])
	 *       ->addTableLine([2, 'Jane Smith', 'jane@example.com']);
	 * ```
	 *
	 * @param array $line Array of cell values for the new row
	 * @return self Returns this instance for method chaining
	 */
	public function addTableLine(array $line): self
	{
		$this->tableBody[] = $line;

		return $this;
	}

	/**
	 * Adds multiple rows to the table body at once.
	 *
	 * Example:
	 * ```php
	 * $users = [
	 *     [1, 'John', 'Admin'],
	 *     [2, 'Jane', 'User'],
	 *     [3, 'Bob', 'Guest']
	 * ];
	 * $table->addTableLines($users);
	 * ```
	 *
	 * @param array $lines Array of rows, where each row is an array of cells
	 * @return self Returns this instance for method chaining
	 */
	public function addTableLines(array $lines): self
	{
		foreach ($lines as $line) {
			$this->tableBody[] = $line;
		}

		return $this;
	}

	/**
	 * Gets the table footer row.
	 *
	 * Example:
	 * ```php
	 * $footer = $table->getTableFoot();
	 * // Returns: ['Total', '', '125.50']
	 * ```
	 *
	 * @return array Array of footer cells
	 */
	public function getTableFoot(): array
	{
		return $this->tableFoot;
	}

	/**
	 * Replaces the entire table footer with a new row.
	 *
	 * Example:
	 * ```php
	 * $table->setTableFoot(['Total', 'Sum: $100.00', '']);
	 * ```
	 *
	 * @param array $tableFoot Array of footer cells to set
	 * @return self Returns this instance for method chaining
	 */
	public function setTableFoot(array $tableFoot): self
	{
		$this->tableFoot = $tableFoot;

		return $this;
	}

	/**
	 * Adds a single cell to the table footer.
	 *
	 * Note: This method is an alias of addTableFootCell() for backward compatibility.
	 * Consider using addTableFootCell() for clarity.
	 *
	 * Example:
	 * ```php
	 * $table->setTableFootCell('Total')
	 *       ->setTableFootCell('$100.00');
	 * // Footer: ['Total', '$100.00']
	 * ```
	 *
	 * @param mixed $tableFoot The cell value to add to the footer
	 * @return self Returns this instance for method chaining
	 */
	public function setTableFootCell(mixed $tableFoot): self
	{
		$this->tableFoot[] = $tableFoot;

		return $this;
	}

	/**
	 * Adds a single cell to the table footer.
	 *
	 * Example:
	 * ```php
	 * $table->addTableFootCell('Total')
	 *       ->addTableFootCell('')
	 *       ->addTableFootCell('$125.50');
	 * // Footer: ['Total', '', '$125.50']
	 * ```
	 *
	 * @param mixed $cell The cell value to add to the footer
	 * @return self Returns this instance for method chaining
	 */
	public function addTableFootCell(mixed $cell): self
	{
		$this->tableFoot[] = $cell;

		return $this;
	}

	/**
	 * Checks if the table has a footer row.
	 *
	 * Example:
	 * ```php
	 * if ($table->hasTableFoot()) {
	 *     echo 'Table has a footer';
	 * }
	 * ```
	 *
	 * @return bool True if footer exists and is not empty, false otherwise
	 */
	public function hasTableFoot(): bool
	{
		return !empty($this->tableFoot);
	}

	/**
	 * Gets the complete table data as a 2D array.
	 *
	 * Combines header, body, and footer into a single array structure.
	 * Each row's associative keys are converted to numeric indexes.
	 * This is useful for exporting to CSV, generating HTML tables, or console output.
	 *
	 * Example:
	 * ```php
	 * $table = new Table(
	 *     ['Name', 'Age'],
	 *     [['John', 30], ['Jane', 25]],
	 *     ['Total', '2 users']
	 * );
	 * $data = $table->getTableData();
	 * // Returns:
	 * // [
	 * //     ['Name', 'Age'],         // header
	 * //     ['John', 30],            // body row 1
	 * //     ['Jane', 25],            // body row 2
	 * //     ['Total', '2 users']     // footer
	 * // ]
	 * ```
	 *
	 * @return array 2D array containing all table rows with numeric indexes
	 */
	public function getTableData(): array
	{
		$table = [];

		if (!empty($this->tableHead)) {
			$table[] = array_values($this->tableHead);
		}

		foreach ($this->tableBody as $line) {
			$table[] = array_values($line);
		}

		if (!empty($this->tableFoot)) {
			$table[] = array_values($this->tableFoot);
		}

		return $table;
	}

	/**
	 * Checks if the table is completely empty (no header, body, or footer).
	 *
	 * Example:
	 * ```php
	 * $table = new Table(null, [], null);
	 * if ($table->isEmpty()) {
	 *     echo 'Table is empty';
	 * }
	 * ```
	 *
	 * @return bool True if table has no data in any section, false otherwise
	 */
	public function isEmpty(): bool
	{
		return empty($this->tableHead) && empty($this->tableBody) && empty($this->tableFoot);
	}

	/**
	 * Gets the total number of rows in the table, including header and footer.
	 *
	 * Example:
	 * ```php
	 * $count = $table->getRowCount();
	 * // With header: 1, body: 5 rows, footer: 1 = returns 7
	 * ```
	 *
	 * @return int Total number of rows across all sections
	 */
	public function getRowCount(): int
	{
		$count = count($this->tableBody);

		if (!empty($this->tableHead)) {
			$count++;
		}

		if (!empty($this->tableFoot)) {
			$count++;
		}

		return $count;
	}

	/**
	 * Gets the number of data rows in the table body (excludes header and footer).
	 *
	 * Example:
	 * ```php
	 * $bodyRows = $table->getBodyRowCount();
	 * // Returns only the number of data rows
	 * ```
	 *
	 * @return int Number of rows in the body section
	 */
	public function getBodyRowCount(): int
	{
		return count($this->tableBody);
	}

	/**
	 * Gets the number of columns based on the header row.
	 *
	 * Returns 0 if no header is set.
	 * Note: This assumes all rows have the same number of columns as the header.
	 *
	 * Example:
	 * ```php
	 * $table->setTableHead(['Name', 'Email', 'Role']);
	 * $columns = $table->getColumnCount(); // Returns: 3
	 * ```
	 *
	 * @return int Number of columns, or 0 if no header exists
	 */
	public function getColumnCount(): int
	{
		return count($this->tableHead);
	}

	/**
	 * Clears all table data (header, body, and footer).
	 *
	 * Example:
	 * ```php
	 * $table->clear();
	 * // Table is now empty and can be repopulated
	 * ```
	 *
	 * @return self Returns this instance for method chaining
	 */
	public function clear(): self
	{
		$this->tableHead = [];
		$this->tableBody = [];
		$this->tableFoot = [];

		return $this;
	}

	/**
	 * Clears only the table body, preserving header and footer.
	 *
	 * Example:
	 * ```php
	 * $table->clearBody();
	 * // Header and footer remain, only data rows are removed
	 * ```
	 *
	 * @return self Returns this instance for method chaining
	 */
	public function clearBody(): self
	{
		$this->tableBody = [];

		return $this;
	}
}