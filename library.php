<?php

/**
 * lib.php
 */

namespace phphaml;

/**
 * The haml\Haml class is the include point for the library.
 */

class Library {
	
	/**
	 * A cache of class info.
	 */
	protected static $class_info = array();
	
	protected static $dir;
	
	/**
	 * Initialises the library.
	 */
	public static function init() {
		
		if(static::$dir)
			return;
		
		static::$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
		
	}
	
	public static function get_class_info($class) {
		
		static::init();
		
		if(isset(static::$class_info[$class]))
			return static::$class_info[$class];
		
		if($class[0] == '\\')
			$class = substr($class, 1);
		$parts = explode('\\', $class);
		$class = array_pop($parts);
		$namespace = '\\' . implode('\\', $parts);
		
		array_shift($parts);
		$dir = static::$dir . implode(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR;
		$file = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $class)) . '.php';
		
		return (static::$class_info[$class] = array($namespace, $class, $dir, $file));
		
	}
	
	public static function autoload($class = null) {
		
		static::init();
		
		if(!$class)
			return spl_autoload_register(array(get_class(), 'autoload'));
		
		list($namespace, $class, $dir, $file) = static::get_class_info($class);
		
		if(!file_exists($dir . $file))
			return false;
		
		require $dir . $file;
		
		if(!class_exists($namespace . '\\' . $class,  false))
			return false;
		
		return true;
		
	}
	
}

?>