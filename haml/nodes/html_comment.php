<?php

/**
 * html_comment.php
 */

namespace phphaml\haml\nodes;

/**
 * The HtmlComment node represents an HTML comment in the parse tree.
 */

class HtmlComment extends Node {
	
	/**
	 * A boolean flag indicating whether this comment is a conditional statement.
	 */
	public $conditional = false;
	
	/**
	 * Renders the content of the node.
	 */
	public function render() {
		
		$indent = str_repeat($this->indent_string(), $this->indent_level);
		
		$return = $indent . '<!--';
		
		if($this->conditional)
			return $return . $this->content . ">\n" . $this->render_children() . $indent . '<![endif]-->';
		else {
			if(empty($this->children))
				return $return . ' ' . $this->content . " -->\n";
			else
				return $return . "\n" . $this->render_children() . $indent . '-->';
		}
		
	}
	
}

?>