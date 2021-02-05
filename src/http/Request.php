<?php
namespace js\tools\commons\http;

use js\tools\commons\exceptions\HttpException;
use js\tools\commons\exceptions\upload\UploadException;
use js\tools\commons\exceptions\UrlException;
use js\tools\commons\upload\UploadedFileCollection;

class Request
{
	const METHODS = ['get', 'post', 'put', 'patch', 'delete', 'head', 'options', 'trace'];
	
	private string $method;
	private Url $url;
	private Parameters $data;
	private UploadedFileCollection $files;
	private string $referer;
	
	/**
	 * @param string $method The request method used for this request (e.g. GET, POST).
	 * @param Url $url The URL that was requested.
	 * @param array $data The request data, if any (GET, POST, PUT, PATCH, DELETE etc).
	 * In the case of a GET request, the same data is available via the $url object.
	 * @param array $files The $_FILES array or its equivalent.
	 * @param string $referer The URL that referred to this URL.
	 * @throws HttpException If the request method is unsupported.
	 * @throws UploadException If the uploaded files contain errors.
	 */
	public function __construct(string $method, Url $url, array $data, array $files = [], string $referer = '')
	{
		if (!in_array($method, self::METHODS))
		{
			throw new HttpException('Unsupported request method "' . $method . '"');
		}
		
		$this->method = $method;
		$this->url = $url;
		$this->data = new Parameters($data);
		$this->files = new UploadedFileCollection($files);
		$this->referer = $referer;
	}
	
	/**
	 * @return Request
	 * @throws HttpException If the required data is missing from the globals, or the request method is unsupported.
	 * @throws UploadException If the uploaded files contain errors.
	 * @throws UrlException If the URL comprised from the globals is invalid.
	 */
	public static function createFromGlobals(): Request
	{
		if (!isset($_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']))
		{
			throw new HttpException(
				'Missing required fields in global $_SERVER - [REQUEST_METHOD, HTTP_HOST, REQUEST_URI]'
			);
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
		
		return new Request(
			$method,
			Url::createFromGlobals(),
			$data,
			$_FILES,
			$_SERVER['HTTP_REFERER'] ?? ''
		);
	}
	
	/**
	 * Get the request method.
	 *
	 * @return string One of the {@link Request::METHODS}.
	 */
	public function getMethod(): string
	{
		return $this->method;
	}
	
	/**
	 * Compare the request method.
	 *
	 * @param string $method One of the {@link Request::METHODS}.
	 * @return bool True if the method matches, false otherwise.
	 */
	public function isMethod(string $method): bool
	{
		return (strcasecmp($this->method, $method) === 0);
	}
	
	public function getUrl(): Url
	{
		return $this->url;
	}
	
	public function isSecure(): bool
	{
		static $secureProtocols = ['https', 'ftps', 'sftp'];
		
		$scheme = strtolower($this->url->getScheme());
		
		return in_array($scheme, $secureProtocols);
	}
	
	/**
	 * Retrieve request data.
	 * For GET requests, this contains query parameters.
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
