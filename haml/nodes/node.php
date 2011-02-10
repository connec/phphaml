<?php

/**
 * node.php
 */

namespace phphaml\haml\nodes;

/**
 * This Node class extends the base Node class with HAML specific stuff.
 */

abstract class Node extends \phphaml\Node {
	
	/**
	 * Indicates whether or not indentation should be rendered before this node.
	 */
	public $render_indent = true;
	
	/**
	 * Indicates whether or not a newline should be rendered after this node.
	 */
	public $render_newline = true;
	
	/**
	 * Generates PHP/HTML code for this nodes children.
	 */
	public function render_children() {
		
		$result = '';
		foreach($this->children as $child) {
			$indent = str_repeat($child->indent_string(), $child->indent_level);
			$result .=
			  	($child->render_indent ? $indent : '')
				. $child->render()
				. ($child->render_newline ? "\n" : '');
		}
		return $result;
		
	}
}

?>