<?php

namespace Osimatic\FileSystem;

class InputFile
{
	/**
	 * The full file path of the uploaded file.
	 * @var string|null
	 */
	private ?string $uploadedFilePath;

	/**
	 * The original filename of the uploaded file.
	 * @var string|null
	 */
	private ?string $originalFileName;

	/**
	 * Binary data of the file (if data sent directly instead of uploaded file)
	 * @var string|null
	 */
	private ?string $data;

	/**
	 * Base64 encoded data of the file (if data sent directly instead of uploaded file)
	 * @var string|null
	 */
	private ?string $base64EncodedData;

	/**
	 * Mime Type of the file (if data sent directly instead of uploaded file)
	 * @var string|null
	 */
	private ?string $mimeType;

	/**
	 * @param array|null $uploadedFileInfos
	 * @param string|null $data
	 * @param string|null $mimeType
	 * @param string|null $base64EncodedData
	 */
	public function __construct(?array $uploadedFileInfos=null, ?string $data=null, ?string $mimeType=null, ?string $base64EncodedData=null)
	{
		if (null !== $uploadedFileInfos) {
			$this->setUploadedFileInfos($uploadedFileInfos);
		}
		$this->data = $data;
		$this->base64EncodedData = $base64EncodedData;
		$this->mimeType = $mimeType;
	}


	/**
	 * @param array $uploadedFileInfos
	 */
	public function setUploadedFileInfos(array $uploadedFileInfos): void
	{
		$this->uploadedFilePath = $uploadedFileInfos['tmp_name'] ?? null;
		$this->originalFileName = $uploadedFileInfos['name'] ?? null;
	}

	/**
	 * @return string|null
	 */
	public function getExtension(): ?string
	{
		if (!empty($this->getOriginalFileName())) {
			return File::getExtension($this->getOriginalFileName());
		}
		if (!empty($this->getMimeType()) && null !== ($extension = File::getExtensionFromMimeType($this->getMimeType()))) {
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
	public function getBase64EncodedData(): ?string
	{
		return $this->base64EncodedData;
	}

	/**
	 * @param string|null $base64EncodedData
	 */
	public function setBase64EncodedData(?string $base64EncodedData): void
	{
		$this->base64EncodedData = $base64EncodedData;
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