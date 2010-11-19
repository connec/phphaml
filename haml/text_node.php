<?php

/**
 * text_node.php
 */

namespace haml\haml;

use \haml\ruby\RubyInterpolatedString;

/**
 * The TextNode class represents a text node in a HAML document.
 */

class TextNode extends \haml\Node {
	
	/**
	 * The content of this text node.
	 */
	public $content;
	
	/**
	 * Generates and returns the output for this Node's subtree.
	 */
	public function render() {
		
		return str_repeat($this->document->indent_string, $this->indent_level)
			. $this->content->to_text($this->document->variables);
		
	}
	
}

?>