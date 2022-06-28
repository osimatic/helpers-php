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
	 * Mime Type of the file (if data sent directly instead of uploaded file)
	 * @var string|null
	 */
	private ?string $mimeType;

	/**
	 * @param array|null $uploadedFileInfos
	 * @param string|null $data
	 * @param string|null $mimeType
	 */
	public function __construct(?array $uploadedFileInfos=null, ?string $data=null, ?string $mimeType=null)
	{
		if (null !== $uploadedFileInfos) {
			$this->setUploadedFileInfos($uploadedFileInfos);
		}
		$this->data = $data;
		$this->mimeType = $mimeType;
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
	public function getExtension(): ?string
	{
		if (!empty($this->getOriginalFileName())) {
			return \Osimatic\Helpers\FileSystem\File::getExtension($this->getOriginalFileName());
		}
		if (!empty($this->getMimeType()) && null !== ($extension = \Osimatic\Helpers\FileSystem\File::getExtensionFromMimeType($this->getMimeType()))) {
			return $extension;
		}
		return null;
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

	/**
	 * @return string|null
	 */
	public function getMimeType(): ?string
	{
		return $this->mimeType;
	}

	/**
	 * @param string|null $mimeType
	 */
	public function setMimeType(?string $mimeType): void
	{
		$this->mimeType = $mimeType;
	}

}