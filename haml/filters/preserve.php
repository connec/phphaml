<?php

/**
 * preserve.php
 */

namespace phphaml\haml\filters;

use \phphaml\Node;

/**
 * The Preserve filter escapes whitespace in a block.
 */

class Preserve extends Filter {
	
	/**
	 * Filters the given content.
	 */
	public static function filter(Node $node) {
		
	  return implode('&#x000A;', $node->content);
		
	}
	
}

?>