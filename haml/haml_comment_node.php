<?php

/**
 * haml_comment_node.php
 */

namespace haml\haml;

/**
 * The HamlCommentNode class represents a HAML comment in a HAML document.
 */

class HamlCommentNode extends Node {
	
	/**
	 * Generates and returns the output for this Node's subtree.
	 * 
	 * This will always be empty.
	 */
	public function render() {
		
		return '';
		
	}
	
}

?>