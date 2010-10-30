<?php

/**
 * parser.php
 */

namespace hamlparser\lib;

/**
 * The Parser class abstracts common parsing functionality for HamlParser and
 * SassParser.
 */

abstract class Parser {
	
	/**
	 * The class to use as a node (must extend {@link \hamlparser\lib\Node}).
	 */
	protected static $node_class = '\hamlparser\lib\Node';
	
	/**
	 * The options to use during parsing.  Implementation specific.
	 */
	protected $options = array();
	
	/**
	 * The handle for the file being parsed.
	 */
	protected $file;
	
	/**
	 * The current line number of the file being parsed.
	 */
	protected $line;
	
	/**
	 * The indentation string for the file being parsed.
	 */
	protected $indent;
	
	/**
	 * The current indentation level.
	 */
	protected $indent_level = 0;
	
	/**
	 * The root of tree of data parsed so far.
	 */
	protected $root;
	
	/**
	 * A the current node in the tree.
	 */
	protected $node;
	
	/**
	 * The result of the parse.
	 */
	protected $result;
	
	public function __construct() {
		
		if(!class_exists(static::$node_class)) {
			throw new Exception(
				'Missing class for $node_class: :class',
				array('class' => static::$node_class)
			);
		}
		
		$test = new static::$node_class(0, 0);
		if(!($test instanceof Node)) {
			throw new Exception(
				'Parser::$node_class must inherit from \hamlparser\lib\Node: :class',
				array('class' => static::$node_class)
			);
		}
		
	}
	
	/**
	 * Returns the string result of parsing.
	 */
	public function result() {
		
		if(!$this->result)
			$this->result = (string)$this->root;
		return $this->result;
		
	}
	
	/**
	 * Parses a given HAML file.
	 */
	public function parse($file) {
		
		if(!file_exists($file)) {
			throw new Exception(
				'File does not exist: :file',
				array('file' => $file)
			);
		}
		
		if(!is_readable($file)) {
			throw new Exception(
				'Cannot read file, ensure you have the appropriate conditions: :file',
				array('file' => $file)
			);
		}
		
		if(!($this->file = fopen($file, 'r'))) {
			throw new Exception(
				'Error reading file: :file',
				array('file' => $file)
			);
		}
		
		$this->line = 0;
		$this->root = new static::$node_class(0, 0);
		$this->node = $this->root;
		
		while($line = fgets($this->file)) {
			$this->line ++;
			$this->parse_line(rtrim($line, "\n\r"));
		}
		
		fclose($this->file);
		
		if(!$this->root->validate()) {
			throw new Exception(
				'Invalid tree was generated.  Developers: use exceptions in your Node '
				. 'class to provide more useful information.'
			);
		}
		
		return true;
		
	}
	
	/**
	 * Parses the current line.
	 */
	protected function parse_line($line) {
		
		preg_match('/^\s+/', $line, $match);
		if(empty($match)) {
			$indent_level = 0;
		} elseif($this->indent === null) {
			$this->indent = $match[0];
			$indent_level = 1;
		} else {
			if(str_replace($this->indent, '', $match[0]) != '') {
				throw new Exception(
					'Parse error at line :line: mixed indentation',
					array('line' => $this->line)
				);
			}
			
			$indent_level = substr_count($match[0], $this->indent);
		}
		
		if($indent_level != $this->indent_level)
			$this->context($indent_level);
		
		if(ltrim($line)) {
			$this->node->add_child(call_user_func(static::$node_class.'::factory',
				$this->line,
				$this->indent_level,
				ltrim($line),
				$this->node)
			);
		}
		
	}
	
	/**
	 * Handles changing the node context.
	 */
	protected function context($indent_level) {
		
		// Sanity check
		if($indent_level == $this->indent_level)
			return;
		
		if($indent_level > $this->indent_level) {
			if($indent_level - $this->indent_level > 1) {
				throw new Exception(
					'Parse error at line :line: indent increased by more than one',
					array('line' => $this->line)
				);
			}
			
			$this->node = $this->node->last_child();
		} else {
			$difference = $this->indent_level - $indent_level;
			for($i = 0; $i < $difference; $i ++)
				$this->node = $this->node->parent();
		}
		
		$this->indent_level = $indent_level;
		
	}
	
}

?>