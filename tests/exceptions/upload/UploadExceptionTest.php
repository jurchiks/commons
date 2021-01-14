<?php
namespace js\tools\commons\tests\exceptions\upload;

use js\tools\commons\exceptions\upload\UploadException;
use js\tools\commons\upload\UploadedFile;
use js\tools\commons\upload\UploadedFileCollection;
use PHPUnit\Framework\TestCase;

class UploadExceptionTest extends TestCase
{
	public function testFailedUpload(): void
	{
		// <input type="file" name="foo" />
		$files = [
			'foo' => [
				'name'     => 'example.txt',
				'type'     => 'text/plain',
				'tmp_name' => '/path/to/tmp/directory/phpC887.tmp',
				'error'    => UPLOAD_ERR_CANT_WRITE,
				'size'     => 666,
			],
		];
		$collection = new UploadedFileCollection($files, false);
		/** @var UploadedFile $uploadedFile */
		$uploadedFile = $collection->get('foo');
		$exception = new UploadException($uploadedFile);
		
		$this->assertInstanceOf(UploadedFile::class, $exception->getUploadedFile());
		$this->assertSame($uploadedFile->getErrorMessage(), $exception->getMessage());
		$this->assertSame($uploadedFile->getErrorCode(), $exception->getCode());
	}
}
