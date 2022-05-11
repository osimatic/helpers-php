<?php

namespace Osimatic\Helpers\FileSystem;

class InputFile
{
	/**
	 * The filename of the uploaded file.
	 * @var string|null
	 */
	private ?string $uploadedFilePath;

	/**
	 * @var string|null
	 */
	private ?string $originalFileName;

	/**
	 * Data of the file (if data sent directly instead of uploaded file)
	 * @var string|null
	 */
	private ?string $data;

	/**
	 * @param array|null $uploadedFileInfos
	 * @param string|null $data
	 */
	public function __construct(?array $uploadedFileInfos, ?string $data)
	{
		$this->setUploadedFileInfos($uploadedFileInfos);
		$this->data = $data;
	}


	/**
	 * @param array $uploadedFileInfos
	 */
	public function setUploadedFileInfos(array $uploadedFileInfos): void
	{
		$this->uploadedFilePath = $file['tmp_name'] ?? null;
		$this->originalFileName = $file['name'] ?? null;
	}



	/**
	 * @return string|null
	 */
	public function getUploadedFilePath(): ?string
	{
		return $this->uploadedFilePath ?? null;
	}

	/**
	 * @param string|null $uploadedFilePath
	 */
	public function setUploadedFilePath(?string $uploadedFilePath): void
	{
		$this->uploadedFilePath = $uploadedFilePath;
	}

	/**
	 * @return string|null
	 */
	public function getOriginalFileName(): ?string
	{
		return $this->originalFileName ?? null;
	}

	/**
	 * @param string|null $originalFileName
	 */
	public function setOriginalFileName(?string $originalFileName): void
	{
		$this->originalFileName = $originalFileName;
	}

	/**
	 * @return string|null
	 */
	public function getData(): ?string
	{
		return $this->data ?? null;
	}

	/**
	 * @param string|null $data
	 */
	public function setData(?string $data): void
	{
		$this->data = $data;
	}

}