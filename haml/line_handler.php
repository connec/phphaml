<?php

/**
 * line_handler.php
 */

namespace phphaml\haml;

/**
 * The LineHandler class provides base functionality to child line handlers.
 */

abstract class LineHandler extends \phphaml\LineHandler {
	
	/**
	 * Wraps a given value in attribute wrappers as defined in the parsers options.
	 */
	protected function attr($text) {
		
		$wrapper = $this->parser->option('attr_wrapper');
		return $wrapper . $text . $wrapper;
		
	}
	
}

?>