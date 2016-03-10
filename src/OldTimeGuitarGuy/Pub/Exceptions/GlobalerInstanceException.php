<?php

namespace OldTimeGuitarGuy\Pub\Exceptions;

class GlobalerInstanceException extends \Exception
{
	public function __construct($className)
	{
		parent::__construct("Globaler does not have an instance of {$className}");
	}
}
