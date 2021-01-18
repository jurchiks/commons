<?php
namespace js\tools\commons\templating;

use js\tools\commons\exceptions\TemplateException;
use js\tools\commons\traits\DataAccessor;
use Throwable;

class Template
{
	use DataAccessor;
	
	private $path;
	private $engine;
	/** @var Template */
	private $parent = null;
	private $blocks = [];
	private $lastBlock = false;
	private $content = '';
	
	public function __construct(string $path, array $data = [], Engine $engine = null)
	{
		if (substr($path, -6) !== '.phtml')
		{
			$path .= '.phtml';
		}
		
		if (!is_readable($path))
		{
			throw new TemplateException('Invalid template path ' . $path);
		}
		
		$this->path = $path;
		$this->engine = $engine;
		$this->init($data);
	}
	
	protected function block(string $name)
	{
		return ((isset($this->blocks[$name]) && is_string($this->blocks[$name]))
			? $this->blocks[$name]
			: null);
	}
	
	protected function content()
	{
		return $this->content;
	}
	
	public function __isset(string $name)
	{
		return $this->exists($name);
	}
	
	public function __get(string $name)
	{
		if (!$this->exists($name))
		{
			throw new TemplateException('Trying to access undefined variable "' . $name . '"');
		}
		
		return $this->get($name);
	}
	
	public function __set(string $name, $value)
	{
		throw new TemplateException('Template values are read-only');
	}
	
	public function __call(string $name, array $arguments)
	{
		if ($this->engine === null)
		{
			throw new TemplateException('Callbacks are only available with Engine');
		}
		
		return $this->engine->callFunction($name, $arguments);
	}
	
	protected function start(string $name)
	{
		if ($this->lastBlock !== false)
		{
			ob_end_clean(); // Clean after previous block.
			throw new TemplateException('Nested blocks are not allowed');
		}
		
		if (ob_start())
		{
			$this->lastBlock = $name;
		}
		else
		{
			throw new TemplateException('Failed to start output buffering');
		}
	}
	
	protected function end()
	{
		if ($this->lastBlock !== false)
		{
			$this->blocks[$this->lastBlock] = ob_get_clean();
			$this->lastBlock = false;
		}
	}
	
	protected function parent(string $path, array $data = [])
	{
		// construct the parent immediately to fail-fast in case the $path is invalid
		$this->parent = $this->getTemplate($path, $data);
	}
	
	protected function include(string $path, array $data = [])
	{
		echo $this->getTemplate($path, $data)->render();
	}
	
	public function render()
	{
		ob_start();
		
		try
		{
			include $this->path;
			
			if ($this->lastBlock !== false)
			{
				ob_end_clean(); // Clean up after previous block.
				throw new TemplateException('Unclosed block "' . $this->lastBlock . '"');
			}
			
			$content = ob_get_clean();
		}
		catch (Throwable $t)
		{
			ob_end_clean();
			throw new TemplateException(
				get_class($t) . ' thrown while rendering template "' . $this->path . '": ' . $t->getMessage(), 0, $t
			);
		}
		
		if ($this->parent instanceof Template)
		{
			$parent = $this->parent;
			$parent->blocks = $this->blocks;
			$parent->content = $content;
			
			$content = $parent->render();
		}
		
		return $content;
	}
	
	public function __toString(): string
	{
		return $this->render();
	}
	
	private function getTemplate(string $path, array $data = [])
	{
		if ($this->engine === null)
		{
			return new static($path, $data);
		}
		else
		{
			return $this->engine->getTemplate($path, $data);
		}
	}
}
