<?php
namespace js\tools\commons\http;

use js\tools\commons\exceptions\HttpException;
use js\tools\commons\exceptions\UrlException;

class Url
{
	private string $scheme;
	private string $username;
	private string $password;
	private string $host;
	private ?int $port;
	private string $path;
	private Parameters $parameters;
	private string $fragment;
	
	/**
	 * @param string $url A complete or partial URL.
	 * @throws UrlException If the URL is invalid.
	 */
	public function __construct(string $url)
	{
		$parts = parse_url($url);
		
		if ($parts === false)
		{
			throw new UrlException('Invalid URL "' . $url . '"');
		}
		
		$this->scheme = $parts['scheme'] ?? '';
		$this->username = $parts['user'] ?? '';
		$this->password = $parts['pass'] ?? '';
		$this->host = $parts['host'] ?? '';
		$this->port = $parts['port'] ?? null;
		$this->path = $parts['path'] ?? '';
		$this->fragment = $parts['fragment'] ?? '';
		
		if (isset($parts['query']))
		{
			// note: parse_str() converts dots and spaces in parameter names into underscores,
			// i.e. "?foo.ba r=baz" will result in ['foo_ba_r' => 'baz']
			parse_str($parts['query'], $parameters);
		}
		else
		{
			$parameters = [];
		}
		
		$this->parameters = new Parameters($parameters);
	}
	
	/**
	 * @return self
	 * @throws HttpException If the globals are missing some required fields.
	 * @throws UrlException If the URL comprised from the globals is invalid.
	 */
	public static function createFromGlobals(): self
	{
		if (!isset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']))
		{
			throw new HttpException('Missing required fields in global $_SERVER - [HTTP_HOST, REQUEST_URI]');
		}
		
		$url = ((!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] !== 'off')) ? 'https' : 'http');
		$url .= '://';
		$url .= $_SERVER['HTTP_HOST'];
		$url .= $_SERVER['REQUEST_URI'];
		
		return new self($url);
	}
	
	public function copy(): self
	{
		return clone $this;
	}
	
	public function getScheme(): string
	{
		return $this->scheme;
	}
	
	public function setScheme(string $scheme): self
	{
		$this->scheme = strtolower(trim($scheme));
		
		return $this;
	}
	
	public function getUsername(): string
	{
		return $this->username;
	}
	
	public function getPassword(): string
	{
		return $this->password;
	}
	
	/**
	 * @param string $username
	 * @param string $password
	 * @return $this
	 * @throws UrlException If the credentials or their format is invalid.
	 */
	public function setAuth(string $username, string $password = ''): self
	{
		self::validateAuth($username, $password);
		
		$this->username = $username;
		$this->password = $password;
		
		return $this;
	}
	
	public function getHost(): string
	{
		return $this->host;
	}
	
	/**
	 * @param string $hostOrIp
	 * @return $this
	 * @throws UrlException If the hostname or IP is invalid.
	 */
	public function setHost(string $hostOrIp): self
	{
		$hostOrIp = strtolower(trim($hostOrIp, '/'));
		
		self::validateHost($hostOrIp);
		
		$this->host = $hostOrIp;
		
		return $this;
	}
	
	/**
	 * @return int|null The port number, null if there is no explicit port specified.
	 */
	public function getPort(): ?int
	{
		return $this->port;
	}
	
	/**
	 * @param int|null $port
	 * @return $this
	 * @throws UrlException If the port is out of range.
	 */
	public function setPort(?int $port): self
	{
		if (!is_null($port) && (($port < 0) || ($port > 65535)))
		{
			throw new UrlException('Invalid port number "' . $port . '"');
		}
		
		$this->port = $port;
		
		return $this;
	}
	
	public function getPath(): string
	{
		return $this->path;
	}
	
	/**
	 * @param string $path
	 * @return $this
	 * @throws UrlException If the path is invalid.
	 */
	public function setPath(string $path): self
	{
		$path = trim($path);
		
		if ($path !== '')
		{
			$path = '/' . $path;
			$path = preg_replace('~//+~', '/', $path);
		}
		
		self::validatePath($path);
		
		$this->path = $path;
		
		return $this;
	}
	
	/**
	 * @param bool $isRawUrl If true, spaces in query parameters are encoded as %20, otherwise as +.
	 * @return string
	 */
	public function getQuery(bool $isRawUrl = false): string
	{
		if ($this->parameters->isEmpty())
		{
			return '';
		}
		
		return '?' . http_build_query(
				$this->parameters->getAll(),
				'',
				'&',
				$isRawUrl ? PHP_QUERY_RFC3986 : PHP_QUERY_RFC1738
			);
	}
	
	/**
	 * @param string $query
	 * @return $this
	 * @throws UrlException If the query is invalid.
	 */
	public function setQuery(string $query): self
	{
		$query = ltrim($query, '?');
		
		if (empty($query))
		{
			$parameters = [];
		}
		else
		{
			self::validateQuery($query);
			
			parse_str($query, $parameters);
		}
		
		$this->parameters = new Parameters($parameters);
		
		return $this;
	}
	
	public function getQueryParameters(): Parameters
	{
		return $this->parameters;
	}
	
	/**
	 * @param array<int|string>|int|string $key Exact key OR array of nested keys OR dot-separated string key.
	 * Examples:
	 * <ul>
	 * <li>getQueryParameter('foo')</li>
	 * <li>getQueryParameter(['foo', 0])</li>
	 * <li>getQueryParameter('foo.bar', 'not found')</li>
	 * </ul>
	 * @param null|int|string|array $default
	 * @return int|string|array The found value or $default.
	 */
	public function getQueryParameter($key, $default = null)
	{
		return $this->parameters->get($key, $default);
	}
	
	/**
	 * Replace query parameters with new ones.
	 *
	 * @param array $parameters The query parameters to replace the existing parameters with.
	 * @return $this
	 * @throws UrlException If any query parameter has an invalid value.
	 */
	public function setQueryParameters(array $parameters): self
	{
		foreach ($parameters as $key => $value)
		{
			self::validateQueryParameter($key, $value);
		}
		
		$this->parameters = new Parameters($parameters);
		
		return $this;
	}
	
	/**
	 * @param array<int|string>|int|string $key
	 * @param array<int|string>|int|string $value
	 * @return $this
	 * @throws UrlException If the value is invalid.
	 */
	public function setQueryParameter($key, $value): self
	{
		self::validateQueryParameter($key, $value);
		
		$this->parameters->set($key, $value);
		
		return $this;
	}
	
	public function getFragment(): string
	{
		return (($this->fragment !== '') ? '#' . $this->fragment : '');
	}
	
	public function setFragment(string $fragment): self
	{
		$this->fragment = ltrim($fragment, '#');
		
		return $this;
	}
	
	public function isAbsolute(): bool
	{
		return !empty($this->getHost());
	}
	
	/**
	 * @param bool $isRawUrl if true, spaces in query parameters are encoded as %20, otherwise as +.
	 * @return string The relative part of the URL, e.g. "/foo/bar?baz=random#hash".
	 */
	public function getRelative(bool $isRawUrl = false): string
	{
		return $this->getPath() . $this->getQuery($isRawUrl) . $this->getFragment();
	}
	
	/**
	 * @param bool $isRawUrl If true, spaces in query parameters are encoded as %20, otherwise as +.
	 * @return string The full URL with all the specified data included.
	 * @throws UrlException If host is missing.
	 */
	public function getAbsolute(bool $isRawUrl = false): string
	{
		if (!$this->isAbsolute())
		{
			throw new UrlException('Cannot make an absolute URL without host');
		}
		
		if (empty($this->getScheme()))
		{
			$source = '//';
		}
		else
		{
			$source = $this->getScheme() . '://';
		}
		
		if (!empty($this->getUsername()))
		{
			$source .= $this->getUsername();
			
			if (!empty($this->getPassword()))
			{
				$source .= ':' . $this->getPassword();
			}
			
			$source .= '@';
		}
		
		$source .= $this->getHost();
		
		if ($this->getPort() !== null)
		{
			$source .= ':' . $this->getPort();
		}
		
		return $source . $this->getRelative($isRawUrl);
	}
	
	/**
	 * Get the URL contained in this object.
	 * May return a relative or absolute URL depending on whether the hostname is available.
	 *
	 * @param bool $isRawUrl If true, spaces in query parameters are encoded as %20, otherwise as +.
	 * @return string
	 * @see getAbsolute
	 * @see getRelative
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public function get(bool $isRawUrl = false): string
	{
		return $this->isAbsolute()
			? $this->getAbsolute($isRawUrl)
			: $this->getRelative($isRawUrl);
	}
	
	public function __toString(): string
	{
		return $this->get();
	}
	
	/**
	 * @param string $username
	 * @param string $password
	 * @throws UrlException
	 */
	private static function validateAuth(string $username, string $password)
	{
		if (!empty($username))
		{
			$tmp = 'http://' . $username . (empty($password) ? '' : ':' . $password) . '@domain.tld';
			$data = parse_url($tmp);
			
			if (($data === false)
				|| !isset($data['user'])
				|| ($data['user'] !== $username)
				|| (isset($data['pass']) && ($data['pass'] !== $password)))
			{
				throw new UrlException('Invalid auth credentials');
			}
		}
		else if (!empty($password))
		{
			throw new UrlException('Cannot have a password without a username');
		}
	}
	
	/**
	 * @param string $hostOrIp
	 * @throws UrlException
	 */
	private static function validateHost(string $hostOrIp)
	{
		if ((filter_var($hostOrIp, FILTER_VALIDATE_IP) === false)
			&& ((strpos($hostOrIp, '/') !== false) // host must only be in the format "domain.tld" or a valid IP
				|| (filter_var('http://' . $hostOrIp, FILTER_VALIDATE_URL) === false)) // requires a protocol
		)
		{
			// not a valid domain nor an IP address
			throw new UrlException('Invalid host "' . $hostOrIp . '"');
		}
	}
	
	/**
	 * @param string $path
	 * @throws UrlException
	 */
	private static function validatePath(string $path)
	{
		$data = parse_url('http://domain.tld' . $path);
		
		if (($data === false) || !isset($data['path']) || ($data['path'] !== $path))
		{
			throw new UrlException('Invalid path "' . $path . '"');
		}
	}
	
	/**
	 * @param string $query
	 * @throws UrlException
	 */
	private static function validateQuery(string $query)
	{
		$data = parse_url('http://domain.tld?' . $query);
		
		if (($data === false) || !isset($data['query']) || ($data['query'] !== $query))
		{
			throw new UrlException('Invalid query "' . $query . '"');
		}
	}
	
	/**
	 * @param $key
	 * @param $value
	 * @throws UrlException
	 */
	private static function validateQueryParameter($key, $value)
	{
		if (is_array($value))
		{
			foreach ($value as $k => $v)
			{
				self::validateQueryParameter($k, $v);
			}
		}
		else if (!is_scalar($value))
		{
			throw new UrlException(
				'Invalid query parameter "' . gettype($value) . '" for key "' . implode('.', (array)$key) . '"'
			);
		}
	}
}
