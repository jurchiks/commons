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
	
	function jsonSerialize()
	{
		return $this->getAll();
	}
}
