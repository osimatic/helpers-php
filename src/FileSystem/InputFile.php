<?php

namespace Osimatic\FileSystem;

/**
 * Represents an input file from upload or base64 data.
 * This class encapsulates file information for files received through:
 * - Traditional HTTP file uploads ($_FILES)
 * - Base64-encoded data from API requests
 *
 * Provides properties to store both the uploaded file location and
 * the decoded binary data for further processing.
 */
class InputFile
{
	/**
	 * The full file path of the uploaded file (temporary location).
	 * @var string|null
	 */
	private ?string $uploadedFilePath = null;

	/**
	 * The full file path of the file after it has been moved from temporary location.
	 * @var string|null
	 */
	private ?string $path = null;

	/**
	 * The original filename provided by the client during upload.
	 * @var string|null
	 */
	private ?string $originalFileName = null;

	/**
	 * Binary data of the file (when data is sent directly instead of uploaded file).
	 * @var string|null
	 */
	private ?string $data = null;

	/**
	 * Base64-encoded data of the file (when data is sent directly instead of uploaded file).
	 * @var string|null
	 */
	private ?string $base64EncodedData = null;

	/**
	 * MIME type of the file (when data is sent directly instead of uploaded file).
	 * @var string|null
	 */
	private ?string $mimeType = null;

	/**
	 * Size of the file in bytes.
	 * @var int|null
	 */
	private ?int $fileSize = null;

	/**
	 * Creates a new InputFile instance.
	 * @param array|null $uploadedFileInfos Information from $_FILES array
	 * @param string|null $data Binary data of the file
	 * @param string|null $mimeType MIME type of the file
	 * @param string|null $base64EncodedData Base64-encoded representation of the file
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
	 * Sets the uploaded file information from $_FILES array.
	 * @param array $uploadedFileInfos Array containing 'tmp_name' and 'name' keys
	 */
	public function setUploadedFileInfos(array $uploadedFileInfos): void
	{
		$this->uploadedFilePath = $uploadedFileInfos['tmp_name'] ?? null;
		$this->originalFileName = $uploadedFileInfos['name'] ?? null;
	}

	/**
	 * Gets the file extension based on the original filename or MIME type.
	 * @return string|null The file extension (without dot), or null if cannot be determined
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


	// ========== Getters and Setters ==========

	/**
	 * Gets the temporary path of the uploaded file.
	 * @return string|null The uploaded file path
	 */
	public function getUploadedFilePath(): ?string
	{
		return $this->uploadedFilePath;
	}

	/**
	 * Sets the temporary path of the uploaded file.
	 * @param string|null $uploadedFilePath The uploaded file path
	 * @return self Returns this instance for method chaining
	 */
	public function setUploadedFilePath(?string $uploadedFilePath): self
	{
		$this->uploadedFilePath = $uploadedFilePath;

		return $this;
	}

	/**
	 * Gets the final path of the file after processing.
	 * @return string|null The file path
	 */
	public function getPath(): ?string
	{
		return $this->path;
	}

	/**
	 * Sets the final path of the file after processing.
	 * @param string|null $path The file path
	 * @return self Returns this instance for method chaining
	 */
	public function setPath(?string $path): self
	{
		$this->path = $path;

		return $this;
	}

	/**
	 * Gets the original filename from the client.
	 * @return string|null The original filename
	 */
	public function getOriginalFileName(): ?string
	{
		return $this->originalFileName;
	}

	/**
	 * Sets the original filename from the client.
	 * @param string|null $originalFileName The original filename
	 * @return self Returns this instance for method chaining
	 */
	public function setOriginalFileName(?string $originalFileName): self
	{
		$this->originalFileName = $originalFileName;

		return $this;
	}

	/**
	 * Gets the binary data of the file.
	 * @return string|null The file data
	 */
	public function getData(): ?string
	{
		return $this->data;
	}

	/**
	 * Sets the binary data of the file.
	 * @param string|null $data The file data
	 * @return self Returns this instance for method chaining
	 */
	public function setData(?string $data): self
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * Gets the base64-encoded data of the file.
	 * @return string|null The base64-encoded data
	 */
	public function getBase64EncodedData(): ?string
	{
		return $this->base64EncodedData;
	}

	/**
	 * Sets the base64-encoded data of the file.
	 * @param string|null $base64EncodedData The base64-encoded data
	 * @return self Returns this instance for method chaining
	 */
	public function setBase64EncodedData(?string $base64EncodedData): self
	{
		$this->base64EncodedData = $base64EncodedData;

		return $this;
	}

	/**
	 * Gets the MIME type of the file.
	 * @return string|null The MIME type
	 */
	public function getMimeType(): ?string
	{
		return $this->mimeType;
	}

	/**
	 * Sets the MIME type of the file.
	 * @param string|null $mimeType The MIME type
	 * @return self Returns this instance for method chaining
	 */
	public function setMimeType(?string $mimeType): self
	{
		$this->mimeType = $mimeType;

		return $this;
	}

	/**
	 * Gets the size of the file in bytes.
	 * @return int|null The file size
	 */
	public function getFileSize(): ?int
	{
		return $this->fileSize;
	}

	/**
	 * Sets the size of the file in bytes.
	 * @param int|null $fileSize The file size
	 * @return self Returns this instance for method chaining
	 */
	public function setFileSize(?int $fileSize): self
	{
		$this->fileSize = $fileSize;

		return $this;
	}

}