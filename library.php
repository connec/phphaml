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
	 * The root directory of this library.
	 */
	protected static $dir;
	
	/**
	 * An array of cached class information.
	 */
	protected static $classes;
	
	/**
	 * Extracts only the namespace from a namespaced class.
	 */
	public static function namespace_from_class($class) {
		
		$namespace = substr($class, 0, strrpos($class, '\\'));
		if($namespace)
		  return $namespace[0] == '\\' ? $namespace : '\\' . $namespace;
	  else
	    return '\\';
		
	}
	
	/**
	 * Extracts only the class name from a namespaced class.
	 */
	public static function class_name_from_class($class) {
		
		return substr($class, strrpos($class, '\\') + 1);
		
	}
	
	/**
	 * Converts a file name into the class name expected to reside in the file.
	 */
	public static function class_name_from_file_name($file_name) {
		
		$file_name = substr($file_name, 0, strpos($file_name, '.'));
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $file_name)));
		
	}
	
	/**
	 * Converts a namespace into the directory classes of that namespace are expected to be in.
	 */
	public static function directory_from_namespace($namespace) {
		
		if($namespace[0] == '\\')
			$namespace = substr($namespace, 1);
			
		$sub_namespace = str_replace(__NAMESPACE__, '', $namespace);
		
		return
			  static::$dir
			. str_replace('\\', DIRECTORY_SEPARATOR, $sub_namespace)
			. DIRECTORY_SEPARATOR;
		
	}
	
	/**
	 * Converts a namespaced class name into the directory it's expected to be in.
	 */
	public static function directory_from_class($class) {
		
		return static::directory_from_namespace(static::namespace_from_class($class));
		
	}
	
	/**
	 * Converts a namespaced class name into the file name it's expected to be in.
	 */
	public static function file_name_from_class($class) {
		
		return
			  strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', static::class_name_from_class($class)))
			. '.php';
		
	}
	
	/**
	 * Gets information about a class.
	 */
	public static function get_class_info($class) {
		
		if(!isset(static::$classes[$class])) {
			static::$classes[$class] = array(
				'class'      => $class,
				'namespace'  => static::namespace_from_class($class),
				'class_name' => static::class_name_from_class($class),
				'directory'  => static::directory_from_class($class),
				'file_name'  => static::file_name_from_class($class)
			);
		}
		
		return static::$classes[$class];
		
	}
	
	/**
	 * Handles autoloading classes in the library.
	 */
	public static function autoload($class = null) {
		
		if(!$class)
			return spl_autoload_register(array(get_class(), 'autoload'));
		
		if(!static::$dir)
			static::$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
		
		$class = static::get_class_info($class);
		
		if(!file_exists($class['directory'] . $class['file_name']))
			return false;
		
		require $class['directory'] . $class['file_name'];
		
		if(!class_exists($class['class'],  false))
			return false;
		
		return true;
		
	}
	
}

?>