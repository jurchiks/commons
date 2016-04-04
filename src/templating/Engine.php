<?php
namespace js\tools\commons\templating;

use js\tools\commons\exceptions\TemplateException;

class Engine
{
	private $templateRoots = [];
	/** @var callable[] */
	private $functions = [];
	/** @var Extension[] */
	private $extensions = [];
	
	public function __construct(string $templateRoot)
	{
		$this->addRoot($templateRoot);
	}
	
	/**
	 * Add another template root. Note that the roots are searched through in the order they were added,
	 * and if a template was found in the first root then the following roots will not be searched.
	 * 
	 * @param string $templateRoot
	 */
	public function addRoot(string $templateRoot)
	{
		$this->templateRoots[] = rtrim($templateRoot, '\\/') . DIRECTORY_SEPARATOR;
	}
	
	public function addExtension(Extension $extension)
	{
		$this->extensions[get_class($extension)] = $extension;
	}
	
	public function addFunction(string $name, callable $function)
	{
		$this->functions[$name] = $function;
	}
	
	public function callFunction(string $name, array $arguments)
	{
		foreach ($this->extensions as $extension)
		{
			if (method_exists($extension, $name))
			{
				return $extension->$name(...$arguments);
			}
		}
		
		if (isset($this->functions[$name]))
		{
			return $this->functions[$name](...$arguments);
		}
		
		throw new TemplateException('Undefined template function "' . $name . '"');
	}
	
	/**
	 * @param string $path : a path to the template file, relative to the template root directory
	 * @param array $data : optional data to provide to the template
	 * @return Template
	 * @throws TemplateException
	 */
	public function getTemplate(string $path, array $data = [])
	{
		if (substr($path, -6) !== '.phtml')
		{
			$path .= '.phtml';
		}
		
		$found = '';
		
		foreach ($this->templateRoots as $root)
		{
			if (is_readable($root . $path))
			{
				$found = $root . $path;
				break;
			}
		}
		
		if ($found === '')
		{
			throw new TemplateException('Invalid template path ' . $path);
		}
		
		return new Template($found, $data, $this);
	}
	
	/**
	 * @param string $path : a path to the template file, relative to the template root directory
	 * @param array $data : optional data to provide to the template
	 * @return string
	 * @throws TemplateException
	 */
	public function render(string $path, array $data = [])
	{
		return $this->getTemplate($path, $data)->render();
	}
}
