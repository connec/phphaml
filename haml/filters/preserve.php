<?php

/**
 * preserve.php
 */

namespace phphaml\haml\filters;

use
	\phphaml\Node,
	\phphaml\haml\Parser;

/**
 * The Preserve filter escapes whitespace in a block.
 */

class Preserve implements Filter {
	
	/**
	 * Filters the given content.
	 */
	public static function filter(Parser $parser, Node $node, array $content) {
		
		return implode('&#x000A;', $content);
		
	}
	
}

?>