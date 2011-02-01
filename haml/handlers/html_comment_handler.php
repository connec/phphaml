<?php

/**
 * html_comment_handler.php
 */

namespace phphaml\haml\handlers;

use \phphaml\haml\Parser;

/**
 * The HtmlCommentHandler handles HTML comments in a HAML document.
 */

class HtmlCommentHandler extends LineHandler {
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = array('/');
	
	/**
	 * A boolean flag indicating whether this comment is a conditional statement.
	 */
	protected $conditional = false;
	
	/**
	 * Parses the content of this node.
	 * 
	 * Naturally, for HAML comments this does nothing.
	 */
	public function parse() {
		
		$this->content = trim(substr($this->content, 1));
		
		if($this->content and $this->content[0] == '[')
			$this->conditional = true;
		
		if($this->content and !$this->conditional)
			$this->parser->expect_indent(Parser::EXPECT_SAME | Parser::EXPECT_MORE);
		
	}
	
	/**
	 * Renders the parsed tree.
	 * 
	 * Naturally, for HAML comments this returns an empty string.
	 */
	public function _render() {
		
		$indent = str_repeat($this->parser->indent_string(), $this->indent_level);
		
		$return = $indent . '<!--';
		
		if($this->conditional)
			return $return . $this->content . ">\n" . $this->render_children() . "\n$indent<![endif]-->";
		else {
			if(empty($this->children))
				return $return . ' ' . $this->content . " -->\n";
			else
				return $return . "\n" . $this->render_children() . "\n$indent-->";
		}
		
	}
	
}

?>