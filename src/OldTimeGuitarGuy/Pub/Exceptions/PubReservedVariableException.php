<?php

namespace OldTimeGuitarGuy\Pub\Exceptions;

class PubReservedVariableException extends \Exception
{
	public function __construct($variableName)
	{
		parent::__construct($variableName . ' is a reserved variable.');
	}
}
