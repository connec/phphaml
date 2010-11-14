<?php

namespace hamlparser\lib\haml;

class Helper {
	
	public static function attribute($name) {
		
		return Parser::options('attr_wrapper') . $name . Parser::options('attr_wrapper');
		
	}
	
	public static function escape_html($content) {
		
		return htmlentities(html_entity_decode($content));
		
	}
	
}

?>