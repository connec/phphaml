<?php

/**
 * filter.php
 */

namespace phphaml\haml\filters;

use
	\phphaml\Node,
	\phphaml\haml\Parser;

/**
 * The Filter interface provides abstract methods for all filters.
 */

interface Filter {
	
	/**
	 * Filters the given content.
	 */
	public static function filter(Parser $parser, Node $node, array $content);
	
}

?>