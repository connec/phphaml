<?php

/**
 * value.php
 */

namespace phphaml\haml;

use \phphaml\Node;

/**
 * The Value class represents an arbitrary value in a HAML parse tree.
 */

class Value extends InterpolatedString {
	
	/**
	 * Instantiates the Value and determines its type and content.
	 */
	public function __construct($value, Node $node) {
		
		if($value[0] == '\'') {
			if($value[strlen($value) - 1] != '\'')
				$node->exception('Parse error: missing closing quote (\')');
			
			if(preg_match('/(^|[^\\\\]|[\\\\][\\\\])\'/', $this->content[] = substr($value, 1, -1)))
				$node->exception('Parse error: unescaped quote (\')');
		} elseif($value[0] == '"') {
			if($value[strlen($value) - 1] != '"')
				$node->exception('Parse error: missing closing quote (")');
			
			if(preg_match('/(^|[^\\\\]|[\\\\][\\\\])"/', substr($value, 1, -1)))
				$node->exception('Parse error: unescaped quote (")');
			
			parent::__construct(substr($value, 1, -1));
		} else
			$this->content[] = new EvalString($value);
		
	}
	
}

?>