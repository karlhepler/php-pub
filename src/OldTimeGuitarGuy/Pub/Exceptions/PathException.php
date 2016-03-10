<?php

namespace OldTimeGuitarGuy\Pub\Exceptions;

class PathException extends \Exception
{
	public function __construct($pathName)
	{
		parent::__construct($pathName . ' is an undefined path.');
	}
}
