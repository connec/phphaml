<?php

/**
 * parser.php
 */

namespace haml;

/**
 * The Parser class provides the base for parser
 * implementations.
 * 
 * Parser implementations are responsible for converting a source file into a
 * Document tree.
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
	 * The namespace this Parser is in.
	 */
	protected static $namespace;
	
	/**
	 * An array mapping regular expressions to callbacks for handling different
	 * source lines.
	 * 
	 * Handlers are matched in the order defined.
	 */
	protected static $handlers = array();
	
	/**
	 * An array of options affecting parsing or output generation.
	 * 
	 * Options are given on construction, and are passed to the Document.
	 */
	protected $options = array();
	
	/**
	 * The source to parse.
	 * 
	 * This is either a stream pointer or a string.
	 */
	protected $source;
	
	/**
	 * The source line currently being processed.
	 */
	protected $line;
	
	/**
	 * The line number of the source line currently being processed.
	 */
	protected $line_number = 0;
	
	/**
	 * The string used for indentation in the source.
	 */
	protected $indent_string;
	
	/**
	 * The current indentation level.
	 */
	protected $indent_level = 0;
	
	/**
	 * A flag describing the expected indent level of the next line, relative to
	 * the current indent level.
	 */
	protected $expect_indent = self::EXPECT_SAME;
	
	/**
	 * The parsed document.
	 */
	protected $document;
	
	/**
	 * The context Node / Document.
	 */
	protected $context;
	
	/**
	 * Returns a handle to the given file.
	 */
	protected static function open_file($file) {
		
		$sub = array('file' => $file);
		
		if(!file_exists($file))
			throw new Exception('File error: given file does not exists - :file', $sub);
		if(!is_readable($file))
			throw new Exception('File error: cannot read given file - :file', $sub);
		if(!($fh = fopen($file, 'r')))
			throw new Exception('File error: could not open file - :file', $sub);
		
		return $fh;
		
	}
	
	/**
	 * Constructs the parser, given a source and options.
	 */
	public function __construct($source, $options = array()) {
		
		if(!static::$namespace)
			static::$namespace = str_replace('Parser', '', get_class($this));
		
		if(is_file($source))
			$this->source = static::open_file($source);
		else
			$this->source = explode("\n", str_replace(array("\r\n", "\r"), "\n", $source));
		
		$this->options = array_merge($this->options, $options);
		
	}
	
	/**
	 * Creates the specified child node.
	 * 
	 * Node classes are expected to be in the same namespace as the Parser class.
	 */
	protected function create_node($type) {
		
		$class = static::$namespace . ucfirst($type) . 'Node';
		return new $class($this->document, $this->context, $this->line_number, $this->indent_level);
		
	}
	
	/**
	 * Parses the source.
	 * 
	 * At a high level, parsing simply walks the source, line-by-line, and calls 
	 * the handler matching the mapped regular expression.
	 */
	public function parse() {
		
		$document = static::$namespace . 'Document';
		$this->document = new $document($this, $this->options);
		$this->context = $this->document;
		
		while($this->line = $this->get_line()) {
			$this->line_number ++;
			
			$this->update_context();
			
			if(trim($this->line)) {
				$handled = false;
				foreach(static::$handlers as $regex => $handler) {
					if(preg_match($regex, $this->line, $match)) {
						$handled = true;
						call_user_func(array($this, 'handle_' . $handler), $match);
						break;
					}
				}
				
				if(!$handled)
					$this->exception('Parse error: unexpected input');
			}
		}
		
	}
	
	/**
	 * Returns the result of compiling the Document.
	 */
	public function compile() {
		
		if(!$this->document)
			$this->parse();
		return $this->document->compile();
		
	}
	
	/**
	 * Returns the result of rendering the Document.
	 */
	public function render() {
		
		if(!$this->document)
			$this->parse();
		return $this->document->render();
		
	}
	
	/**
	 * Retrieves the next line from the source, whilst one exists.
	 */
	protected function get_line() {
		
		if(is_array($this->source)) {
			$return = current($this->source);
			next($this->source);
		} else {
			$return = fgets($this->source);
		}
		
		return rtrim($return, "\r\n");
		
	}
	
	/**
	 * Updates the context based on the indent level, and checks the indent level
	 * is as expected.
	 */
	protected function update_context() {
		
		$re = '/^\s+/';
		preg_match($re, $this->line, $match);
		if($match) {
			if($this->indent_string) {
				$indent_level = substr_count($this->line, $this->indent_string);
			} else {
				$indent_level = 1;
				$this->indent_string = $match[0];
				$this->document->indent_string = $match[0];
			}
			
			if(str_replace($this->indent_string, '', $match[0]))
				$this->exception('Parse error: mixed indentation');
			
			$this->line = substr($this->line, strlen($match[0]));
		} else {
			$indent_level = 0;
		}
		
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
		
		$this->indent_level = $indent_level;
		$this->expect_indent = self::EXPECT_ANY;
		
	}
	
	/**
	 * Throws an exception with the given message and substitution, and appends
	 * the line number.
	 */
	protected function exception($message, $sub = array()) {
		
		$sub = array_merge($sub, array('line' => $this->line_number));
		$message .= ' - line :line';
		throw new Exception($message, $sub);
		
	}
	
}

?>