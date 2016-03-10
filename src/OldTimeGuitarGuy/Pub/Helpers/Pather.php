<?php

namespace OldTimeGuitarGuy\Pub\Helpers;

class Pather
{
    /**
	 * Normalize a path
	 * Make sure it starts with a slash
	 * and doesn't end with a slash
     *
     * @param string $path
	 * @return string
     */
    public static function normalize($path)
    {
		return '/' . trim($path, '/');
    }

    /**
	 * Finalize a path
	 * Make sure it doesn't end with a slash
     *
     * @param string $path
	 * @return string
     */
    public static function finalize($path)
    {
		return rtrim($path, '/');
    }

    /**
     * Convert a string to camel case
     *
     * @param string $string
	 * @return string
     */
    public static function camelCase($string)
    {
		// Get all words & digits from the string
		$hasMatches = preg_match_all('/[A-Za-z0-9]+/', $string, $matches);

		// Return early if there are no matches or there's an error
		if ( $hasMatches === 0 || $hasMatches === false ) return '';

		// Start an empty output string
		$output = '';

		// Go through each match and append
		// to the output string
		foreach ( $matches[0] as $match ) {
			$output .= ucfirst(strtolower($match));
		}

		// Return the output string
		// with the first char lowercase
		return lcfirst($output);
    }

	/**
	 * Get the basename of a string
	 * separated by anything that isn't
	 * a word or a digit
	 *
	 * @param string $string
	 * @return string
	 */
	public static function base($string)
	{
		// Get all words and digits from the string
		$hasMatches = preg_match_all('/[A-Za-z0-9]+/', $string, $matches);

		// Return early if there are no matches or there was an error
		if ( $hasMatches === 0 || $hasMatches = false ) return '';

		// Return the last match
		return end($matches[0]);
	}

	/**
	 * Force all instance calls
	 * to resolve as static
	 *
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call($name, array $args)
	{
		return call_user_func_array(get_class($this)."::{$name}", $args);
	}
}
