<?php

/**
 * eval_string.php
 */

namespace phphaml\haml;

/**
 * The EvalString class contains a string of PHP code for later evaluation.
 */

class EvalString {
	
	/**
	 * Variables set to be used during evaluation.
	 */
	protected static $variables = array();
	
	/**
	 * The PHP code for evaluation.
	 */
	protected $content;
	
	/**
	 * Sets the variables to use during evaluation.
	 */
	public static function variables(array $variables) {
		
		static::$variables = $variables;
		
	}
	
	/**
	 * Instantiates a new EvalString with given content.
	 */
	public function __construct($content) {
		
		$this->content = $content;
		
	}
	
	/**
	 * Evaluates the PHP code.
	 */
	public function evaluate() {
		
		$evaluate = function($__variables__, $__content__) {
			extract($__variables__);
			return eval('return (' . $__content__ . ');');
		};
		
		return $evaluate(static::$variables, $this->content);
		
	}
	
	/**
	 * Automagically generates a string result from the PHP code.
	 */
	public function __toString() {
		
		return (string) $this->evaluate();
		
	}
	
}

?>