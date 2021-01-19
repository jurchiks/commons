<?php
namespace js\tools\commons\http;

use js\tools\commons\exceptions\HttpException;
use js\tools\commons\upload\UploadedFileCollection;

class Request
{
	const METHODS = ['get', 'post', 'put', 'patch', 'delete', 'head', 'options', 'trace'];
	
	private $method;
	private $uri;
	private $data;
	private $files;
	private $referer;
	
	/**
	 * @param string $method : the request method used for this request (e.g. GET, POST)
	 * @param Uri $uri : the URI that was requested
	 * @param array $data : the request data, if any (GET, POST, PUT, PATCH, DELETE etc). In the case of a GET request,
	 * the same data is available via the $uri object
	 * @param array $files : the $_FILES array or its equivalent
	 * @param string $referer : the URL that referred to this URL
	 * @throws HttpException if the request method is invalid
	 */
	public function __construct(string $method, Uri $uri, array $data, array $files = [], string $referer = '')
	{
		if (!in_array($method, self::METHODS))
		{
			throw new HttpException('Unsupported request method "' . $method . '"');
		}
		
		$this->method = $method;
		$this->uri = $uri;
		$this->data = new Parameters($data);
		$this->files = new UploadedFileCollection($files);
		$this->referer = $referer;
	}
	
	public static function createFromGlobals()
	{
		if (!isset($_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']))
		{
			throw new HttpException('Missing required fields in global $_SERVER - [REQUEST_METHOD, HTTP_HOST, REQUEST_URI]');
		}
		
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		
		if (!in_array($method, self::METHODS))
		{
			throw new HttpException('Unsupported request method "' . $method . '"');
		}
		
		if ($method === 'get')
		{
			$data = $_GET;
		}
		else if ($method === 'post')
		{
			$data = $_POST;
		}
		else
		{
			// PHP does not automatically populate $_PUT and $_DELETE variables
			$body = static::getRequestBody();
			
			// HTTP_CONTENT_TYPE - PHP built-in server; CONTENT_TYPE - everything else
			$contentType = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? '';
			$contentType = strtolower($contentType);
			
			if (strpos($contentType, 'application/json') !== false)
			{
				$data = json_decode($body, true);
			}
			else
			{
				parse_str($body, $data);
			}
		}
		
		return new static(
			$method,
			Uri::createFromGlobals(),
			$data,
			$_FILES ?? [],
			$_SERVER['HTTP_REFERER'] ?? ''
		);
	}
	
	/**
	 * Get the request method.
	 *
	 * @return string one of [get, post, put, delete, head, options]
	 */
	public function getMethod()
	{
		return $this->method;
	}
	
	/**
	 * Compare the request method.
	 *
	 * @param string $method one of [get, post, put, delete, head, options]
	 * @return bool true if the method matches, false otherwise
	 */
	public function isMethod(string $method): bool
	{
		return (strcasecmp($this->method, $method) === 0);
	}
	
	public function getUri(): Uri
	{
		return $this->uri;
	}
	
	public function isSecure(): bool
	{
		static $secureProtocols = ['https', 'ftps', 'sftp'];
		
		$scheme = strtolower($this->uri->getScheme());
		
		return in_array($scheme, $secureProtocols);
	}
	
	/**
	 * Retrieve request data.
	 */
	public function getData(): Parameters
	{
		return $this->data;
	}
	
	/**
	 * Retrieve uploaded files.
	 */
	public function getFiles(): UploadedFileCollection
	{
		return $this->files;
	}
	
	public function getReferer(): string
	{
		return $this->referer;
	}
	
	protected static function getRequestBody(): string
	{
		return file_get_contents('php://input');
	}
}
