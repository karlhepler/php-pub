<?php

namespace spec\OldTimeGuitarGuy\Pub\Helpers;

use Prophecy\Argument;
use PhpSpec\ObjectBehavior;
use OldTimeGuitarGuy\Pub\Helpers\Pather;

class PatherSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Pather::class);
    }

	function it_can_normalize_a_path()
	{
		$this->normalize('/something/')->shouldReturn('/something');
		$this->normalize('something')->shouldReturn('/something');
	}

	function it_can_finalize_a_path()
	{
		$this->finalize('/something/')->shouldReturn('/something');
		$this->finalize('something/')->shouldReturn('something');
	}

	function it_can_transform_text_to_camel_case()
	{
		$this->camelCase('This is some tExt')->shouldReturn('thisIsSomeText');
		$this->camelCase('this_is_soMe_text')->shouldReturn('thisIsSomeText');
		$this->camelCase('/tHis|is sOmeText 123')->shouldReturn('thisIsSometext123');
	}
	
	function it_can_return_the_base_of_a_string()
	{
		$this->base('tick/tack/toe')->shouldReturn('toe');
		$this->base('toe')->shouldReturn('toe');
		$this->base('tick|tack|toe')->shouldReturn('toe');
		$this->base('tick\\tack\\toe')->shouldReturn('toe');
	}
}
