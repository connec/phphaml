<?php

/**
 * string.php
 */

namespace phphaml\haml\ruby;

use
	\phphaml\Node;

/**
 * The RubyValue class represents an arbitrary value in a ruby-style attribute hash.
 */

class Value {
	
	/**
	 * Instantiates the RubyValue and determines its type and content.
	 */
	public function __construct($value, Node $node) {
		
		if($value[0] == ':') {
			if(!preg_match('/^:[a-z0-9]+/i', $value))
				$node->exception('Parse error: invalid character in symbol');
			
			$this->content = substr($value, 1);
		} else
		  $this->content = $value;
		
	}
	
}

?>