<?php
namespace js\tools\commons\templating;

use js\tools\commons\exceptions\TemplateException;

class Engine
{
	private $templateRoot;
	
	public function __construct(string $templateRoot)
	{
		$this->templateRoot = rtrim($templateRoot, '\\/') . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * @param string $path : a path to the template file, relative to the template root directory
	 * @param array $data : optional data to provide to the template
	 * @return Template
	 * @throws TemplateException
	 */
	public function getTemplate(string $path, array $data = [])
	{
		return new Template($this->templateRoot . $path, $data, $this);
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
