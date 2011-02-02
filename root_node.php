<?php

/**
 * root_node.php
 */

namespace phphaml;

/**
 * The RootNode class forms the root node in any document tree.
 */

class RootNode extends Node {
	
	/**
	 * The parser the created this node.
	 */
	protected $parser;
	
	/**
	 * Gets the value of an option.
	 */
	public function option($key) {
		
		return $this->parser->option($key);
		
	}
	
	/**
	 * Gets the indent string to use when rendering.
	 */
	public function indent_string() {
		
		return $this->parser->indent_string();
		
	}
	
	/**
	 * Instantiates this node.
	 */
	public function __construct(Parser $parser) {
		
		$this->parser = $parser;
		$this->options = $parser->options();
		$this->root = $this;
		
	}
	
	/**
	 * Renders the content of the node.
	 */
	public function render() {
		
		return 'render';
		
	}
	
}

?>