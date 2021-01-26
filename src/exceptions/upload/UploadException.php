<?php
namespace js\tools\commons\exceptions\upload;

use Exception;
use js\tools\commons\upload\UploadedFile;

class UploadException extends Exception
{
	private UploadedFile $uploadedFile;
	
	public function __construct(UploadedFile $file)
	{
		parent::__construct($file->getErrorMessage(), $file->getErrorCode());
		$this->uploadedFile = $file;
	}
	
	public function getUploadedFile(): UploadedFile
	{
		return $this->uploadedFile;
	}
}
