<?php

namespace Osimatic\FileSystem;

class OutputFile
{
	/**
	 * The full file path of the file.
	 * @var string|null
	 */
	private ?string $filePath;

	/**
	 * The displayed file name of the file (filename for the user who download the file). If null, filename in file path will be used.
	 * @var string|null
	 */
	private ?string $fileName;

	/**
	 * @param string|null $filePath
	 * @param string|null $fileName
	 */
	public function __construct(?string $filePath=null, ?string $fileName=null)
	{
		$this->filePath = $filePath;
		$this->fileName = $fileName;
	}

	public function getFilePath(): ?string
	{
		return $this->filePath;
	}

	public function setFilePath(?string $filePath): void
	{
		$this->filePath = $filePath;
	}

	public function getFileName(): ?string
	{
		return $this->fileName;
	}

	public function setFileName(?string $fileName): void
	{
		$this->fileName = $fileName;
	}

	public function getExtension(): ?string
	{
		$path = $this->filePath ?? $this->fileName;
		if (!$path) {
			return null;
		}

		return strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: null;
	}
}