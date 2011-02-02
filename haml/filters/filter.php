<?php

/**
 * filter.php
 */

namespace phphaml\haml\filters;

use
	\phphaml\Node,
	\phphaml\haml\Parser;

/**
 * The Filter class provides abstract methods for all filters.
 */

class Filter {
	
	/**
	 * Filters the given content.
	 */
	public static function filter(Node $node) {
		
		throw new Exception('Sanity error: filters must implement the filter() method.');
		
	}
	
}

?>