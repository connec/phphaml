<?php

/**
 * string_stream.php
 */

namespace phphaml;

/**
 * The StringStream class allows strings to be treated as files for the purposes
 * of 'include' etc.  This avoids needing to use eval().
 */

class StringStream {
	
	/**
	 * An array of registered strings.
	 * 
	 * To save memory it is recommended to clear() set strings after use.
	 */
	protected static $strings = array();
	
	/**
	 * The string this instance is dealing with.
	 */
	protected $string;
	
	/**
	 * The current position in the string.
	 */
	protected $position;
	
	/**
	 * Sets a string.
	 */
	public static function set($key, $string) {
		
		static::$strings[$key] = $string;
		
	}
	
	/**
	 * Binds a string, useful for writing.
	 */
	public static function bind($key, &$string) {
		
		static::$strings[$key] =& $string;
		
	}
	
	/**
	 * Gets the value of a string, useful for writing.
	 */
	public static function get($key) {
		
		return static::$strings[$key];
		
	}
	
	/**
	 * Clears a string (unset).
	 */
	public static function clear($key) {
		
		unset(static::$strings[$key]);
		
	}
	
	/**
	 * Open a stream.
	 */
	public function stream_open($path, $mode, $options, &$opened_path) {
		
		$path = explode('://', $path);
		$this->string = end($path);
		$this->position = 0;
		return true;
		
	}
	
	/**
	 * Read from a stream.
	 */
	public function stream_read($count) {
		
		$return = substr(static::$strings[$this->string], $this->position, $count);
		$this->position += strlen($return);
		return $return;
		
	}
	
	/**
	 * Write to a stream.
	 */
	public function stream_write($data) {
		
		$before = substr(static::$strings[$this->string], 0, $this->position);
		$after = substr(static::$strings[$this->string], $this->position + strlen($data));
		static::$strings[$this->string] = $before . $data . $after;
		$this->position += strlen($data);
		return strlen($data);
		
	}
	
	/**
	 * Get the stream's position.
	 */
	public function stream_tell() {
		
		return $this->position;
		
	}
	
	/**
	 * Check if the stream is at the end.
	 */
	public function stream_eof() {
		
		return $this->position >= strlen(static::$strings[$this->string]);
		
	}
	
	/**
	 * Seek to a position in the stream.
	 */
	public function stream_seek($offset, $flag) {
		
		switch($flag) {
			case SEEK_SET:
				if($offset < strlen(static::$strings[$this->string]) and $offset >= 0) {
					$this->position = $offset;
					return true;
				} else {
					return false;
				}
			break;
			case SEEK_CUR:
				if($offset >= 0) {
					$this->position += $offset;
					return true;
				} else {
					return false;
				}
			break;
			case SEEK_END:
				if(strlen(static::$strings[$this->string]) + $offset >= 0) {
					$this->position = strlen(static::$strings[$this->string]) + $offset;
					return true;
				} else {
					return false;
				}
			break;
			default:
				return false;
			break;
		}
		
	}
	
	/**
	 * Stops 'include' complaining.
	 */
	public function stream_stat() {}
	
}

stream_wrapper_register('string', '\\' . __NAMESPACE__ . '\StringStream');

?>