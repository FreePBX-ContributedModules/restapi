<div class="panel panel-info">
	<div class="panel-heading">
		<div class="panel-title">
			<a href="#" data-toggle="collapse" data-target="#moreinfo-restapi"><i class="glyphicon glyphicon-info-sign"></i></a>&nbsp;&nbsp;&nbsp;<?php echo _("What is RestAPI")?>
		</div>
	</div>
	<!--At some point we can probably kill this... Maybe make is a 1 time panel that may be dismissed-->
	<div class="panel-body collapse" id="moreinfo-restapi">
		<p><?php echo _('This section is used in conjunction with the RESTful Phone Apps. The RESTful Phone Apps module gives you access to a range of Phone side applications. It requires the Commercial End Point Manager to configure your devices as it needs to receive data from EPM for most applications to work correctly. ')?></p>
	</div>
</div>
<?php foreach($tokens as $token) { ?>
	<input type="hidden" value="<?php echo $token['token']?>" name="<?php echo 'restapi_'.$token['id'].'_token'?>" id="<?php echo 'restapi_'.$token['id'].'_token'?>">
	<input type="hidden" value="<?php echo $token['tokenkey']?>" name="<?php echo 'restapi_'.$token['id'].'_tokenkey'?>" id="<?php echo 'restapi|'.$token['id'].'_tokenkey'?>">
	<input type="hidden" value="<?php echo $token['name']?>" name="<?php echo 'restapi_'.$token['id'].'_name'?>" id="<?php echo 'restapi_'.$token['id'].'_name'?>">
	<input type="hidden" value="<?php echo $token['desc']?>" name="<?php echo 'restapi_'.$token['id'].'_desc'?>" id="<?php echo 'restapi_'.$token['id'].'_desc'?>">
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="<?php echo 'restapi_'.$token['id'].'_token_status'?>"><?php echo _('Enabled')?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo 'restapi_'.$token['id'].'_token_status'?>"></i>
						</div>
						<div class="col-md-9">
							<span class="radioset">
								<input type="radio" class="restapi_token_toggle" data-for="<?php echo $token['id']?>" name="<?php echo 'restapi_'.$token['id'].'_token_status'?>" value='enabled' id="<?php echo 'restapi_'.$token['id'].'_token_status'?>_enabled" <?php  echo ($enabled) ? 'checked' : ''?>>
								<label for="<?php echo 'restapi_'.$token['id'].'_token_status'?>_enabled"><?php echo _('Yes')?></label>
								<input type="radio" class="restapi_token_toggle" data-for="<?php echo $token['id']?>" name="<?php echo 'restapi_'.$token['id'].'_token_status'?>" value='disabled' id="<?php echo 'restapi_'.$token['id'].'_token_status'?>_disabled" <?php echo (!is_null($enabled) && !$enabled) ? 'checked' : ''?>>
								<label for="<?php echo 'restapi_'.$token['id'].'_token_status'?>_disabled"><?php echo _('No')?></label>
								<?php if($mode == "user") {?>
									<input type="radio" class="restapi_token_toggle" data-for="<?php echo $token['id']?>" name="<?php echo 'restapi_'.$token['id'].'_token_status'?>" value='inherit' id="<?php echo 'restapi_'.$token['id'].'_token_status'?>_inherit" <?php echo is_null($enabled) ? 'checked' : ''?>>
									<label for="<?php echo 'restapi_'.$token['id'].'_token_status'?>_inherit"><?php echo _('Inherit')?></label>
								<?php } ?>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="<?php echo 'restapi_'.$token['id'].'_token_status'?>-help" class="help-block fpbx-help-block"><?php echo _("Enable RestAPI for this user")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="<?php echo 'restapi_'.$token['id'].'_users[]'?>"><?php echo _('Extensions')?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo 'restapi_'.$token['id'].'_users'?>"></i>
						</div>
						<div class="col-md-9">
							<?php $token['users'] = !empty($token['users']) ? $token['users'] : array(0 => '*');?>
							<select id="restapi_<?php echo $token['id']?>_users" class="form-control chosenmultiselect restapi_<?php echo $token['id']?>" name="restapi_<?php echo $token['id']?>_users[]" multiple="multiple" <?php echo empty($enabled) ? 'disabled' : ''?>>
								<?php foreach($user_list_all as $key => $value) {?>
									<option value="<?php echo $key?>" <?php echo in_array($key,$token['users']) ? 'selected' : '' ?>><?php echo $value?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="<?php echo 'restapi_'.$token['id'].'_users'?>-help" class="help-block fpbx-help-block"><?php echo _("Which extensions this user can control")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="<?php echo 'restapi_'.$token['id'].'_modules[]'?>"><?php echo _('Modules')?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo 'restapi_'.$token['id'].'_modules'?>"></i>
						</div>
						<div class="col-md-9">
							<?php $token['modules'] = !empty($token['modules']) ? $token['modules'] : array(0 => '*');?>
							<select id="restapi_<?php echo $token['id']?>_modules" class="bsmultiselect restapi_<?php echo $token['id']?>" name="restapi_<?php echo $token['id']?>_modules[]" multiple="multiple" <?php echo empty($enabled) ? 'disabled' : ''?>>
								<?php foreach($module_list as $key => $value) {?>
									<option value="<?php echo $key?>" <?php echo in_array($key,$token['modules']) ? 'selected' : '' ?>><?php echo $value?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="<?php echo 'restapi_'.$token['id'].'_modules'?>-help" class="help-block fpbx-help-block"><?php echo _("Which modules this user can control")?></span>
			</div>
		</div>
	</div>
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="<?php echo 'restapi_'.$token['id'].'_rate'?>"><?php echo _("Rate Limit")?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo 'restapi_'.$token['id'].'_rate'?>"></i>
						</div>
						<div class="col-md-9">
							<input type="number" max="1000" class="form-control restapi_<?php echo $token['id']?>" value="<?php echo $token['rate']?>" name="<?php echo 'restapi_'.$token['id'].'_rate'?>" <?php echo empty($enabled) ? 'disabled' : ''?>>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="<?php echo 'restapi_'.$token['id'].'_rate'?>-help" class="help-block fpbx-help-block"><?php echo _("Quantity of API requests this token can make per hour")?></span>
			</div>
		</div>
	</div>
	<hr>
<?php } ?>
<script>
	$(".restapi_token_toggle").change(function() {
		var id = $(this).data("for");
		if($(this).val() == "enabled") {
			$(".restapi_" + id).prop("disabled", false).trigger("chosen:updated");
			$('select[multiple].bsmultiselect.restapi_'+id).multiselect('enable');
		} else {
			$(".restapi_" + id).prop("disabled", true).trigger("chosen:updated");
			$('select[multiple].bsmultiselect.restapi_'+id).multiselect('disable');
		}
	})
</script>
