<?php

/**
 * string_stream.php
 */

namespace phphaml\haml;

/**
 * The StringStream class allows strings to be included like files.
 */
class StringStream {
  
  /**
   * The store of streamable strings.
   */
  protected static $strings = array();
  
  /**
   * The string being streamed.
   */
  protected $string;
  
  /**
   * The current position in the string.
   */
  protected $position;
  
  /**
   * Adds a string to stream.
   */
  public static function add_string($name, $string) {
    
    static::$strings[$name] = $string;
    
  }
  
  /**
   * Removes a string.
   */
  public static function clear($name) {
    
    unset(static::$strings[$name]);
    
  }
  
  /**
   * Opens the stream.
   */
  public function stream_open($url, $mode, $options, &$opened_path) {
    
    $url = parse_url($url);
    $this->string = $url['host'];
    $this->position = 0;
    
    if(isset(static::$strings[$this->string]))
      return true;
    else
      return false;
    
  }
  
  /**
   * Reads from the stream.
   */
  public function stream_read($count) {
    
    $return = substr(static::$strings[$this->string], $this->position, $count);
    $this->position += strlen($return);
    return $return;
    
  }
  
  /**
   * Writes to the stream.
   */
  public function stream_write($data) {
    
    $before = substr(static::$strings[$this->string], 0, $this->position);
    $after = substr(static::$strings[$this->string], $this->position + strlen($data));
    static::$strings[$this->string] = $before . $data . $after;
    $this->position += strlen($data);
    return strlen($data);
    
  }
  
  /**
   * Gets the position in the stream.
   */
  public function stream_tell() {
    
    return $this->position;
    
  }
  
  /**
   * Determines if we've reached the end of the stream.
   */
  public function stream_eof() {
    
    return $this->position >= strlen(static::$strings[$this->string]);
    
  }
  
  /**
   * Seeks to a point in the stream.
   */
  public function stream_seek($offset, $type) {
    
    switch($type) {
      case SEEK_SET:
        if($offset < strlen(static::$strings[$this->string]) && $offset >= 0) {
          $this->position = $offset;
          return true;
        } else
          return false;
      break;
      case SEEK_CUR:
        if($offset >= 0) {
          $this->position += $offset;
          return true;
        } else
          return false;
      break;
      case SEEK_END:
        if(strlen(static::$strings[$this->string]) + $offset >= 0) {
          $this->position = strlen(static::$strings[$this->string]) + $offset;
          return true;
        } else
          return false;
      break;
      default:
        return false;
    }
    
  }
  
  /**
   * Should return various stats about a file, for strings... do nothing.
   */
  public function stream_stat() {
    
    return array();
    
  }
  
}

stream_wrapper_register('StringStream', '\phphaml\haml\StringStream');

?>