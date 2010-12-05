<?php

/**
 * lib.php
 */

namespace haml;

/**
 * The haml\Haml class is the include point for the library.
 */

class Library {
	
	protected static $dir;
	
	/**
	 * Initialises the library.
	 */
	public static function init() {
		
		if(static::$dir)
			return;
		
		static::$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
		
	}
	
	public static function autoload($class = null) {
		
		static::init();
		
		if(!$class)
			return spl_autoload_register(array(get_class(), 'autoload'));
		
		// Extract the namespace tree.
		if($class[0] == '\\')
			$class = substr($class, 1);
		$parts = explode('\\', $class);
		$class = array_pop($parts);
		$namespace = '\\' . implode('\\', $parts);
		
		array_shift($parts);
		$dir = static::$dir . implode(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR;
		$file = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $class)) . '.php';
		
		if(!file_exists($dir . $file))
			return false;
		
		require $dir . $file;
		
		if(!class_exists($namespace . '\\' . $class,  false))
			return false;
		
		return true;
		
	}
	
}

?>