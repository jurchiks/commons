<?php
namespace js\tools\commons\http;

use js\tools\commons\exceptions\HttpException;
use js\tools\commons\exceptions\UriException;

class Url
{
	const SUPPORTED_SCHEMES = ['http', 'https', 'ftp', 'ftps', 'sftp'];
	
	private $scheme;
	private $username;
	private $password;
	private $host;
	private $port;
	private $path;
	private $parameters;
	private $fragment;
	private $sourceChanged = false;
	
	/**
	 * @param string $url : a complete or partial URL
	 * @throws UriException
	 */
	public function __construct(string $url)
	{
		$parts = parse_url($url);
		
		if ($parts === false)
		{
			throw new UriException('Invalid URL "' . $url . '"');
		}
		
		if (isset($parts['scheme']))
		{
			self::validateScheme($parts['scheme']);
		}
		
		$this->scheme = $parts['scheme'] ?? '';
		$this->username = $parts['user'] ?? '';
		$this->password = $parts['pass'] ?? '';
		$this->host = $parts['host'] ?? '';
		$this->port = $parts['port'] ?? null;
		$this->path = $parts['path'] ?? '/';
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
	
	public static function createFromGlobals()
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
	
	public function copy()
	{
		return clone $this;
	}
	
	public function getScheme(): string
	{
		return $this->scheme;
	}
	
	public function setScheme(string $scheme): self
	{
		$scheme = strtolower(trim($scheme));
		
		if ($scheme !== '')
		{
			// empty scheme is allowed for "//domain.tld" URLs where protocol is taken from referer
			self::validateScheme($scheme);
		}
		
		if ($this->scheme !== $scheme)
		{
			$this->scheme = $scheme;
			$this->sourceChanged = true;
		}
		
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
	
	public function setAuth(string $username, string $password = ''): self
	{
		self::validateAuth($username, $password);
		
		if (($this->username !== $username) || ($this->password !== $password))
		{
			$this->username = $username;
			$this->password = $password;
			$this->sourceChanged = true;
		}
		
		return $this;
	}
	
	public function getHost(): string
	{
		return $this->host;
	}
	
	public function setHost(string $hostOrIp): self
	{
		$hostOrIp = strtolower(trim($hostOrIp, '/'));
		
		self::validateHost($hostOrIp);
		
		if ($this->host !== $hostOrIp)
		{
			$this->host = $hostOrIp;
			$this->sourceChanged = true;
		}
		
		return $this;
	}
	
	/**
	 * @return int|null the port number, null if there is no explicit port specified
	 */
	public function getPort(): ?int
	{
		return $this->port;
	}
	
	public function setPort(?int $port): self
	{
		if (!is_null($port) && (($port < 0) || ($port > 65535)))
		{
			throw new UriException('Invalid port number "' . $port . '"');
		}
		
		if ($this->port !== $port)
		{
			$this->port = $port;
			$this->sourceChanged = true;
		}
		
		return $this;
	}
	
	public function getPath(): string
	{
		return $this->path;
	}
	
	public function setPath(string $path): self
	{
		$path = '/' . trim($path, '/');
		
		self::validatePath($path);
		
		$this->path = $path;
		
		return $this;
	}
	
	/**
	 * @param bool $isRawUrl : if true, spaces in query parameters are encoded as %20, otherwise as +
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
	
	public function setQueryParameters(array $parameters): self
	{
		foreach ($parameters as $key => $value)
		{
			self::validateQueryParameter($key, $value);
		}
		
		$this->parameters = new Parameters($parameters);
		
		return $this;
	}
	
	public function setQueryParameter(string $key, $value): self
	{
		self::validateQueryParameter($key, $value);
		
		$this->parameters->set($key, $value);
		
		return $this;
	}
	
	public function getFragment(): string
	{
		return $this->fragment;
	}
	
	public function setFragment(string $fragment): self
	{
		$this->fragment = $fragment;
		
		return $this;
	}
	
	public function isAbsolute(): bool
	{
		return !empty($this->getHost());
	}
	
	/**
	 * @param bool $isRawUrl : if true, spaces in query parameters are encoded as %20, otherwise as +
	 * @return string the relative part of the URL, e.g. "/foo/bar?baz=random#hash"
	 */
	public function getRelative(bool $isRawUrl = false): string
	{
		$fragment = ($this->getFragment() ? '#' . $this->getFragment() : '');
		
		return $this->getPath() . $this->getQuery($isRawUrl) . $fragment;
	}
	
	/**
	 * @param bool $isRawUrl : if true, spaces in query parameters are encoded as %20, otherwise as +
	 * @return string the full URL with all the specified data included
	 * @throws UriException if host is missing
	 */
	public function getAbsolute(bool $isRawUrl = false): string
	{
		if (!$this->isAbsolute())
		{
			throw new UriException('Cannot make an absolute URL without host');
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
	 * Get the URL contained in this object. May return a relative or absolute URL depending on
	 * whether the absolute part has changed.
	 *
	 * @param bool $isRawUrl : if true, spaces in query parameters are encoded as %20, otherwise as +
	 * @return string the link to the required route
	 * @see getRelative
	 * @see getAbsolute
	 */
	public function get(bool $isRawUrl = false): string
	{
		if ($this->sourceChanged)
		{
			return $this->getAbsolute($isRawUrl);
		}
		
		return $this->getRelative($isRawUrl);
	}
	
	public function __toString()
	{
		return $this->get();
	}
	
	private static function validateScheme(string $scheme)
	{
		if (!in_array($scheme, self::SUPPORTED_SCHEMES))
		{
			throw new UriException('Unsupported URI scheme "' . $scheme . '"');
		}
	}
	
	private static function validateAuth(string $username, string $password)
	{
		if (!empty($username))
		{
			$tmp = 'http://' . $username . (empty($password) ? '' : ':' . $password) . '@domain.tld';
			$data = parse_url($tmp);
			
			if (($data === false)
				|| !isset($data['user'])
				|| ($data['user'] !== $username)
				|| (isset($data['pass']) && ($data['pass'] !== $password))
			)
			{
				throw new UriException('Invalid auth credentials');
			}
		}
		else if (!empty($password))
		{
			throw new UriException('Cannot have a password without a username');
		}
	}
	
	private static function validateHost(string $hostOrIp)
	{
		if ((filter_var($hostOrIp, FILTER_VALIDATE_IP) === false)
			&& ((strpos($hostOrIp, '/') !== false) // host must only be in the format "domain.tld" or a valid IP
				|| (filter_var('http://' . $hostOrIp, FILTER_VALIDATE_URL) === false)) // requires a protocol
		)
		{
			// not a valid domain nor an IP address
			throw new UriException('Invalid host "' . $hostOrIp . '"');
		}
	}
	
	private static function validatePath(string $path)
	{
		$data = parse_url('http://domain.tld' . $path);
		
		if (($data === false) || !isset($data['path']) || ($data['path'] !== $path))
		{
			throw new UriException('Invalid path "' . $path . '"');
		}
	}
	
	private static function validateQuery(string $query)
	{
		$data = parse_url('http://domain.tld?' . $query);
		
		if (($data === false) || !isset($data['query']) || ($data['query'] !== $query))
		{
			throw new UriException('Invalid query "' . $query . '"');
		}
	}
	
	private static function validateQueryParameter(string $key, $value)
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
			throw new UriException('Invalid query parameter "' . gettype($value) . '" for key "' . $key . '"');
		}
	}
}
