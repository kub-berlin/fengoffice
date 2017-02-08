<?php
	$dimensions_info = array();
	$hidden_dim_ids = array();
	
	$enabled_dimensions = config_option('enabled_dimensions');
	$dimensions = Dimensions::findAll(array('conditions' => 'id IN ('.implode(',',$enabled_dimensions).') AND is_manageable=1'));
	foreach ($dimensions as $dimension) {
		if (in_array($dimension->getCode(), array('feng_users', 'feng_persons'))) continue;
		
		$hook_return = null;
		Hook::fire("hidden_breadcrumbs", array('ot_id' => $object->getObjectTypeId(), 'dim_id' => $dimension->getId()), $hook_return);
		if (!is_null($hook_return) && array_var($hook_return, 'hidden')) {
			$hidden_dim_ids[] = $dimension->getId();
			continue;
		}
		
		if (!isset($dimensions_info[$dimension->getName()])) {
			$dimensions_info[$dimension->getName()] = array('id' => $dimension->getId(), 'members' => array());
		}
	}
	
	$members = $object->getMembers();
	foreach ($members as $member) {
		/* @var $member Member */
		$dimension = $member->getDimension();
		if (in_array($dimension->getCode(), array('feng_users', 'feng_persons')) || !in_array($dimension->getId(), $enabled_dimensions) 
				|| !$dimension->getIsManageable() || in_array($dimension->getId(), $hidden_dim_ids)) {
			continue;
		}
		
		$obj_is_user = $object instanceof Contact && $object->isUser();
		
		if ($dimension->getDefinesPermissions() && !$obj_is_user && !can_read(logged_user(), array($member), $object->getObjectTypeId())) continue;
		
		if (!isset($dimensions_info[$dimension->getName()])) {
			$dimensions_info[$dimension->getName()] = array('members' => array(), 'icon' => $member->getIconClass());
		}
		if (!isset($dimensions_info[$dimension->getName()]['icon'])) {
			$dimensions_info[$dimension->getName()]['icon'] = $member->getIconClass();
		}
		$parents = array_reverse($member->getAllParentMembersInHierarchy(true));
		foreach ($parents as $p) {
			$dimensions_info[$dimension->getName()]['members'][$p->getId()] = array('p' => $p->getParentMemberId(), 'name' => $p->getName(), 'ot' => $p->getObjectTypeId(), 'color' => $p->getMemberColor());
		}
	}
	
	foreach ($dimensions_info as &$dim_info) {
		if (!isset($dim_info['icon'])) {
			$dots = DimensionObjectTypes::findAll(array('conditions' => 'dimension_id = '.$dim_info['id']));
			if (count($dots) > 0) {
				$ot = ObjectTypes::findById($dots[0]->getObjectTypeId());
				if ($ot instanceof ObjectType) $dim_info['icon'] = $ot->getIconClass();
			}
		}
	}
	
	$breadcrumb_member_count = user_config_option('breadcrumb_member_count');
	if (!$breadcrumb_member_count) $breadcrumb_member_count = 5;
	
	$width_style = ($object instanceof ProjectTask || $object instanceof TemplateTask) ? "width:50%;" : "width:100%;";
	
	if (count($dimensions_info) > 0) {
		ksort($dimensions_info, SORT_STRING);
?>
<div class="commentsTitle"><?php echo lang('related to')?></div>
	<div style="padding-bottom: 10px;">
	<div style="<?php echo $width_style?> float: left; overflow: hidden;" class="object-view-member-path-container">
	
		<table style="width:100%;">
<?php
		$member_path = $object->getMembersIdsToDisplayPath();

		foreach ($dimensions_info as $dname => $dinfo) {
			$dim_name = $dname;
			Hook::fire("edit_dimension_name", array('dimension' => $dinfo['id']), $dim_name);
			?>
			<tr class="member-path-dim-block">
				<td style="width: 200px; height:25px;">
					<div class="dname coViewAction <?php echo array_var($dinfo, 'icon')?>"><?php echo $dim_name?>:&nbsp;</div>
				</td>
				<td>
		<?php 
			if (array_var($member_path, $dinfo['id'])) {
				$dim_mem_path = array($dinfo['id'] => array_var($member_path, $dinfo['id']));
		?>
					<div class='breadcrumb-container' style='max-width:800px; width:100%;' id="breadcrumb-container-<?php echo $dinfo['id']?>">
						<script>
						
							var dim_mem_path = '<?php echo json_encode($dim_mem_path)?>';
							var mpath = null;
							if (dim_mem_path){
								mpath = Ext.util.JSON.decode(dim_mem_path);
							}
							var mem_path = "";			
							if (mpath){
								mem_path = og.getEmptyCrumbHtml(mpath, '.breadcrumb-container');
							}
							$("#breadcrumb-container-<?php echo $dinfo['id']?>").html(mem_path);
						
						</script>
					</div>
				</td>
			</tr>
		<?php
			}
			
		//	$ret=null; Hook::fire('object_view_member_path_dims', $object, $ret);
		}
		?>
		</table>
		<?php
		
	?></div>
	<?php 
		if($object instanceof ProjectTask || $object instanceof TemplateTask) {
		?><div style="width:50%; float: left; "><?php 
			
			$task_list = $object;
			//milestone
			if (isset($milestone)){
				echo $milestone;
			}
			
			//parent
			if (isset($parentInf)){
				echo $parentInf;
			}
				
		}
		?></div><?php 
		?>
	
	
	</div>
	<div class="clear"></div>
		
	
	<script>
	$(function() {
		// set max breadcrumb width
		<?php foreach ($dimensions_info as $dname => $dinfo) { ?>
			$("#breadcrumb-container-<?php echo $dinfo['id']?>").css('max-width', ($("#breadcrumb-container-<?php echo $dinfo['id']?>").parent().width()-10)+'px');
		<?php } ?>
		// draw breadcrumbs
		og.eventManager.fireEvent('replace all empty breadcrumb', null);
	});
	</script>
	<?php
	}