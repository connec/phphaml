<?php

/**
 * parser.php
 */

namespace phphaml;

/**
 * The Parser class forms the root of a source document, and handles traversing and delegating the
 * source lines.
 */

abstract class Parser extends Node {
	
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
	 * The namespace this parser class is in.
	 * 
	 * Note: this can be left blank to reduce configuration.
	 */
	protected static $namespace;
	
	/**
	 * The directory this parser's line handlers are in.
	 * 
	 * Note: for portability this should be left blank to be assigned at runtime.
	 */
	protected static $handler_directory;
	
	/**
	 * An array of line handler classes for this parser.
	 */
	protected static $handlers = array();
	
	/**
	 * An array of options affecting parsing or output generation.
	 */
	protected $options = array();
	
	/**
	 * The source this parser is parsing.
	 */
	protected $source;
	
	/**
	 * The indentation string used in the source being parsed.
	 */
	protected $indent_string;
	
	/**
	 * The context node in the tree.
	 * 
	 * This will be the parent of any created nodes.
	 */
	protected $context;
	
	/**
	 * A flag indicating what indentation to expect on the next line relative to the current
	 * indentation.
	 */
	protected $expect_indent = self::EXPECT_SAME;
	
	/**
	 * When set, the named handler will be used on the next non-empty source line.
	 */
	protected $force_handler;
	
	/**
	 * Accessor for {$options}.
	 */
	public function option($key) {
		
		if(!isset($this->options[$key]))
			throw new Exception('Sanity error: unknown option - ' . $key);
		
		return $this->options[$key];
		
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
	 * Sets the indent expectation.
	 */
	public function expect_indent($flag) {
		
		$this->expect_indent = $flag;
		
	}
	
	/**
	 * Forces the next source line to be handled by the given handler.
	 */
	public function force_handler($handler) {
		
		if(!in_array($handler, static::$handlers))
			throw new Exception('Sanity error: cannot force unregistered handler');
		
		$this->force_handler = $handler;
		
	}
	
	/**
	 * Initialises the parser with given source and options.
	 */
	public function __construct($source, array $options = array()) {
		
		if(!static::$namespace)
			list(static::$namespace) = Library::get_class_info(get_class($this));
		if(!static::$handler_directory)
			list(,,static::$handler_directory) = Library::get_class_info(get_class($this));
		if(empty(static::$handlers)) {
			foreach(glob(static::$handler_directory . '*_handler.php') as $file) {
				$file = substr(basename($file), 0, -4);
				$handler = static::$namespace . '\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $file)));
				$trigger = $handler::trigger();
				
				if(!$trigger)
					continue;
				if(is_array($trigger)) {
					foreach($trigger as $_trigger)
						static::$handlers[$_trigger] = $handler;
				} else
					static::$handlers[$trigger] = $handler;
			}
			
			uksort(static::$handlers, function($a, $b) {
				if(strlen($a) == strlen($b)) return  0;
				if(strlen($b) > strlen($a))  return -1;
				if(strlen($a) > strlen($b))  return  1;
			});
		}
		
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
	 * Renders the parsed tree.
	 */
	public function render() {
		
		if(!$this->line_number)
			$this->parse();
		
		$result = '';
		foreach($this->children as $child)
			$result .= $child->render() . "\n";
		return rtrim($result);
		
	}
	
	/**
	 * Gets a line from the source.
	 */
	public function get_line() {
		
		if(is_array($this->source)) {
			$line = each($this->source);
			if(!$line)
				return false;
			$line = $line[1];
		} elseif(is_resource($this->source)) {
			$line = rtrim(fgets($this->source), "\r\n");
		} else
			throw new Exception('Sanity error: unexpected source type - ' . typeof($this->source));
		
		return $line;
		
	}
	
	/**
	 * Parses the source.
	 * 
	 * Note: most actual parsing is delegated to discovered handlers.
	 */
	public function parse() {
		
		// Set up the tree.
		$this->children = array();
		$this->context = $this;
		
		$this->line_number = 0;
		$this->indent_level = 0;
		while($this->content = $this->get_line()) {
			$this->line_number ++;
			
			try {
				$this->update_context();
			} catch(Exception $e) {
				$e->set('line', $this->line_number);
				throw $e;
			}
			
			if(trim($this->content)) {
				$handled = false;
				
				if($this->force_handler) {
					$handled = true;
					$handler = $this->force_handler;
					$hander::handle($this);
				} else {
					foreach(static::$handlers as $trigger => $handler) {
						if($trigger == '*')
							continue; // Leave the wildcard trigger until last.
						if(substr($this->content, 0, strlen($trigger)) == $trigger) {
							$handled = true;
							$handler::handle($this);
						}
					}
				}
				
				if(!$handled and isset(static::$handlers['*'])) {
					$handled = true;
					$handler = static::$handlers['*'];
					$handler::handle($this);
				}
				
				if(!$handled)
					throw new Exception('Parse error: unexpected input - line ' . $this->line_number);
			}
		}
		
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
				$indent_level = substr_count($this->content, $this->indent_string);
			} else {
				$indent_level = 1;
				$this->indent_string = $match[0];
			}

			if(str_replace($this->indent_string, '', $match[0]))
				throw new Exception('Parse error: mixed indentation - line :line');

			$this->content = substr($this->content, strlen($match[0]));
		} else {
			$indent_level = 0;
		}

		if($indent_level < $this->indent_level) {
			if(!($this->expect_indent & self::EXPECT_LESS))
				throw new Exception('Parse error: unexpected indentation decrease - line :line');

			$difference = $this->indent_level - $indent_level;
			while($difference--)
				$this->context = $this->context->parent;
		}

		if($indent_level == $this->indent_level) {
			if(!($this->expect_indent & self::EXPECT_SAME))
				throw new Exception('Parse error: expected indentation change - line :line');
		}

		if($indent_level > $this->indent_level) {
			if(!($this->expect_indent & self::EXPECT_MORE))
				throw new Exception('Parse error: unexpected indentation increase - line :line');
			if($indent_level - $this->indent_level > 1)
				throw new Exception('Parse error: indent increased by more than 1 - line :line');
			if(empty($this->context->children))
				throw new Exception('Parse error: indent increased without parent node - line :line');

			$this->context = end($this->context->children);
		}

		$this->indent_level = $indent_level;
		$this->expect_indent = self::EXPECT_ANY;

	}
	
}

?>