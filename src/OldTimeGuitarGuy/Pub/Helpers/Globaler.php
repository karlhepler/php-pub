<?php

namespace OldTimeGuitarGuy\Pub\Helpers;

use OldTimeGuitarGuy\Pub\Helpers\Pather;
use OldTimeGuitarGuy\Pub\Exceptions\GlobalerInstanceException;

class Globaler
{
	/**
	 * This holds all of the instances
	 *
	 * @var array
	 */
	protected static $instances = [];

    /**
	 * Store a class instance in the instances array
	 *
	 * Optionally pass a default method name:
	 * ie: The method name on the instance that you
	 * want to be called when the camel-case representation
	 * of the basename of the class is called.
	 * ex: capture(new MyGreatClass, 'method') => myGreatClass() => (new MyGreatClass)->method()
     *
     * @param mixed $instance
	 * @param mixed $default
	 * @return mixed
     */
    public static function capture($instance, $defaultMethodName = null)
    {
		// Get the class name
		$className = get_class($instance);

		// Throw it in the instances
		static::$instances += [$className => $instance];

		// Set a default method if applicable
		if ( !is_null($defaultMethodName) ) {
			static::addFunction(null, $className, $defaultMethodName);
		}

		// Return a reference to the instance
		return static::$instances[$className];
    }

	/**
	 * Return the Globaler instances
	 *
	 * @return array
	 */
	public static function instances()
	{
		return static::$instances;
	}

	/**
	 * Remove an instance
	 *
	 * @param string $className
	 * @return void
	 */
	public static function removeInstance($className)
	{
		unset(static::$instances[$className]);
	}
	/**
	 * Add a function to global scope
	 *
	 * @param string|null $functionName
	 * @param string $className
	 * @param string $methodName
	 * @param boolean $override
	 * @return void
	 * @throws \OldTimeGuitarGuy\Pub\Exceptions\GlobalerInstanceException
	 */
	public static function addFunction($functionName = null, $className, $methodName, $override = false)
	{
		// First make sure we have an instance of the class
		if ( !isset(static::$instances[$className]) ) {
			throw new GlobalerInstanceException($className);
		}

		// If there is no function name,
		// then we will use the camel-cased version
		// of the class's base name
		if ( is_null($functionName) ) {
			$functionName = Pather::camelCase(Pather::base($className));
		}

		// If the function exists and we're not overriding it, then return 
		if ( function_exists($functionName) && !$override ) return;

		// Create the function!
		${$functionName} = function() use ($className, $methodName) {
			return call_user_func_array(
				[static::$instances[$className], $methodName],
				func_get_args()
			);
		};

		// Store the function in global scope
		$GLOBALS[$functionName] = ${$functionName};

		// Create the actual global function, calling the global scope lambda
		$fn  = 'function '.$functionName.'() { return call_user_func_array($GLOBALS["'.$functionName.'"], func_get_args());}';

		// Evaluate the function delcaration
		eval($fn);
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

