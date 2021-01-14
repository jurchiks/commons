<?php
namespace js\tools\commons\upload;

use js\tools\commons\exceptions\upload\UploadException;
use js\tools\commons\traits\DataAccessor;

class UploadedFileCollection
{
	use DataAccessor;
	
	/**
	 * Convert all the different $_FILES structures into one intuitive  structure.
	 * The default PHP implementation of $_FILES returns a different structure for each case of input nesting:
	 * <ul>
	 * <li>&lt;input type="file" name="file1" /&gt;<br/>
	 * returns ['file1' => [
	 * 'name' => string,
	 * 'type' => string,
	 * 'tmp_name' => string,
	 * 'error' => int,
	 * 'size' => int
	 * ]]</li>
	 * <li>&lt;input type="file" name="file2[]" /&gt;<br/>
	 * returns ['file2' => [
	 * 'name' => [0 => string],
	 * 'type' => [0 => string],
	 * 'tmp_name' => [0 => string],
	 * 'error' => [0 => int],
	 * 'size' => [0 => int],
	 * ]]</li>
	 * <li>&lt;input type="file" name="file3[nested]" /&gt;<br/>
	 * returns ['file3' => [
	 * 'name' => ['nested' => string],
	 * 'type' => ['nested' => string],
	 * 'tmp_name' => ['nested' => string],
	 * 'error' => ['nested' => int],
	 * 'size' => ['nested' => int],
	 * ]]</li>
	 * <li>&lt;input type="file" name="file4[nested][]" /&gt;<br/>
	 * returns ['file4' => [
	 * 'name' => ['nested' => [0 => string]],
	 * 'type' => ['nested' => [0 => string]],
	 * 'tmp_name' => ['nested' => [0 => string]],
	 * 'error' => ['nested' => [0 => int]],
	 * 'size' => ['nested' => [0 => int]],
	 * ]]</li>
	 * </ul>
	 * Obviously, such structure is confusing and can cause headache for anyone.
	 * Thankfully, it is consistently inconsistent and thus can be reliably converted to a consistent, intuitive
	 * structure. This method converts all of these formats to nested arrays of {@link UploadedFile} objects,
	 * such that the input of the following:
	 * <ul>
	 * <li>&lt;input type="file" name="file1" /&gt;<br/>
	 * results in ['file1' => {@link UploadedFile}]</li>
	 * <li>&lt;input type="file" name="file2[]" /&gt;<br/>
	 * results in ['file2' => [0 => {@link UploadedFile}]]</li>
	 * <li>&lt;input type="file" name="file3[nested]" /&gt;<br/>
	 * results in ['file3' => ['nested' => {@link UploadedFile}]]</li>
	 * <li>&lt;input type="file" name="file4[nested][]" /&gt;<br/>
	 * results in ['file4' => ['nested' => [0 => {@link UploadedFile}]]]</li>
	 * </ul>
	 * And so on.
	 *
	 * @param array $files : the $_FILES array to normalize
	 * @param bool $throwException : if true, an {@link UploadException}
	 * will be thrown if any of the files did not upload successfully.
	 * If this is parameter is false, you have to check the error values manually
	 * @throws UploadException if $throwException is true and any of the files failed to upload
	 */
	public function __construct(array $files, bool $throwException = true)
	{
		foreach ($files as $key => $file)
		{
			$files[$key] = self::normalizeFile($file, $throwException);
		}
		
		$this->init($files);
	}
	
	private static function normalizeFile(array $file, bool $throwException)
	{
		if (is_string($file['name']))
		{
			// case #1 - normal file structure (name="file")
			return self::makeFile($file, $throwException);
		}
		
		// any other input name automatically means an array of files
		$files = [];
		
		foreach ($file['name'] as $key => $value)
		{
			$data = [
				'name'     => $value,
				'type'     => $file['type'][$key],
				'tmp_name' => $file['tmp_name'][$key],
				'error'    => $file['error'][$key],
				'size'     => $file['size'][$key],
			];
			
			if (is_string($value))
			{
				// case #2 - array of files (name="file[]")
				$files[$key] = self::makeFile($data, $throwException);
			}
			else
			{
				// cases #3 and #4 - nested arrays of files (name="file[nested]", name="file[nested][]", etc)
				$files[$key] = self::normalizeFile($data, $throwException);
			}
		}
		
		return $files;
	}
	
	private static function makeFile(array $data, bool $throwException)
	{
		$file = new UploadedFile($data);
		
		if ($throwException && !$file->isValid())
		{
			throw new UploadException($file);
		}
		
		return $file;
	}
}
