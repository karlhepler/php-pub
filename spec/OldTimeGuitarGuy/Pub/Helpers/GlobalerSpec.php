<?php

namespace spec\OldTimeGuitarGuy\Pub\Helpers;

use Prophecy\Argument;
use PhpSpec\ObjectBehavior;
use OldTimeGuitarGuy\Pub\Pub;
use OldTimeGuitarGuy\Pub\Helpers\Globaler;

class GlobalerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Globaler::class);
    }

	function it_can_capture_an_instance_of_a_class_and_set_a_default_function_name_and_declare_that_function()
	{
		$pub = $this->newPub();

		$this->capture($pub, 'path');

		$this->instances()->shouldHaveKey(get_class($pub));
		
		$this->assertSame('/public/test.txt', pub('test.txt'));
	}

	function it_can_add_functions()
	{
		// Capture a new instance of Pub
		$this->capture($this->newPub());

		// Get a simple reference to it
		$pub = &$this->instances()[Pub::class];

		// Add a path
		$pub->addPath('tpl', 'assets/templates');
	
		// Add a function, referencing Pub's new path
		$this->addFunction('tpl', Pub::class, 'tpl');

		// Make sure it returns what it's supposed to return
		$this->assertSame('/public/assets/templates/test.html', tpl('test.html'));
	}

	/**
	 * Get a new instance of Pub
	 *
	 * @return \OldTimeGuitarGuy\Pub\Pub
	 */
	protected function newPub()
	{
		return new Pub([], '/public');
	}

	protected function assertSame($expected, $actual)
	{
		if ( strcmp($expected, $actual) !== 0 ) {
			throw new \Exception("Same assertion failed. Expected {$expected}, but got {$actual}");
		}

		return true;
	}
}
