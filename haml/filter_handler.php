<?php

/**
 * filter_handler.php
 */

namespace phphaml\haml;

use \phphaml\Library;

/**
 * The FilterHandler handles filtered blocks.
 */

class FilterHandler extends LineHandler {
	
	/**
	 * The directory filters reside in.
	 * 
	 * By default this will be the "filter" directory in the same directory as this file.
	 */
	protected static $filter_directory;
	
	/**
	 * The namespace filters reside in.
	 * 
	 * By default this will be the filter subnamespace of this namespace.
	 */
	protected static $filter_namespace;
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = array(':');
	
	/**
	 * The filter being parsed.
	 */
	protected static $filter_handler;
	
	/**
	 * Represents the indent level this filter began on.
	 */
	protected static $start_indent_level = false;
	
	/**
	 * The name of the filter to use on the content.
	 */
	protected $filter;
	
	/**
	 * The content of filters is an array (of lines) rather than a string.
	 */
	protected $content = array();
	
	/**
	 * Handles the current line in the given parser.
	 * 
	 * This is used instead of the parser appending to the tree itself in order to deal with
	 * potential multiline statements.
	 */
	public static function handle(\phphaml\Parser $parser) {
		
		if(static::$start_indent_level === $parser->indent_level()) {
			static::$start_indent_level = false;
			static::$filter_handler = null;
			return $parser->handle();
		}
		
		if(static::$start_indent_level === false) {
			parent::handle($parser);
			static::$start_indent_level = $parser->indent_level();
			static::$filter_handler = $parser->context()->last_child();
		} else {
			$indent_level = $parser->indent_level() - static::$start_indent_level - 1;
			$indent = str_repeat($parser->indent_string(), $indent_level);
			static::$filter_handler->content[] = $indent . $parser->content();
		}
		
		$parser->force_handler(get_called_class());
		
	}
	
	/**
	 * Parses the content of this node.
	 */
	public function parse() {
		
		if(!static::$filter_namespace) {
			list(static::$filter_namespace) = Library::get_class_info(get_class($this));
			static::$filter_namespace .= '\\filters';
		}
		
		$this->filter = 
			  static::$filter_namespace . '\\'
			. str_replace(' ', '', ucwords(str_replace('_', ' ', substr($this->content, 1))));
		
		if(!class_exists($this->filter))
			$this->exception('Load Error: could not load filter :filter', array('filter' => $this->content));
		
		$this->content = array();
		
	}
	
	/**
	 * Renders the parsed tree.
	 */
	public function _render() {
		
		$filter = $this->filter;
		$filtered = $filter::filter($this->parser, $this, $this->content);
		
		if(is_array($filtered))
			$filtered = implode("\n", $filtered);
		
		return new InterpolatedString($filtered, $this);
		
	}
	
}

?>