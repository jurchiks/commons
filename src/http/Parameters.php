<?php
namespace js\tools\commons\http;

use js\tools\commons\traits\DataWriter;
use JsonSerializable;

class Parameters implements JsonSerializable
{
	use DataWriter;
	
	public function __construct(array $data)
	{
		$this->init($data);
	}
	
	public function copy(): self
	{
		return clone $this;
	}
	
	function jsonSerialize(): array
	{
		return $this->getAll();
	}
}
