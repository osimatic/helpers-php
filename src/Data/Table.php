<?php

namespace Osimatic\Data;

class Table
{
	private array $tableHead = [];
	private array $tableBody = [];
	private array $tableFoot = [];

	/**
	 * @param array|null $tableHead
	 * @param array $tableBody
	 * @param array|null $tableFoot
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
	 * @return array
	 */
	public function getTableHead(): array
	{
		return $this->tableHead;
	}

	/**
	 * @param $tableHead
	 * @return self
	 */
	public function setTableHeadCell($tableHead): self
	{
		$this->tableHead[] = $tableHead;

		return $this;
	}

	/**
	 * @param $cell
	 * @return self
	 */
	public function addTableHeadCell($cell): self
	{
		$this->tableHead[] = $cell;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getTableBody(): array
	{
		return $this->tableBody;
	}

	/**
	 * @param array $tableBody
	 * @return self
	 */
	public function setTableBody(array $tableBody): self
	{
		$this->tableBody[] = $tableBody;

		return $this;
	}

	/**
	 * @param array $line
	 * @return self
	 */
	public function addTableLine(array $line): self
	{
		$this->tableBody[] = $line;

		return $this;
	}

	/**
	 * @param array $lines
	 * @return self
	 */
	public function addTableLines(array $lines): self
	{
		foreach ($lines as $line) {
			$this->tableBody[] = $line;
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getTableFoot(): array
	{
		return $this->tableFoot;
	}

	/**
	 * @param $tableFoot
	 * @return self
	 */
	public function setTableFootCell($tableFoot): self
	{
		$this->tableFoot[] = $tableFoot;

		return $this;
	}

	/**
	 * @param $cell
	 * @return self
	 */
	public function addTableFootCell($cell): self
	{
		$this->tableFoot[] = $cell;

		return $this;
	}

	/**
	 * @return array
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
}