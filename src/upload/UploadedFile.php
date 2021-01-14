<?php
namespace js\tools\commons\upload;

use finfo;
use js\tools\commons\exceptions\upload\FileMoveException;

/**
 * This class is a wrapper for data contained in the $_FILES array.
 * It also adds a few utility methods.
 */
class UploadedFile
{
	/** @var string */
	private $name;
	/** @var int */
	private $statusCode;
	/** @var int */
	private $size;
	/** @var string */
	private $path;
	/** @var string */
	private $type = null;
	/** @var string */
	private $originalType;
	
	private $hasBeenMoved = false;
	
	public function __construct(array $data)
	{
		$this->name = $data['name'];
		$this->originalType = $data['type'];
		$this->statusCode = $data['error'];
		$this->size = $data['size'];
		$this->path = $data['tmp_name'];
	}
	
	/**
	 * Get the original filename of the uploaded file.
	 */
	public function getName(): string
	{
		return $this->name;
	}
	
	/**
	 * Get the size of the uploaded file in bytes.
	 */
	public function getSize(): int
	{
		return $this->size;
	}
	
	/**
	 * Get the MIME type of the uploaded file.
	 * If the file was uploaded successfully and the `fileinfo` extension is enabled,
	 * this will read the MIME type from the file contents instead of from the upload info.
	 */
	public function getMimeType(): string
	{
		if (($this->type === null) && $this->isValid() && extension_loaded('fileinfo'))
		{
			$this->type = (new finfo(FILEINFO_MIME_TYPE))->file($this->path);
		}
		else
		{
			$this->type = $this->originalType;
		}
		
		return $this->type;
	}
	
	/**
	 * Get the absolute path to the temporary uploaded file.
	 * This can then be passed to {@link move_uploaded_file()} to move it wherever necessary.
	 */
	public function getTempFilePath(): string
	{
		return $this->path;
	}
	
	/**
	 * Get the error code of the upload status.
	 *
	 * @see getErrorConstant
	 * @see getErrorMessage
	 * @see isValid
	 * @see http://php.net/manual/en/features.file-upload.errors.php
	 */
	public function getErrorCode(): int
	{
		return $this->statusCode;
	}
	
	/**
	 * Check if the file was uploaded successfully.
	 *
	 * @see getErrorCode
	 * @see getErrorConstant
	 * @see getErrorMessage
	 */
	public function isValid(): bool
	{
		return ($this->statusCode === UPLOAD_ERR_OK);
	}
	
	/**
	 * Get the name of the UPLOAD_ERR_* constant that corresponds
	 * to the error code of the upload status.
	 * The value of the constant can be retrieved via {@link constant}().
	 *
	 * @see http://php.net/manual/en/features.file-upload.errors.php
	 */
	public function getErrorConstant(): string
	{
		static $constants = [
			UPLOAD_ERR_OK         => 'UPLOAD_ERR_OK',
			UPLOAD_ERR_INI_SIZE   => 'UPLOAD_ERR_INI_SIZE',
			UPLOAD_ERR_FORM_SIZE  => 'UPLOAD_ERR_FORM_SIZE',
			UPLOAD_ERR_PARTIAL    => 'UPLOAD_ERR_PARTIAL',
			UPLOAD_ERR_NO_FILE    => 'UPLOAD_ERR_NO_FILE',
			UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
			UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE',
			UPLOAD_ERR_EXTENSION  => 'UPLOAD_ERR_EXTENSION',
		];
		
		return $constants[$this->statusCode];
	}
	
	/**
	 * Get a human-readable error message of the upload status.
	 * Do not use this to check the upload status!
	 *
	 * @see getErrorCode
	 * @see getErrorConstant
	 * @see isValid
	 * @see http://php.net/manual/en/features.file-upload.errors.php
	 */
	public function getErrorMessage(): string
	{
		static $messages = [
			UPLOAD_ERR_OK         => 'Upload successful',
			UPLOAD_ERR_INI_SIZE   => 'The size of the file exceeds the value of the "upload_max_filesize" directive in php.ini',
			UPLOAD_ERR_FORM_SIZE  => 'The size of the file exceeds the value of the "MAX_FILE_SIZE" input in the HTML form',
			UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded',
			UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
			UPLOAD_ERR_NO_TMP_DIR => 'Temporary directory is missing',
			UPLOAD_ERR_CANT_WRITE => 'Failed to write the file to disk',
			UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload',
		];
		
		return $messages[$this->statusCode];
	}
	
	/**
	 * Move the newly uploaded file to another directory and optionally rename it.
	 * This method can only be called once, subsequent calls will throw an exception.
	 *
	 * @param string $destination : the absolute path to where to move the file; if this does not include the file name,
	 * the original file name is used
	 * @return string the absolute path to the moved file
	 * @throws FileMoveException if anything is wrong or if attempting to move the file more than once
	 */
	public function moveTo(string $destination)
	{
		if (!$this->isValid())
		{
			throw new FileMoveException('File was not uploaded successfully and thus cannot be moved');
		}
		
		if ($this->hasBeenMoved)
		{
			throw new FileMoveException('Uploaded file has already been moved');
		}
		
		if (is_dir($destination))
		{
			$directory = $destination;
			$filename = $this->name;
		}
		else
		{
			$directory = dirname($destination);
			$filename = basename($destination);
		}
		
		if (!is_dir($directory))
		{
			throw new FileMoveException($destination . ' - folder does not exist');
		}
		
		if (!is_writable($directory))
		{
			throw new FileMoveException($destination . ' - folder is not writable, check permissions');
		}
		
		if (!move_uploaded_file($this->path, $directory . DIRECTORY_SEPARATOR . $filename))
		{
			throw new FileMoveException('Failed to move uploaded file to ' . $destination);
		}
		
		$this->hasBeenMoved = true;
		
		return $directory . DIRECTORY_SEPARATOR . $filename;
	}
}
