<?php

namespace OldTimeGuitarGuy\Pub;

use OldTimeGuitarGuy\Pub\Helpers\Pather;
use OldTimeGuitarGuy\Pub\Helpers\Globaler;
use OldTimeGuitarGuy\Pub\Exceptions\PathException;
use OldTimeGuitarGuy\Pub\Exceptions\PubReservedVariableException;

class Pub
{
	/**
	 * The base path.
	 * This could be assumed to be something like "/public"
	 *
	 * @var string
	 */
	protected $base;

	/**
	 * The definable extended paths.
	 * ex: ['css' => '/assets/css']
	 *
	 * @var array
	 */
	protected $paths = [];

	/**
	 * Variables that can be used
	 * when defining the base path.
	 * Referenced like {{variable}}
	 *
	 * @var array
	 */
	protected $variables = [];

	/**
	 * Variables that can be used
	 * just like $variables, BUT
	 * this class defines them and
	 * they cannot be overriden.
	 *
	 * @var array
	 */
	protected $reservedVariables = [];

	/**
	 * Create a new instance of \OldTimeGuitarGuy\Pub\Pub
	 *
	 * Generate path strings from the given base path.
	 * Dynamically define public methods that generate
	 * paths that extend beyond the base path.
	 *
	 * @param array $paths
	 * @param string $base
	 */
	public function __construct(array $paths = [], $base = '', $variables = [])
	{
		$this->addPath($paths);
		$this->base = Pather::finalize($base);
		$this->defineReservedVariables();
		$this->addVariable($variables);
	}

    /**
	 * Get a path from the base path.
	 * If there are no args, then just
	 * return the base path
     *
     * @param string $path
	 * @return string
     */
    public function path($path = '')
    {
		// If there is an at sign, then don't add base
		if ( strpos($path, '/@') === 0 ) {
			return ltrim($path, '/@');
		}

		return Pather::finalize(
			$this->base . Pather::normalize($path)
		);
    }

	/**
	 * Dynamically add a path method
	 * that will return a path that extends
	 * the base path as defined by the given value
	 *
	 * @param array|string $key
	 * @param string $value
	 * @return void
	 */
	public function addPath($key, $value = '')
	{
		// If $key is not an array, then merge it in
		// It must have a camelCase key & must be normalized
		if ( !is_array($key) ) {
			$this->paths += [Pather::camelCase($key) => Pather::normalize($value)];
			return;
		}

		// We're dealing with an array, so recurse
		foreach ( $key as $_key => $_value ) {
			$this->addPath($_key, $_value);
		}
	}

	/**
	 * Add a variable that can be used in base
	 *
	 * @param  array|string $key
	 * @param  string $value
	 * @return void
	 */
	public function addVariable($key, $value = '')
	{
		// If $key is not an array, then we can just add it in
		if ( !is_array($key) ) {
			// Convert to lowercase
			$key = strtolower($key);

			// Make sure it's not reserved
			if ( array_key_exists($key, $this->reservedVariables) ) {
				throw new PubReservedVariableException($key);
			}

			// Add it in
			$this->variables += [$key => $value];

			return;
		}

		// It's a array, so recurse
		foreach ( $key as $_key => $_value ) {
			$this->addVariable($_key, $_value);
		}
	}

	/**
	 * Call the dynamic path method
	 * and return the generated path
	 *
	 * @param string $name
	 * @param array $args
	 * @return string
	 * @throws PathException
	 */
	public function __call($name, array $args)
	{
		// Throw exception if the name
		// isn't a defined path
		if ( !isset($this->paths[$name]) ) {
			throw new PathException($name);
		}

		// Grab the path if there is one
		$path = isset($args[0]) ? $args[0] : '';

		// Build the path and return it
		return $this->build($this->paths[$name], $path);
	}

	/**
	 * Define the reserved variables.
	 * This is called from the constructor.
	 *
	 * @return void
	 */
	protected function defineReservedVariables()
	{
		$this->reservedVariables = [
			// Just return the current timestamp
			'timestamp' => time(),

			// This will be added on the fly
			'path' => null
		];
	}

	/**
	 * Build a path that extends
	 * from the base path with an additional
	 * base and an ending path
	 *
	 * @param string $base
	 * @param string $path
	 * @return string
	 */
	protected function build($base, $path)
	{
		return $this->path(
			$this->concatWithVariables($base, $path)
		);
	}

	/**
	 * Concatinate the base & path
	 * while injecting variables
	 *
	 * @param string $base
	 * @param string $path
	 * @return string
	 */
	protected function concatWithVariables($base, $path)
	{
		// Parse the variables in base
		$base = $this->parseVariables($base);

		// Parse the path variable
		$baseWithPath = $this->parsePathVariable($base, $path);

		// If they're the same, then concat normally
		if ( $base === $baseWithPath ) {
			return $base . Pather::normalize($path);
		}

		// Otherwise, return the base with the path
		return $baseWithPath;
	}

	/**
	 * Replace the path variable with the given path
	 *
	 * @param string $base
	 * @param string $path
	 */
	protected function parsePathVariable($base, $path)
	{
		return preg_replace("/({{\s*path\s*}})/i", $path, $base);
	}

	/**
	 * Parse variables in the string,
	 * designated by double mustaches
	 *
	 * @param string $string
	 * @return string
	 */
	protected function parseVariables($string)
	{
		// Parse reserved variables first, ignoring path
		foreach ( $this->reservedVariables as $key => $value ) {
			if ( $key === 'path' ) continue;
			$string = preg_replace("/({{\s*{$key}\s*}})/i", $value, $string);
		}

		// Now parse regular variables
		foreach ( $this->variables as $key => $value ) {
			$string = preg_replace("/({{\s*{$key}\s*}})/i", $value, $string);
		}

		// Return the string
		return $string;
	}

	/**
	 * Create global pub functions.
	 * This is the main entry point
	 * for applications wanting to use Pub
	 *
	 * Example:
	 *
	 * $base = '/public';
	 * $functionPaths = [
	 *	'fnName1' => 'foo/bar/path',
	 *	'fnName2' => 'foo/path',
	 *	...
	 * ];
	 *
	 * After create is called, you can now call the GLOBAL functions.
	 *
	 * ex: fnName1('test.txt') => '/public/foo/bar/path/test.txt'
	 *
	 * @param array $functionPaths
	 * @param string $base
	 * @return \OldTimeGuitarGuy\Pub\Pub
	 */
	public static function globalize(array $functionPaths = [], $base = '')
	{
		// Captcher a new instance of Pub
		$pub = Globaler::capture(new static($functionPaths, $base), 'path');

		// Create global functions
		// for each function name in the functionPaths array
		foreach ( $functionPaths as $functionName => $path ) {
			Globaler::addFunction($functionName, static::class, $functionName);
		}

		// Return Globaler::instances()[static::class]
		return $pub;
	}
}
