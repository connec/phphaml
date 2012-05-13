<?php

/**
 * parser.php
 */

namespace phphaml;

/**
 * The Parser class forms the root of a source document, and handles traversing and delegating the
 * source lines.
 */

abstract class Parser {
	
	/**
	 * A flag representing that less indentation is required on the next line.
	 */
	const EXPECT_LESS = 1;

	/**
	 * A flag representing that the same indentation is required on the next line.
	 */
	const EXPECT_SAME = 2;

	/**
	 * A flag representing that more indentation is required on the next line.
	 */
	const EXPECT_MORE = 4;

	/**
	 * A flag representing that any indentation can be given on the next line.
	 */
	const EXPECT_ANY = 7;
	
	/**
	 * An array of line handler classes for this parser.
	 */
	protected static $handlers = array();
	
	/**
	 * An array of options affecting parsing or output generation.
	 */
	protected $options = array();
	
	/**
	 * An array of variable => value pairs to substitute into the render.
	 */
	protected $variables = array();
	
	/**
	 * The source this parser is parsing.
	 */
	protected $source;
	
	/**
	 * The indentation string used in the source being parsed.
	 */
	protected $indent_string;
	
	/**
	 * The root node of the tree.
	 */
	protected $root;
	
	/**
	 * The context node in the tree.
	 * 
	 * This will be the parent of any created nodes.
	 */
	protected $context;
	
	/**
	 * The number of the line being handled.
	 */
	protected $line_number;
	
	/**
	 * The indentation level of the line being handled.
	 */
	protected $indent_level;
	
	/**
	 * The content of the line being handled.
	 */
	protected $content;
	
	/**
	 * A flag indicating what indentation to expect on the next line relative to the current
	 * indentation.
	 */
	protected $expect_indent = self::EXPECT_SAME;
	
	/**
	 * When set, the named handler will be used on the next non-empty source line.
	 */
	protected $force_handler = false;
	
	/**
	 * When !== false, the context will not change.
	 */
	protected $context_locked = false;
	
	/**
	 * Finds handlers in this Parser's directory.
	 */
	protected static function find_handlers() {
		
		$class_info = Library::get_class_info(get_called_class());
		$handler_namespace = $class_info['namespace'] . '\\handlers';
		
		foreach(scandir(Library::directory_from_namespace($handler_namespace)) as $file_name) {
			if($file_name[0] == '.')
				continue;
			
			$handler = $handler_namespace . '\\' . Library::class_name_from_file_name($file_name);
			$trigger = $handler::trigger();
			
			if(!$trigger)
				continue;
			if(is_array($trigger)) {
				foreach($trigger as $_trigger)
					static::$handlers[$_trigger] = $handler;
			} else
				static::$handlers[$trigger] = $handler;
		}
		
		if(empty(static::$handlers))
			throw new Exception('Sanity error: there are no handlers');
		
		uksort(static::$handlers, function($a, $b) {
			if(strlen($a)  < strlen($b)) return  1;
			if(strlen($a) == strlen($b)) return  0;
			if(strlen($a)  > strlen($b)) return -1;
		});
		
	}
	
	/**
	 * Accessor for {$options}.
	 */
	public function options() {
		
		return $this->options;
		
	}
	
	/**
	 * Accessor for individual options.
	 */
	public function option($key) {
		
		if(!isset($this->options[$key]))
			throw new Exception('Sanity error: unknown option - ' . $key);
		
		return $this->options[$key];
		
	}
	
	/**
	 * Accessor for {$variables}.
	 */
	public function variables() {
		
		return $this->variables;
		
	}
	
	/**
	 * Accessor for {$indent_string}.
	 */
	public function indent_string() {
		
		return $this->indent_string;
		
	}
	
	/**
	 * Accessor for {$context}.
	 */
	public function context() {
		
		return $this->context;
		
	}
	
	/**
	 * Accessor for {$line_number}.
	 */
	public function line_number() {
		
		return $this->line_number;
		
	}
	
	/**
	 * Accessor for {$indent_level}.
	 */
	public function indent_level() {
		
		return $this->indent_level;
		
	}
	
	/**
	 * Accessor for {$content}.
	 */
	public function content() {
		
		return $this->content;
		
	}
	
	/**
	 * Sets the indent expectation.
	 */
	public function expect_indent($flag) {
		
		$this->expect_indent = $flag;
		
	}
	
	/**
	 * Forces the next source line to be handled by the given handler.
	 */
	public function force_handler($handler) {
		
		if($handler[0] != '\\')
			$handler = '\\' . $handler;
		
		if(!in_array($handler, static::$handlers))
			$this->exception('Sanity error: cannot force unregistered handler');
		
		$this->force_handler = $handler;
		
	}
	
	/**
	 * Locks the context so that it will not change until the indent level is
	 * next equal to the current indent level.
	 */
	public function lock_context() {
	  
	  if($this->context_locked !== false)
	    $this->exception('Sanity error: cannot lock context - it\'s already locked');

	  $this->context_locked = $this->indent_level;
	  
	}
	
	/**
   * Accessor for {$context_locked}.
	 */
	public function context_locked() {
	  
	  return $this->context_locked;
	  
	}
	
	/**
	 * Initialises the parser with given source and options.
	 */
	public function __construct($source, array $options = array()) {
		
		if(empty(static::$handlers))
			static::find_handlers();
		
		$this->options = array_merge($this->options, $options);
		
		if(!is_file($source))
			$this->source = explode("\n", str_replace(array("\r\n", "\r"), "\n", $source));
		else {
			$sub = array('file' => $source);
			
			if(!file_exists($source))
				throw new Exception('File error: given file does not exist - :file', $sub);
			if(!is_readable($source))
				throw new Exception('File error: given file cannot be read - :file', $sub);
			if(!($this->source = fopen($source, 'r')))
				throw new Exception('File error: given file could not be opened - :file', $sub);
		}
		
	}
	
	/**
	 * Parses the source.
	 * 
	 * Most actual parsing is delegated to discovered handlers.
	 */
	public function parse() {
		
		Handler::set_parser($this);
		
		$node_namespace = Library::namespace_from_class(get_called_class()) . '\\' . 'nodes';
		$root_class = $node_namespace . '\\Node';
		$this->root = new $root_class;
		$this->root->set_from_parser($this);
		
		$this->context = $this->root;
		
		$this->line_number = 0;
		$this->indent_level = 0;
		
		$this->force_handler = false;
		$this->context_locked = false;
		
		while(($this->content = $this->get_line()) !== false) {
			$this->line_number ++;
			
			$this->update_context();
			
			if($this->content = trim($this->content))
				$this->handle();
		}
		
		foreach(static::$handlers as $handler)
			$handler::reset();
		
	}
	
	/**
	 * Renders the parsed tree.
	 */
	abstract public function render();
	
	/**
	 * Gets a line from the source.
	 */
	public function get_line() {
		
		static $get_line;
		
		if(!$get_line) {
			if(is_array($this->source)) {
				$get_line = function(&$source) {
					$line = each($source);
					if(!$line)
						return false;
					else
						return $line[1];
				};
			} elseif(is_resource($this->source)) {
				$get_line = function(&$source) {
					if(feof($source))
						return false;
					else
						return rtrim(fgets($source), "\r\n");
				};
			} else
				throw new Exception('Sanity error: unexpected source type - ' . typeof($this->source));
		}
		
		return $get_line($this->source);
		
	}
	
	/**
	 * Updates the context based on the indent level, and checks the indent level
	 * is as expected.
	 */
	protected function update_context() {

		$re = '/^\s+/';
		preg_match($re, $this->content, $match);
		if($match) {
			if($this->indent_string) {
				$indent_level = substr_count($match[0], $this->indent_string);
			} else {
				$indent_level = 1;
				$this->indent_string = $match[0];
			}

			if(str_replace($this->indent_string, '', $match[0]))
				$this->exception('Parse error: mixed indentation');
		} else {
			$indent_level = 0;
		}
		
		if($this->context_locked !== false) {
		  if($indent_level <= $this->context_locked) {
		    $this->indent_level = $indent_level = $this->context_locked;
		    $this->force_handler = false;
		    $this->context_locked = false;
	    }
    }
  	if($this->context_locked === false) {
  		if($indent_level < $this->indent_level) {
  			if(!($this->expect_indent & self::EXPECT_LESS))
  				$this->exception('Parse error: unexpected indentation decrease');
  
  			$difference = $this->indent_level - $indent_level;
  			while($difference--)
  				$this->context = $this->context->parent;
  		}
  
  		if($indent_level == $this->indent_level) {
  			if(!($this->expect_indent & self::EXPECT_SAME))
  				$this->exception('Parse error: expected indentation change');
  		}
  
  		if($indent_level > $this->indent_level) {
  			if(!($this->expect_indent & self::EXPECT_MORE))
  				$this->exception('Parse error: unexpected indentation increase');
  			if($indent_level - $this->indent_level > 1)
  				$this->exception('Parse error: indent increased by more than 1');
  			if(empty($this->context->children))
  				$this->exception('Parse error: indent increased without parent node');
        
  			$this->context = end($this->context->children);
  		}
		}
    
		$this->indent_level = $indent_level;
		$this->expect_indent = self::EXPECT_ANY;

	}
	
	/**
	 * Handles a line of source.
	 */
	public function handle() {
		
		$handled = false;
				
		if($this->force_handler) {
			$handled = true;
			$handler = $this->force_handler;
			$this->force_handler = false;
			$handler::handle();
		} else {
			foreach(static::$handlers as $trigger => $handler) {
				if($trigger == '*')
					continue; // Leave the wildcard trigger until last.
				if(substr($this->content, 0, strlen($trigger)) == $trigger) {
				  try {
					  $handler::handle($this);
					  $handled = true;
					  break;
				  } catch(NotHandledException $e) {
				    // Fall through
				  }
				}
			}
		}
		
		if(!$handled and isset(static::$handlers['*'])) {
			$handled = true;
			$handler = static::$handlers['*'];
			$handler::handle();
		}
		
		if(!$handled)
			$this->exception('Parse error: unexpected input');
		
	}
	
	/**
	 * Throws an exception, and appends the line number to the given message.
	 */
	public function exception($message, array $sub = array()) {
		
		$sub['line'] = $this->line_number;
		throw new Exception($message . ' - line :line', $sub);
		
	}
	
}

/**
 * An exception thrown when a handler does not handle a source line.
 */

class NotHandledException extends Exception {
  
  
  
}

?>