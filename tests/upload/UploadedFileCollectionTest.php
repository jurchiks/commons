<?php
namespace js\tools\commons\tests\upload;

use js\tools\commons\exceptions\upload\UploadException;
use js\tools\commons\upload\UploadedFile;
use js\tools\commons\upload\UploadedFileCollection;
use PHPUnit\Framework\TestCase;

class UploadedFileCollectionTest extends TestCase
{
	public function testSingleFile(): void
	{
		// <input type="file" name="foo" />
		$files = [
			'foo' => [
				'name'     => 'example.txt',
				'type'     => 'text/plain',
				'tmp_name' => '/path/to/tmp/directory/phpC887.tmp',
				'error'    => UPLOAD_ERR_OK,
				'size'     => 666,
			],
		];
		$collection = new UploadedFileCollection($files);
		
		$this->assertNotEmpty($collection->getAll());
		$this->assertInstanceOf(UploadedFile::class, $collection->get('foo'));
	}
	
	public function testFileList(): void
	{
		// <input type="file" name="foo[]" />
		$files = [
			'foo' => [
				'name'     => [0 => 'example1.txt', 1 => 'example2.txt'],
				'type'     => [0 => 'text/plain', 1 => 'text/plain'],
				'tmp_name' => [0 => '/path/to/tmp/directory/phpC887.tmp', 1 => '/path/to/tmp/directory/phpC888.tmp'],
				'error'    => [0 => UPLOAD_ERR_OK, 1 => UPLOAD_ERR_OK],
				'size'     => [0 => 666, 1 => 999],
			],
		];
		$collection = new UploadedFileCollection($files);
		$this->assertNotEmpty($collection->getAll());
		$this->assertContainsOnlyInstancesOf(UploadedFile::class, $collection->getArray('foo'));
		
		foreach ($collection->getArray('foo') as $uploadedFile)
		{
			/** @var UploadedFile $uploadedFile */
			$this->assertTrue($uploadedFile->isValid());
		}
	}
	
	public function testFileListNamed(): void
	{
		// <input type="file" name="foo[bar]" />
		$files = [
			'foo' => [
				'name'     => ['bar' => 'example.txt'],
				'type'     => ['bar' => 'text/plain'],
				'tmp_name' => ['bar' => '/path/to/tmp/directory/phpC887.tmp'],
				'error'    => ['bar' => UPLOAD_ERR_OK],
				'size'     => ['bar' => 666],
			],
		];
		$collection = new UploadedFileCollection($files);
		$this->assertNotEmpty($collection->getAll());
		$this->assertContainsOnlyInstancesOf(UploadedFile::class, $collection->getArray('foo'));
		
		/** @var UploadedFile $uploadedFile */
		$uploadedFile = $collection->get('foo.bar');
		$this->assertSame('example.txt', $uploadedFile->getName());
	}
	
	public function testFileCollection(): void
	{
		// <input type="file" name="foo[bar][]" />
		$files = [
			'foo' => [
				'name'     => ['bar' => [0 => 'example.txt']],
				'type'     => ['bar' => [0 => 'text/plain']],
				'tmp_name' => ['bar' => [0 => '/path/to/tmp/directory/phpC887.tmp']],
				'error'    => ['bar' => [0 => UPLOAD_ERR_OK]],
				'size'     => ['bar' => [0 => 666]],
			],
		];
		$collection = new UploadedFileCollection($files);
		$this->assertNotEmpty($collection->getAll());
		$this->assertContainsOnlyInstancesOf(UploadedFile::class, $collection->getArray('foo.bar'));
		
		/** @var UploadedFile $uploadedFile */
		$uploadedFile = $collection->getArray('foo.bar')[0];
		$this->assertSame('example.txt', $uploadedFile->getName());
	}
	
	public function testFileCollectionNamed(): void
	{
		// <input type="file" name="foo[bar][baz]" />
		$files = [
			'foo' => [
				'name'     => ['bar' => ['baz' => 'example.txt']],
				'type'     => ['bar' => ['baz' => 'text/plain']],
				'tmp_name' => ['bar' => ['baz' => '/path/to/tmp/directory/phpC887.tmp']],
				'error'    => ['bar' => ['baz' => UPLOAD_ERR_OK]],
				'size'     => ['bar' => ['baz' => 666]],
			],
		];
		$collection = new UploadedFileCollection($files);
		
		$this->assertNotEmpty($collection->getAll());
		$this->assertInstanceOf(UploadedFile::class, $collection->get('foo.bar.baz'));
		
		/** @var UploadedFile $uploadedFile */
		$uploadedFile = $collection->get('foo.bar.baz');
		$this->assertSame('example.txt', $uploadedFile->getName());
	}
	
	public function testMixedStructures(): void
	{
		$files = [
			// <input type="file" name="foo" />
			'foo' => [
				'name'     => 'example1.txt',
				'type'     => 'text/plain',
				'tmp_name' => '/path/to/tmp/directory/phpC887.tmp',
				'error'    => UPLOAD_ERR_OK,
				'size'     => 666,
			],
			// <input type="file" name="bar[baz]" />
			'bar' => [
				'name'     => ['baz' => 'example2.txt'],
				'type'     => ['baz' => 'text/plain'],
				'tmp_name' => ['baz' => '/path/to/tmp/directory/phpC888.tmp'],
				'error'    => ['baz' => UPLOAD_ERR_OK],
				'size'     => ['baz' => 666],
			],
			// <input type="file" name="qux[quux][]" />
			'qux' => [
				'name'     => ['quux' => [0 => 'example3.txt']],
				'type'     => ['quux' => [0 => 'text/plain']],
				'tmp_name' => ['quux' => [0 => '/path/to/tmp/directory/phpC889.tmp']],
				'error'    => ['quux' => [0 => UPLOAD_ERR_OK]],
				'size'     => ['quux' => [0 => 666]],
			],
		];
		$collection = new UploadedFileCollection($files);
		
		$this->assertNotEmpty($collection->getAll());
		$this->assertInstanceOf(UploadedFile::class, $collection->get('foo'));
		$this->assertInstanceOf(UploadedFile::class, $collection->get('bar.baz'));
		$this->assertContainsOnlyInstancesOf(UploadedFile::class, $collection->getArray('qux.quux'));
	}
	
	public function testUploadErrorException(): void
	{
		$this->expectException(UploadException::class);
		
		$files = [
			// <input type="file" name="foo[]" />
			'foo' => [
				'name'     => [0 => 'example1.txt', 1 => 'example2.txt'],
				'type'     => [0 => 'text/plain', 1 => 'text/plain'],
				'tmp_name' => [0 => '/path/to/tmp/directory/phpC887.tmp', 1 => '/path/to/tmp/directory/phpC888.tmp'],
				'error'    => [0 => UPLOAD_ERR_OK, 1 => UPLOAD_ERR_CANT_WRITE],
				'size'     => [0 => 666, 1 => 666],
			],
		];
		new UploadedFileCollection($files);
	}
	
	public function testUploadErrorManual(): void
	{
		$files = [
			// <input type="file" name="foo[]" />
			'foo' => [
				'name'     => [0 => 'example1.txt', 1 => 'example2.txt'],
				'type'     => [0 => 'text/plain', 1 => 'text/plain'],
				'tmp_name' => [0 => '/path/to/tmp/directory/phpC887.tmp', 1 => '/path/to/tmp/directory/phpC888.tmp'],
				'error'    => [0 => UPLOAD_ERR_OK, 1 => UPLOAD_ERR_CANT_WRITE],
				'size'     => [0 => 666, 1 => 666],
			],
		];
		$collection = new UploadedFileCollection($files, false);
		
		/** @var UploadedFile[] $uploadedFiles */
		$uploadedFiles = $collection->getArray('foo');
		
		$this->assertSame(2, count($uploadedFiles));
		$this->assertContainsOnlyInstancesOf(UploadedFile::class, $uploadedFiles);
		
		$this->assertTrue($uploadedFiles[0]->isValid());
		$this->assertFalse($uploadedFiles[1]->isValid());
	}
}
