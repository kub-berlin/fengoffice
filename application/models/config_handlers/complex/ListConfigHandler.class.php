<?php

  /**
  * Let user select where he wants to store uploaded files
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ListConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $options = array();
      
      $data = $this->getConfigOption()->getOptions();
      $possible_values = explode(",", $data);
      
      $options[] = option_tag("", "");
      
      foreach ($possible_values as $value) {
      	
	      $option_attributes = $this->getValue() == $value ? array('selected' => 'selected') : null;
	      $options[] = option_tag($value, $value, $option_attributes);
 		
      }
      
      $attributes = array('id' => 'list_' . $this->getConfigOption()->getName());
      
      return select_box($control_name, $options, $attributes);
    } // render
  
  } // ListConfigHandler

?>