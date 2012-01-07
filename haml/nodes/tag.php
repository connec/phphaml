<?php

/**
 * tag.php
 */

namespace phphaml\haml\nodes;

/**
 * The Tag node represents a HAML tag in a parse tree.
 */

class Tag extends Node {
	
	/**
	 * The tag name of this tag.
	 */
	public $tag_name = 'div';
	
	/**
	 * An array of attributes for this tag.
	 */
	public $attributes = array();
	
	/**
	 * A flag indicating whether or not this tag is self-closing.
	 */
	public $self_closing = false;
	
	/**
	 * A flag indicating whether or not this tag should have surrounding whitespace.
	 */
	public $trim_outer = false;
	
	/**
	 * A flag indicating whether or not this tag should have inner whitespace.
	 */
	public $trim_inner = false;
	
	/**
	 * Renders the parsed tree.
	 */
	public function render() {
		
	  $newline = '<?php echo "\n"; ?>';
	  $open_tag = '<' . $this->tag_name . (empty($this->attributes) ? '' : ' ' . $this->render_attributes_php()) . '>';
		$close_tag = '</' . $this->tag_name . '>';
		
		if($this->self_closing or (empty($this->children) and in_array($this->tag_name, $this->option('autoclose')))) {
			if($this->option('format') == 'xhtml')
				$open_tag = substr($open_tag, 0, -1) . ' />';
			
			return $open_tag;
		}
		
		if(empty($this->children))
			return $open_tag . $this->content . $close_tag;
		
		if($this->trim_inner) {
			foreach($this->children as $child)
			  $child->indent_level --;
		  $this->children[0]->render_indent = 0;
		  return $open_tag . $this->render_children() . $close_tag;
		}
		
		return
		    $open_tag . $newline
		  . $this->render_children() . $newline
		  . $this->indent() . $close_tag;
		
	}
	
	/**
	 * Renders the PHP representing the attributes.
	 */
	protected function render_attributes_php() {
		
	  $attributes = 'array(';
	  foreach($this->attributes as $attribute => $value) {
	    if(is_int($attribute)) {
	      if(is_array($value[1])) {
	        $_value = 'array(';
	        foreach($value[1] as $__value)
	          $_value .= $__value . ',';
          $value[1] = $_value . ')';
	      }
	      $value = 'array(' . $value[0] . ',' . $value[1] . ')';
	    } else
	      $attribute = var_export($attribute, true);
	    
	    $attributes .= $attribute . '=>';
	    
	    if(is_array($value)) {
	      $attributes .= 'array(';
	      foreach($value as $_value)
	        $attributes .= $_value . ',';
        $attributes .= ')';
	    } else
	      $attributes .= $value;
      
      $attributes .= ',';
	  }
	  $attributes .= ')';
	  
	  return '<?php $__render_attributes(' . $attributes . '); ?>';
		
	}
	
	/**
	 * Renders HTML for given attributes.
	 */
	public static function render_attributes_html($format, $quote, $attributes) {
	  
	  $result = array();
    $classes = array();
    $ids = array();
    
    foreach($attributes as $attribute) {
      if($attribute[1] === false or $attribute[1] === null)
        continue;
      
      $value = $attribute[1];
      $attribute = $attribute[0];
      
      if($attribute == 'class') {
        if(is_array($value))
          $classes = array_merge($classes, $value);
        else
          $classes[] = $value;
        continue;
      }
      
      if($attribute == 'id') {
        if(is_array($value))
          $ids = array_merge($ids, $value);
        else
          $ids[] = $value;
        continue;
      }
      
      if($value === true and $format != 'xhtml') {
        $result[] = $attribute;
        continue;
      } elseif($value === true) {
        $result[] = $attribute . '=' . $quote . $attribute . $quote;
        continue;
      }
      
      if($attribute == 'class') {
        $classes = array_merge($classes, $value);
        continue;
      } elseif($attribute == 'id') {
        $ids = array_merge($ids, $value);
        continue;
      }
      
      $result[] = $attribute . '=' . $quote . $value . $quote;
    }
    
    if(!empty($classes)) {
      natcasesort($classes);
      $result[] = 'class=' . $quote . implode(' ', $classes) . $quote;
    }
    if(!empty($ids))
      $result[] = 'id=' . $quote . implode('_', $ids) . $quote;
    
    natcasesort($result);
    echo implode(' ', $result);
	  
	}
	
}

?>