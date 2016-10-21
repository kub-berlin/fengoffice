<style>
table.og-custom-properties.main td {
	line-height: 25px;
	font-size: 14px;
}
</style>
<?php
	if (!isset($visibility)) $visibility = 'all';
	
	$properties = $__properties_object->getCustomProperties();	
	$cpvCount = CustomPropertyValues::getCustomPropertyValueCount($__properties_object, $visibility);
	if ((!is_array($properties) || count($properties) == 0) && $cpvCount == 0) 
		return "";

if (!($visibility == 'all' || $visibility == 'visible_by_default')) { 
	?><div class="commentsTitle"><?php
		echo lang('other properties');
	?></div><?php
} 
?>
<?php if($cpvCount > 0){?>
<table class="og-custom-properties <?php echo ($visibility == 'visible_by_default' ? 'main' : 'other')?>">
<?php 
	$alt = true;
	$cps = CustomProperties::getAllCustomPropertiesByObjectType($__properties_object->getObjectTypeId(), $visibility);
	foreach($cps as $customProp){ 
		$cpv = CustomPropertyValues::getCustomPropertyValue($__properties_object->getId(), $customProp->getId());
		if($cpv instanceof CustomPropertyValue && ($customProp->getIsRequired() || $cpv->getValue() != '')){
			$alt = !$alt; ?>
			<tr class="<?php echo $alt ? 'altRow' : ''?>">
				<td class="name" title="<?php 
					$label = clean($customProp->getName());
					if ($customProp->getIsSpecial()) {
						$label_code = str_replace("_special", "", $customProp->getCode());
						$label_value = Localization::instance()->lang($label_code);
						if (is_null($label_value)) {
							$label_value = Localization::instance()->lang(str_replace('_', ' ', $label_code));
						}
						if (!is_null($label_value)) $label = $label_value;
					}
					echo $label;
				?>"><?php echo $label ?>:&nbsp;</td>
				<?php
					// dates are in standard format "Y-m-d H:i:s", must be formatted
					if ($customProp->getType() == 'date') {
						$dtv = DateTimeValueLib::dateFromFormatAndString("Y-m-d H:i:s", $cpv->getValue());
						$format = user_config_option('date_format');
						//Hook::fire("custom_property_date_format", null, $format);
						$value = $dtv->format($format);
					} else {
						$value = clean($cpv->getValue());
					}
					
					$title = '';
					$style = '';
					if ($customProp->getType() == 'contact' || $customProp->getType() == 'user'){
						$c = Contacts::findById($value);
						if($c instanceof Contact){
							$htmlValue = clean($c->getObjectName());
						}
						
					} else if ($customProp->getType() == 'list'){
						if ($customProp->getIsSpecial()) {
							$lang_value = Localization::instance()->lang($value);
							$htmlValue = is_null($lang_value) ? $value : $lang_value;
						}
						
					} else if ($customProp->getType() == 'boolean'){
						
						$htmlValue = '<div class="db-ico ico-'.($value?'complete':'delete').' '.($value?'cpbooltrue':'cpboolfalse').'">&nbsp;</div>';
						
					} else if ($customProp->getType() == 'table'){
					
						$headers = explode(',', $customProp->getValues());
						$rows = array();
						
						$cpvs = CustomPropertyValues::getCustomPropertyValues($__properties_object->getId(), $customProp->getId());
						foreach ($cpvs as $cpval) {
							$row = array();
							$values = str_replace("\|", "%%_PIPE_%%", $cpval->getValue());
							$exploded = explode("|", $values);
							foreach ($exploded as &$v) {
								$v = str_replace("%%_PIPE_%%", "|", $v);
								$v = escape_character($v);
								$row[] = $v;
							}
							$rows[] = $row;
						}
						
						$table_html = '<table class="og-add-custom-properties"><tr>';
						foreach ($headers as $h) $table_html .= '<th>'.$h.'</th>';
						$table_html .= '</tr>';
						
						foreach ($rows as $row) {
							$table_html .= '<tr>';
							foreach ($row as $rowval) $table_html .= '<td>'.$rowval.'</td>';
							$table_html .= '</tr>';
						}
						$table_html .= '</table>';
						
						$htmlValue = $table_html;
						
					} else if ($customProp->getType() == 'address'){
						
						$values = str_replace("\|", "%%_PIPE_%%", $cpv->getValue());
						$exploded = explode("|", $values);
						foreach ($exploded as &$v) {
							$v = str_replace("%%_PIPE_%%", "|", $v);
							$v = escape_character($v);
						}
						if (count($exploded) > 0) {
							$address_type = array_var($exploded, 0, '');
							$street = array_var($exploded, 1, '');
							$city = array_var($exploded, 2, '');
							$state = array_var($exploded, 3, '');
							$country = array_var($exploded, 4, '');
							$zip_code = array_var($exploded, 5, '');
							
							$out = $street;
							if($city != '') $out .= ' - ' . $city;
							if($zip_code != '') $out .= ' - ' . $zip_code;
							if($state != '') $out .= ' - ' . $state;
							if($country != '') $out .= ' - ' . lang("country $country");

							$htmlValue = '<div class="info-content-item">'. $out .'&nbsp;<a class="map-link coViewAction ico-map" href="http://maps.google.com/?q='. $out .'" target="_blank">'. lang('map') .'</a></div>';
						} else {
							$htmlValue = "";
						}
						
					} else if ($customProp->getIsMultipleValues()) {
						$multValues = CustomPropertyValues::getCustomPropertyValues($__properties_object->getId(), $customProp->getId());
						$newAlt = $alt;
						if ($customProp->getType() == 'table') {
							$htmlValue = '<table style="margin-bottom:2px">';
							$columnNames = explode(',', $customProp->getValues());
							$htmlValue .= '<tr class="' . ($newAlt ? 'altRow' : 'row') . '">';
							foreach ($columnNames as $colName) {
								$htmlValue .= '<th style="width:130px;font-weight:bold;text-align:center;">'.$colName.'</th>';
							}
							$htmlValue .= '</tr>';
							$newAlt = !$newAlt;
							foreach ($multValues as $mv){
								$value = str_replace('\|', '"%%_PIPE_%%"', $mv->getValue());
								$exploded = explode('|', $value);
								$htmlValue .= '<tr class="' . ($newAlt ? 'altRow' : 'row') . '">';
								foreach ($exploded as $col_val) {
									$col_val = str_replace('"%%_PIPE_%%"', '|', $col_val);
									$title =  (strlen($col_val) > 20) ? clean($col_val) : '';
									$showValue = clean($col_val);
									$htmlValue .= '<td style="padding:0px 5px;border-right:1px solid #DDD;" title="' . $title . '">' . $showValue . '</td>';
								}
								$htmlValue .= '</tr>';
								$newAlt = !$newAlt; 
							}
						} else {
							$htmlValue = '<table style="width:100%;margin-bottom:2px">';
							foreach ($multValues as $mv){
								$value = str_replace('\|', '"%%_PIPE_%%"', $mv->getValue());
								$value = str_replace('|', ',', $value);
								$value = str_replace('"%%_PIPE_%%"', '|', $value);
								$title =  (strlen($value) > 100 && $customProp->getType() != 'memo') ? clean(str_replace('|', ',', $value)) : '';
								$showValue = $customProp->getType() == 'memo' ? escape_html_whitespace(convert_to_links(clean($value))) : clean($value);
								$htmlValue .= '<tr class="' . ($newAlt ? 'altRow' : 'row') . '"><td style="padding:0px 5px" title="' . $title . '">' . $showValue . '</td></tr>';
								$newAlt = !$newAlt; 
							}
						}
						$htmlValue .= '</table>';
						$style = 'style="padding:1px 0px"';
					} else {
						$title =  (strlen($value) > 100 && $customProp->getType() != 'memo') ? clean($value) : '';
						$htmlValue = $customProp->getType() == 'memo' ? escape_html_whitespace(convert_to_links(clean($value))) : $value;
					}
				?>
				<td class="value" <?php echo $style ?> title="<?php echo $title?>"><?php echo $htmlValue ?></td>
			</tr>
		<?php } // if
	} // foreach ?>
</table>
<?php } // if

// Draw flexible custom properties
if (is_array($properties) && count($properties) > 0){ ?>
	<table class="og-custom-properties">
	<?php foreach ($properties as $prop) {?>
		<tr>
			<td class="name" title="<?php echo clean($prop->getPropertyName()) ?>"><?php echo clean($prop->getPropertyName()) ?>:&nbsp;</td>
			<td title="<?php echo clean($prop->getPropertyValue()) ?>"><?php echo clean($prop->getPropertyValue()) ?></td>
		</tr>
	<?php } // foreach ?>
	</table>
<?php } // if ?>
