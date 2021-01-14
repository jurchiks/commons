<?php
namespace js\tools\commons\exceptions\upload;

use Exception;
use js\tools\commons\upload\UploadedFile;

/**
 * This exception is thrown if a file was not uploaded successfully.
 * 
 * @see UploadException::getUploadedFile()
 */
class UploadException extends Exception
{
	private $uploadedFile;
	
	public function __construct(UploadedFile $file)
	{
		parent::__construct($file->getErrorMessage(), $file->getErrorCode());
		$this->uploadedFile = $file;
	}
	
	/**
	 * Get the file that was not uploaded successfully.
	 * 
	 * @return UploadedFile
	 */
	public function getUploadedFile()
	{
		return $this->uploadedFile;
	}
}
