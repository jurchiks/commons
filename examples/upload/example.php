<?php
// You can run this example by launching the PHP built-in server from this folder: `php -S localhost:8080`
// and afterwards visiting http://localhost:8080/example.php in your browser.
?>
<form method="post" enctype="multipart/form-data">
	<input type="file" name="upload[nested][name][]" />
	<button type="submit">Upload</button>
</form>
<?php
require __DIR__ . '/../autoloader.php';

use js\tools\commons\upload\exceptions\UploadException;
use js\tools\commons\upload\UploadedFile;
use js\tools\commons\upload\UploadedFileCollection;

if (!empty($_FILES))
{
	try
	{
		$collection = new UploadedFileCollection($_FILES);
		
		if (!$collection->exists('upload.nested.name'))
		{
			echo 'file does not exist<br/>';
			die();
		}
		
		/** @var UploadedFile[] $files */
		$files = $collection->getArray('upload.nested.name');
		
		if ($files[0]->isValid())
		{
			echo 'name=', $files[0]->getName(), '<br/>';
			echo 'size=', $files[0]->getSize(), ' bytes<br/>';
			echo 'MIME=', $files[0]->getMimeType(), '<br/>';
			echo 'error=', $files[0]->getErrorMessage(), '<br/>';
			
			echo 'new file path=', $files[0]->moveTo(__DIR__), '<br/>';
		}
		else
		{
			echo $files[0]->getErrorMessage(), '<br/>';
		}
	}
	catch (UploadException $e)
	{
		echo $e->getMessage(), '<br/>';
		echo $e->getUploadedFile()->getName(), '<br/>';
	}
}
