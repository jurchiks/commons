<?php
namespace js\tools\commons\collections;

use RuntimeException;

final class None extends Option
{
	/**
	 * @return void
	 */
	public function get()
	{
		throw new RuntimeException('None does not have a value; consider using getOrElse()');
	}
}
