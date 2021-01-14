<?php

namespace js\tools\commons\tests\upload;

use js\tools\commons\upload\UploadedFile;
use PHPUnit\Framework\TestCase;

class UploadedFileTest extends TestCase
{
	public function testValidFile(): void
	{
		$uploadedFile = new UploadedFile(
			[
				'name'     => 'example.txt',
				'type'     => 'text/plain',
				'tmp_name' => __FILE__, // Needs to be a real file for mime type retrieval.
				'error'    => UPLOAD_ERR_OK,
				'size'     => 666,
			]
		);
		
		$this->assertTrue($uploadedFile->isValid());
		$this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getErrorCode());
		$this->assertSame('UPLOAD_ERR_OK', $uploadedFile->getErrorConstant());
		$this->assertSame('Upload successful', $uploadedFile->getErrorMessage());
		
		$this->assertSame('example.txt', $uploadedFile->getName());
		$this->assertSame(__FILE__, $uploadedFile->getTempFilePath());
		$this->assertSame('text/x-php', $uploadedFile->getMimeType());
		$this->assertSame(666, $uploadedFile->getSize());
	}
	
	public function testInvalidFile(): void
	{
		$uploadedFile = new UploadedFile(
			[
				'name'     => 'example.txt',
				'type'     => 'text/plain',
				'tmp_name' => __FILE__,
				'error'    => UPLOAD_ERR_CANT_WRITE,
				'size'     => 666,
			]
		);
		
		$this->assertFalse($uploadedFile->isValid());
		$this->assertSame(UPLOAD_ERR_CANT_WRITE, $uploadedFile->getErrorCode());
		$this->assertSame('UPLOAD_ERR_CANT_WRITE', $uploadedFile->getErrorConstant());
		$this->assertSame('Failed to write the file to disk', $uploadedFile->getErrorMessage());
		$this->assertSame('text/plain', $uploadedFile->getMimeType());
	}
}
