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
		
	  $open_tag = '<' . $this->tag_name . (empty($this->attributes) ? '' : ' ' . $this->render_attributes()) . '>';
		$close_tag = '</' . $this->tag_name . '>';
		
		if($this->self_closing) {
			if($this->option('format') == 'xhtml')
				$open_tag = substr($open_tag, 0, -1) . ' />';
			
			return array($this->indent() . $open_tag);
		}
		
		if(empty($this->children)) {
			return array(
			  $this->indent() . $open_tag . $this->content . $close_tag
			);
		}
		
		if($this->trim_inner) {
			foreach($this->children as $child)
			  $child->indent_level --;
		  $this->children[0]->indent_level = 0;
		  return array(
		    $this->indent() . $open_tag . $this->render_children() . $close_tag
    	);
		}
		
		return array(
		  $this->indent() . $open_tag,
		  $this->render_children(),
		  $this->indent() . $close_tag
		);
		
	}
	
	/**
	 * Renders the attribute string for this tag.
	 */
	protected function render_attributes() {
		
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
	  
	  return '<?php $__generate_attributes('
	    . var_export($this->option('format'), true) . ','
	    . var_export($this->option('attr_wrapper'), true) . ','
  	  . $attributes . '); ?>';
		
	}
	
}

?>