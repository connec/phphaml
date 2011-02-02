<?php

/**
 * plain.php
 */

namespace phphaml\haml\filters;

use \phphaml\Node;

/**
 * The plain filter simply outputs what it is given, without any additional parsing.
 */

class Plain extends Filter {
	
	/**
	 * Filters the given content.
	 */
	public static function filter(Node $node) {
		
		return $node->content;
		
	}
	
}

?>