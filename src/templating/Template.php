<?php
namespace js\tools\commons\templating;

use js\tools\commons\exceptions\TemplateException;
use js\tools\commons\traits\DataAccessor;
use Throwable;

class Template
{
	use DataAccessor;
	
	private string $path;
	private ?Engine $engine;
	private ?Template $parent = null;
	private array $blocks = [];
	private ?string $lastBlock = null;
	private string $content = '';
	
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
	
	protected function block(string $name): ?string
	{
		return ((isset($this->blocks[$name]) && is_string($this->blocks[$name]))
			? $this->blocks[$name]
			: null);
	}
	
	protected function content(): string
	{
		return $this->content;
	}
	
	public function __isset(string $name): bool
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
	
	public function __set(string $name, $value): void
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
	
	protected function start(string $name): void
	{
		if ($this->lastBlock !== null)
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
	
	protected function end(): void
	{
		if ($this->lastBlock !== null)
		{
			$this->blocks[$this->lastBlock] = ob_get_clean();
			$this->lastBlock = null;
		}
	}
	
	protected function parent(string $path, array $data = []): void
	{
		// construct the parent immediately to fail-fast in case the $path is invalid
		$this->parent = $this->getTemplate($path, $data);
	}
	
	protected function include(string $path, array $data = []): void
	{
		echo $this->getTemplate($path, $data)->render();
	}
	
	public function render(): string
	{
		ob_start();
		
		try
		{
			include $this->path;
			
			if ($this->lastBlock !== null)
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
	
	private function getTemplate(string $path, array $data = []): self
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
