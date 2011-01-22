<?php

/**
 * line_handler.php
 */

namespace phphaml;

/**
 * The LineHandler class provides base functionality to child line handlers.
 */

abstract class LineHandler extends Node {
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = false;
	
	/**
	 * The line number of this node.
	 */
	protected $line_number;
	
	/**
	 * The indentation level of this node.
	 */
	protected $indent_level;
	
	/**
	 * Accessor for {$trigger}.
	 */
	public static function trigger() {
		
		return static::$trigger;
		
	}
	
	/**
	 * Handles the current line in the given parser.
	 * 
	 * This is used instead of the parser appending to the tree itself in order to deal with
	 * potential multiline statements.
	 */
	public static function handle(Parser $parser) {
		
		$class = get_called_class();
		$parser->context()->add_child(new $class($parser));
		$parser->context()->last_child()->parse();
		
	}
	
	/**
	 * Initialises the node.
	 */
	public function __construct(Parser $parser) {
		
		$this->parser = $parser;
		$this->parent = $parser->context();
		
		$this->line_number = $parser->line_number();
		$this->indent_level = $parser->indent_level();
		$this->content = $parser->content();
		
	}
	
	/**
	 * Parses the content of this node.
	 */
	abstract public function parse();
	
	/**
	 * Renders the parsed tree.
	 */
	public function render() {
		
		return str_repeat($this->parser->indent_string(), $this->indent_level)
			. $this->content
			. $this->render_children();
		
	}
	
	/**
	 * Renders the children of this node.
	 */
	public function render_children() {
		
		$return = '';
		foreach($this->children as $child)
			$return .= $child->render() . "\n";
		return rtrim($return);
		
	}
	
}

?>