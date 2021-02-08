<?php
namespace js\tools\commons\http;

use js\tools\commons\exceptions\HttpException;
use js\tools\commons\exceptions\upload\UploadException;
use js\tools\commons\exceptions\UrlException;
use js\tools\commons\upload\UploadedFileCollection;
use JsonException;

class Request
{
	const METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS', 'TRACE'];
	private string $method;
	private Url $url;
	private Parameters $data;
	private UploadedFileCollection $files;
	private string $referer;
	
	/**
	 * @param string $method The request method used for this request (e.g. GET, POST).
	 * @param Url $url The URL that was requested.
	 * @param array $data The request body data, if any (POST, PUT, PATCH, DELETE etc).
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
	 * @return self
	 * @throws HttpException If the required data is missing from the globals, or the request method is unsupported.
	 * @throws UploadException If the uploaded files contain errors.
	 * @throws UrlException If the URL comprised from the globals is invalid.
	 * @throws JsonException If the Content-Type is JSON but the body could not be parsed as such.
	 */
	public static function createFromGlobals(): self
	{
		if (!isset($_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']))
		{
			throw new HttpException(
				'Missing required fields in global $_SERVER - [REQUEST_METHOD, HTTP_HOST, REQUEST_URI]'
			);
		}
		
		$method = strtoupper($_SERVER['REQUEST_METHOD']);
		
		if (!in_array($method, self::METHODS))
		{
			throw new HttpException('Unsupported request method "' . $_SERVER['REQUEST_METHOD'] . '"');
		}
		
		$data = [];
		
		if ($method !== 'GET')
		{
			$body = static::getRequestBody();
			$contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
			
			if (strpos($contentType, 'application/x-www-form-urlencoded') !== false)
			{
				parse_str($body, $data);
			}
			else if (strpos($contentType, 'application/json') !== false)
			{
				$data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
			}
		}
		
		return new self(
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
