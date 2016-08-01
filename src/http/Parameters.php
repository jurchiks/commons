<?php
namespace js\tools\commons\http;

use js\tools\commons\traits\DataWriter;

class Parameters
{
	use DataWriter;
	
	public function __construct(array $data)
	{
		$this->init($data);
	}
}
