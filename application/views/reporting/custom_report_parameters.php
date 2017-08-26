<?php
	$genid = gen_id();
	$tiCount = 0;
	$firstId = '';
?>

<form style='height:100%;background-color:white' class="internalForm" action="<?php echo get_url('reporting', 'view_custom_report', array('id' => $id)) ?>" method="post">

<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
		<div class="coInputTitle"><?php echo $title ?></div>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">

	<div style="width:600px;padding-bottom:20px"><?php echo $description ?></div>

	<table>
		<tr>
			<td style="padding:0 10px 10px 10px;"><span class="bold"><?php echo lang('field') ?></span></td>
			<td style="text-align:center;padding:0 10px 10px 10px;"><span class="bold"><?php echo lang('condition') ?></span></td>
			<td style="padding:0 10px 10px 0px;"><span class="bold"><?php echo lang('value') ?></span></td>
		</tr>
		
		<?php
		$ot = ObjectTypes::findById($model);
		$model = $ot->getHandlerClass(); 
		foreach($conditions as $condition){
			if($condition->getCustomPropertyId() > 0){
				$cp = null;
				if ($ot instanceof ObjectType && in_array($ot->getType(), array('dimension_object','dimension_group'))) {
					if (Plugins::instance()->isActivePlugin('member_custom_properties')) {
						$cp = MemberCustomProperties::getCustomProperty($condition->getCustomPropertyId());
					}
				} else {
					$cp = CustomProperties::getCustomProperty($condition->getCustomPropertyId());
				}
				if (!$cp) continue;
				$name = $cp->getName();
				
			} else {
				$name = Localization::instance()->lang('field ' . $model . ' ' . $condition->getFieldName());
				if (!$name) {
					$name = lang('field Objects ' . $condition->getFieldName());
				}
			}
			$tiCount ++;
			?>
			<tr style='height:30px;'>
			<?php
				$condId = $genid . 'rpcond' . $condition->getId();
				if ($firstId == '') $firstId = $condId;
			?>
				<td style="padding:3px 0 0 10px;"><span class="bold"><?php echo $name ?>&nbsp;</span></td>
				<td style="text-align:center;padding:3px 0 0 0;"><?php 
					if ($condition->getCondition() == '%') {
						echo lang('ends with');
					} else {
						$cond_label = Localization::instance()->lang($condition->getCondition());
						if (!$cond_label) $cond_label = $condition->getCondition();
						echo $cond_label;
					}
				?>&nbsp;</td>
			<?php if(isset($cp)){ ?>
				<td align='left'>
					<?php if($cp->getType() == 'text' || $cp->getType() == 'numeric'){ ?>
						<input type="text" id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId()."_".clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>/>
					<?php }else if($cp->getType() == 'boolean'){  ?>
						<select id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId()."_".clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>>
							<option value="1" > <?php echo lang('true') ?>  </option>
							<option value="0" > <?php echo lang('false') ?> </option>
						</select>
					<?php }else if($cp->getType() == 'list'){  ?>
						<select id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId()."_".clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>>
						<?php foreach(explode(',', $cp->getValues()) as $value){ ?>
							<option value="<?php echo $value ?>"> <?php echo $value ?>  </option>
						<?php }//foreach ?>
						</select>
					<?php }else if($cp->getType() == 'date'){  ?>
						<?php echo pick_date_widget2("params[".$condition->getId()."_".clean($cp->getName())."]",$genid,$tiCount)?>
					<?php }?>
				</td>
			<?php }else{ ?>
				<td align='left'>
				<?php 
						$model_instance = new $model();
						$col_type = $model_instance->getColumnType($condition->getFieldName());
						
						if(in_array($condition->getFieldName(), array_keys($external_fields))){
				?>
						<select id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId() ?>]">
				<?php 		foreach($external_fields[$condition->getFieldName()] as $value){ ?>
								<option value="<?php echo $value['id'] ?>"><?php echo $value['name'] ?></option>
				<?php		} ?>
						</select>
				<?php 
						} else {
							if ($condition->getFieldName() == 'is_user') {
								$options = array(option_tag(lang('yes'), 1), option_tag(lang('no'), 0));
								echo select_box("params[".$condition->getId()."]", $options);
							} else  if ($col_type == DATA_TYPE_DATE || $col_type == DATA_TYPE_DATETIME) {
								echo pick_date_widget2("params[".$condition->getId()."]");
							} else {
								$already_rendered = false;
								Hook::fire('custom_report_param_render', array('field' => $condition, 'report' => null, 'report_ot' => $ot), $already_rendered);
								
								if (!$already_rendered) {
				?>
						<input type="text" id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId() ?>]" />
				<?php 			
								}
							}
						}
				?>
				</td>
			</tr>
		<?php
			}
			unset($cp);
		} //foreach ?>
	</table>
	
<?php echo submit_button(lang('generate report'),'s',array('tabindex' => $tiCount + 1))?>	
</div>

<script>
var firstCond = Ext.getDom('<?php echo $firstId ?>');
if (firstCond)
	firstCond.focus();
</script>

</form>