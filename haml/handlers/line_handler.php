<?php

/**
 * line_handler.php
 */

namespace phphaml\haml\handlers;

/**
 * The LineHandler class provides base functionality to child line handlers.
 */

abstract class LineHandler extends \phphaml\LineHandler {
	
	/**
	 * Indicates whether this line should render a trailing linebreak.
	 */
	protected $render_newline = true;
	
	/**
	 * Wraps a given value in attribute wrappers as defined in the parsers options.
	 */
	protected function attr($text) {
		
		$wrapper = $this->parser->option('attr_wrapper');
		return $wrapper . $text . $wrapper;
		
	}
	
	/**
	 * The 'inner' render method to be used by child classes.
	 */
	abstract protected function _render();
	
	/**
	 * Renders the parsed tree.
	 */
	public function render() {
		
		return $this->_render() . ($this->render_newline ? "\n" : '');
		
	}
	
	/**
	 * Renders the children of this node.
	 */
	public function render_children() {
		
		$return = '';
		foreach($this->children as $child)
			$return .= $child->render();
		return rtrim($return);
		
	}
	
}

?>