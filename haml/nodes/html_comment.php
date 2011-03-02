<?php

/**
 * html_comment.php
 */

namespace phphaml\haml\nodes;

use \phphaml\haml\ruby;

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
		
	  $newline = '<?php echo "\n"; ?>';
	  
		$return = '<!--';
		
		if($this->conditional) {
			return
			    $return . $this->content . '>' . $newline
			  . $this->render_children() . $newline
			  . $this->indent() . '<![endif]-->';
		} else {
			if(empty($this->children)) {
			  $this->content = ruby\InterpolatedString::compile($this->content);
				return $return . ' <?php echo (' . $this->content . '); ?> -->';
			}
			else {
				return
				    $return . $newline
				  . $this->render_children() . $newline
  				. $this->indent() . '-->';
			}
		}
		
	}
	
}

?>