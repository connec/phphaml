<?php

/**
 * parser.php
 */

namespace hamlparser\lib;

/**
 * The Parser class handles the construction of the parse tree and general 
 * structure checking.
 */

abstract class Parser {
	
	/**
	 * The class to use for tree nodes.
	 */
	protected static $node_class = '\hamlparser\lib\Node';
	
	/**
	 * Bit-flag for indicating to the parser that the indent level should
	 * decrease.
	 */
	const INDENT_LESS = 1;
	
	/**
	 * Bit-flag for indicating to the parser that the indent level should
	 * increase.
	 */
	const INDENT_MORE = 2;
	
	/**
	 * Bit-flag for indicating to the parser that the indent level should
	 * remain the same.
	 */
	const INDENT_SAME = 4;
	
	/**
	 * Convenience bit-flag for indicating to the parser that the indent level
	 * can do anything.
	 */
	const INDENT_ANY = 7;
	
	/**
	 * The options configuring the parser.
	 */
	protected $options = array();
	
	/**
	 * The root of the parse tree.
	 */
	protected $root;
	
	/**
	 * The current context node.
	 */
	protected $node;
	
	/**
	 * The line number of the line being processed.
	 */
	protected $line_number = 0;
	
	/**
	 * The line of the file being parsed.
	 */
	protected $line;
	
	/**
	 * The indent string of the file being parsed.
	 */
	protected $indent;
	
	/**
	 * The current indent level.
	 */
	protected $indent_level = 0;
	
	/**
	 * The expected indent level as a bit-flag.
	 */
	protected $expected_indent = self::INDENT_SAME;
	
	/**
	 * The result of the most recent parse.
	 */
	protected $result;
	
	/**
	 * Returns the value of the given option, or the options array if none
	 * given.
	 */
	public function options($option = null) {
		
		if(!$options)
			return $this->options;
		
		if(!isset($this->options[$option])) {
			throw new Exception(
				'Internal error: unknown option ":option"',
				array('option' => $option)
			);
		}
		
		return $this->options[$option];
		
	}
	
	/**
	 * Returns the line number of the line being processed.
	 */
	public function line_number() {
		
		return $this->line_number;
		
	}
	
	/**
	 * Returns the line of the file being parsed.
	 */
	public function line() {
		
		return $this->line;
		
	}
	
	/**
	 * Returns the indent string of the file being parsed.
	 */
	public function indent() {
		
		return $this->indent;
		
	}
	
	/**
	 * Returns the current indent level.
	 */
	public function indent_level() {
		
		return $this->indent_level;
		
	}
	
	/**
	 * Sets the expected indent level bit-flag if given, returns the current
	 * expectation otherwise.
	 */
	public function expect($flag = null) {
		
		if($flag === null)
			return $this->expected_indent;
		
		$this->expected_indent = $flag;
		
	}
	
	/**
	 * Returns the result of the most recent parse.
	 */
	public function result() {
		
		return $this->result;
		
	}
	
	/**
	 * Initalises the parser with given options.
	 */
	public function __construct($options = array()) {
		
		if(!class_exists(static::$node_class)) {
			throw new Exception(
				'Internal error: given node class does not exist - :class',
				array('class' => static::$node_class)
			);
		}
		
		if(!is_subclass_of(static::$node_class, '\hamlparser\lib\Node')) {
			throw new Exception(
				'Internal error: given node class does not extend \hamlparser\lib\Node - :class',
				array('class' => static::$node_class)
			);
		}
		
		$this->options = $options;
		
	}
	
	/**
	 * Parses the given file and returns the result.
	 */
	public function parse($file) {
		
		$sub = array('file' => $file);
		if(!file_exists($file)) {
			throw new Exception(
				'Input error: file does not exist - :file',
				$sub
			);
		}
		if(!is_readable($file)) {
			throw new Exception(
				'Input error: could not read file - :file',
				$sub
			);
		}
		if(!($fh = fopen($file, 'r'))) {
			throw new Exception(
				'Input error: could not open file - :file',
				$sub
			);
		}
		
		$node = static::$node_class;
		$this->root = new $node($this);
		$this->node = $this->root;
		
		while($this->line = fgets($fh)) {
			$this->line = rtrim($this->line, "\r\n");
			$this->line_number ++;
			$this->handle_context();
			if(!empty($this->line)) {
				$this->expected_indent = self::INDENT_ANY;
				$this->node->add_child($this);
			}
		}
		
		$this->root->parse();
		return rtrim((string)$this->root);
		
	}
	
	/**
	 * Handles context changes based on indentation.
	 */
	public function handle_context() {
		
		$sub = array('line' => $this->line_number);
		
		// Find the new indent level.
		preg_match('/^\s+/', $this->line, $match);
		if(empty($match)) {
			$indent_level = 0;
		} elseif(!$this->indent) {
			$this->line = substr($this->line, strlen($match[0]));
			$this->indent = $match[0];
			$indent_level = 1;
		} else {
			if(str_replace($this->indent, '', $match[0], $indent_level)) {
				throw new Exception(
					'Parse error: mixed indentation - line :line',
					$sub
				);
			}
			$this->line = substr($this->line, strlen($match[0]));
		}
		
		if($indent_level < $this->indent_level) {
			if(!($this->expected_indent & self::INDENT_LESS)) {
				throw new Exception(
					'Parse error: did not expect decreased indent - line :line',
					$sub
				);
			}
			
			$decrease = $this->indent_level - $indent_level;
			for($i = 0; $i < $decrease; $i ++)
				$this->node = $this->node->parent();
		} elseif($indent_level == $this->indent_level) {
			if(!($this->expected_indent & self::INDENT_SAME)) {
				throw new Exception(
					'Parse error: did not expect same indent - line :line',
					$sub
				);
			}
		} else {
			if(!($this->expected_indent & self::INDENT_MORE)) {
				throw new Exception(
					'Parse error: did not expect increased indent - line :line',
					$sub
				);
			}
			
			if($indent_level - $this->indent_level > 1) {
				throw new Exception(
					'Parse error: indent increased by more than 1 - line :line',
					$sub
				);
			}
			
			$this->node = $this->node->last_child();
		}
		
		$this->indent_level = $indent_level;
		
	}
	
}

?>