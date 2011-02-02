<?php

/**
 * haml_comment.php
 */

namespace phphaml\haml\nodes;

/**
 * The HamlComment node represents a HamlComment is the parse tree.
 * 
 * By default HamlComment nodes are stripped once the whole comment has been processed.
 */

class HamlComment extends Node {
	
	/**
	 * Renders the content of the node.
	 */
	public function render() {
		
		return '';
		
	}
	
}

?>