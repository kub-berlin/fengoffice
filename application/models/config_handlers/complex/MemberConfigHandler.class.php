<?php

/**
* Class that handles member config values
*
* @version 1.0 
*/
class MemberConfigHandler extends ConfigHandler {
    
   /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
    	
    	$options = null;
    	$opt_options = $this->getConfigOption()->getOptions();
    	if ($opt_options) {
    		$options = json_decode($opt_options, true);
    	}
    	
    	if (!$options) return;
    	
    	$sel_options = array(
    		'is_multiple' => array_var($options, 'is_multiple', false),
    		'width' => '300',
    		'hide_label' => true,
    		'hidden_field_name' => $control_name,
    	);
    	
    	$dimension = Dimensions::findById($options['dim_id']);
    	if (isset($options['mem_type_ids'])) {
    		$sel_options['allowedMemberTypes'] = $options['mem_type_ids'];
    	}
    	
    	ob_start();
    	render_single_member_selector($dimension, null, array($this->getValue()), $sel_options);
    	$html = ob_get_clean();
    	
    	return $html;
	}
	
	
	function rawToPhp($value) {
		if (is_numeric($value)) return $value;
		$vals = json_decode($value);
		return array_var($vals, 0);
	}
	
	function phpToRaw($value) {
		if (is_numeric($value)) return $value;
		$vals = json_decode($value);
		return array_var($vals, 0);
	}
    
}
