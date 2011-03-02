<?php

/**
 * filter.php
 */

namespace phphaml\haml\nodes;

use \phphaml\haml\ruby;

/**
 * The Filter node represents filtered content in the parse tree.
 */

class Filter extends Node {
	
	/**
	 * The filter class to use on the content.
	 */
	public $filter;
	
	/**
	 * The content of filters is an array (of lines) rather than a string.
	 */
	public $content = array();
	
	/**
	 * Renders the parsed tree.
	 */
	public function render() {
	  
		$filter = $this->filter;
		$filtered = $filter::filter($this);
		
		if(is_array($filtered))
		  $filtered = implode('#{"\n"}' . $this->indent(), $filtered);
		
		return '<?php echo (' . ruby\InterpolatedString::compile($filtered) . '); ?>';
		
	}
	
}

?>