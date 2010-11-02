<?php

/**
 * string.php
 */

namespace hamlparser\lib\ruby;

/**
 * The RubyValue class contains functions for checking if a given string is a 
 * valid Ruby value.
 */

class RubyValue {
	
	public static function value_to_string($value) {
		
		if($value[0] == ':')
			return static::symbol_to_string($value);
		
		if(($value[0] == '\'' or $value[0] == '"'))
			return static::string_to_string($value);
		
		if(is_numeric($value))
			return $value;
		
		throw new Exception(
			'Syntax error: invalid value, values must be strings, symbols or numeric',
			array('char' => $value[0])
		);
		
	}
	
	public static function string_to_string($string) {
		
		if(preg_match("/^.+[^\\\\]{$string[0]}.+$/", $string)) {
			throw new Exception(
				'Syntax error: unescaped ":char" in string',
				array('char' => $string[0])
			);
		}
		if($string[0] != $string[strlen($string) - 1]) {
			throw new Exception(
				'Syntax error: unexpected ":char1" at end of string, expected ":char2"',
				array('char1' => $string[strlen($string) - 1], 'char2' => $string[0])
			);
		}
		if($string[0] == '"') {
			$string = preg_replace('/([^\\\\])#{(.*?)}/', '$1<?= $2 ?>', $string);
			if(strpos($string, '#{') !== false) {
				throw new Exception(
					'Syntax error: no closing "}" for open "#{"'
				);
			}
		}
		return substr($string, 1, -1);
		
	}
	
	public static function symbol_to_string($symbol) {
		
		if(!preg_match('/^:[_a-zA-Z][_a-zA-Z0-9]*$/', $symbol)) {
			// Check if it's a string 'forced' to be a symbol.
			try {
				return static::string_to_string(substr($symbol, 1));
			} catch(Exception $e) {
				throw new Exception('Syntax error: invalid symbol');
			}
		}
		
		return substr($symbol, 1);
		
	}
	
}

?>