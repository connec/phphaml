<?php

/**
 * ruby_interpolated_string.php
 */

namespace phphaml\ruby;

use
	\phphaml\Exception,
	\phphaml\StringStream;

/**
 * The RubyInterpolatedString class handles parsing ruby's interpolated strings
 * into PHP echo statements for evaluation.
 * 
 * @todo Handle interpolated strings that are inside interpolated strings.
 */

class RubyInterpolatedString {
	
	/**
	 * A regular expression for matching the beginning of an interpolation.
	 */
	const RE_INTERPOLATION_START = '/(?:^|[^\\\\]|[\\\\]{2})(#){/';
	
	/**
	 * The input string being parsed.
	 */
	protected $input;
	
	/**
	 * Contains the enclosing string character in the event that $input is a ruby
	 * string.
	 */
	protected $string_delimeter;
	
	/**
	 * The names of variables in this interpolation.
	 */
	protected $variables = array();
	
	/**
	 * The parsed (eval'able) string.
	 */
	protected $parsed;
	
	/**
	 * Initalises the class and parses the given string.
	 */
	public function __construct($input) {
		
		$this->input = $input;
		
		while(preg_match(self::RE_INTERPOLATION_START, $this->input, $match, PREG_OFFSET_CAPTURE)) {
			$this->parsed .= substr($this->input, 0, $match[1][1]) . '<?php echo ';
			$this->input = substr($this->input, $match[1][1] + 2);
			$this->parse_interpolation();
			$this->parsed .= '; ?>';
		}
		
		$this->parsed .= $this->input;
		
		$this->parsed = preg_replace(
			array('/([^\\\\]|^)[\\\\]#/', '/[\\\\]{2}/'),
			array('$1#', '\\'),
			$this->parsed
		);
		
	}
	
	/**
	 * Returns the parse result before evaluation.
	 */
	public function to_php() {
		
		return $this->parsed;
		
	}
	
	/**
	 * Returns the evaluated parse result .
	 */
	public function to_text($variables = array()) {
		
		foreach($this->variables as $variable) {
			if(isset($variables[$variable]))
				$$variable = $variables[$variable];
		}
		
		ob_start();
		StringStream::set('ruby_interpolated_string', $this->parsed);
		include 'string://ruby_interpolated_string';
		StringStream::clear('ruby_interpolated_string');
		return ob_get_clean();
		
	}
	
	/**
	 * Parses the interpolation that begins at position $start.
	 */
	protected function parse_interpolation() {
		
		$interesting = '/(?:\\\\|\'|"|}|\$[a-z_][a-z0-9_]*)/i';
		$in_apos = false;
		$in_quot = false;
		$escape = false;
		
		while(preg_match($interesting, $this->input, $match, PREG_OFFSET_CAPTURE)) {
			$this->parsed .= substr($this->input, 0, $match[0][1]);
			$this->input = substr($this->input, $match[0][1] + strlen($match[0][0]));
			
			if($escape) {
				$escape = false;
				$this->parsed .= $match[0][0];
				continue;
			}
			
			switch($match[0][0][0]) {
				case '\\':
					$escape = true;
				break;
				case '$':
					if($match[0][0] == '$this')
						$match[0][0] = '$_this';
					$this->variables[] = substr($match[0][0], 1);
				break;
				case '\'':
					if(!$in_quot)
						$in_apos = !$in_apos;
				break;
				case '"':
					if(!$in_apos)
						$in_quot = !$in_quot;
				break;
				case '}':
					if(!$in_apos and !$in_quot)
						return;
				break;
			}
			
			$this->parsed .= $match[0][0];
		}
		
		throw new Exception('Parse error: missing closing \'}\' for interpolated string');
		
	}
	
}

?>