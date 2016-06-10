<?php
$vars = array(
			'status'		=> '',
			'logging'		=> '',
			'action'		=> ''
);

foreach ($vars as $k => $v) {
	$vars[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}

if(isset($vars['action'])) {
	switch ($vars['action']) {
		case 'save_general':
			restapi_opts_put($vars);
			break;
		default:
			break;
	}
}

$vars = array_merge($vars, restapi_opts_get());

//if we dont have tokens, create new ones
if (!$vars['token'] && !$vars['tokenkey']) {
	$vars['token']		= restapi_tokens_generate();
	$vars['tokenkey']	= restapi_tokens_generate();
}

?>
<div class="container-fluid">
	<div class="row">
		<div class="col-sm-12">
			<h1><?php echo _('Rest API')?></h1>
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="panel-title">
						<a href="#" data-toggle="collapse" data-target="#moreinfo" class="collapsed" aria-expanded="false"><i class="glyphicon glyphicon-info-sign"></i></a>&nbsp;&nbsp;&nbsp;<?php echo _('What is Rest API')?></div>
				</div>
				<!--At some point we can probably kill this... Maybe make is a 1 time panel that may be dismissed-->
				<div class="panel-body collapse" id="moreinfo" aria-expanded="false" style="height: 30px;">
					<p><?php echo _('Rest API is used to manage API tokens for use in modules such as Rest Apps for your phones. From here you can edit general settings.')?></p>
				</div>
			</div>
			<div class="fpbx-container">
				<div class="display full-border">
					<form action="?display=restapi&amp;action=save_general" method="post" class="fpbx-submit">
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="row">
										<div class="form-group">
											<div class="col-md-3">
												<label class="control-label" for="element1"><?php echo _('Server Status')?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="element1"></i>
											</div>
											<div class="col-md-9">
												<select id="status" name="status" class="form-control">
													<option value="normal" <?php echo $vars['status'] == "normal" ? "selected" : ""?>><?php echo _('Enabled')?></option>
													<option value="tempdown" <?php echo $vars['status'] == "tempdown" ? "selected" : ""?>><?php echo _('Temporarly Disabed')?></option>
													<option value="maint" <?php echo $vars['status'] == "maint" ? "selected" : ""?>><?php echo _('Down for Maintence')?></option>
													<option value="disabled" <?php echo $vars['status'] == "disabled" ? "selected" : ""?>><?php echo _('Disabled')?></option>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="element1-help" class="help-block fpbx-help-block"><?php echo _('Desired status of the server')?></span>
								</div>
							</div>
						</div>
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="row">
										<div class="form-group">
											<div class="col-md-3">
												<label class="control-label" for="element2"><?php echo _('Server Token')?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="element2"></i>
											</div>
											<div class="col-md-9"><?php echo $vars['token']?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="element2-help" class="help-block fpbx-help-block"><?php echo _('Server Token')?></span>
								</div>
							</div>
						</div>
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="row">
										<div class="form-group">
											<div class="col-md-3">
												<label class="control-label" for="element3"><?php echo _('Server Token Key')?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="element3"></i>
											</div>
											<div class="col-md-9"><?php echo $vars['tokenkey']?></div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="element3-help" class="help-block fpbx-help-block"><?php echo _('Server Token Key')?></span>
								</div>
							</div>
						</div>
						<div class="element-container">
							<div class="row">
								<div class="col-md-12">
									<div class="row">
										<div class="form-group">
											<div class="col-md-3">
												<label class="control-label" for="element4"><?php echo _('Request Logging')?></label>
												<i class="fa fa-question-circle fpbx-help-icon" data-for="element4"></i>
											</div>
											<div class="col-md-9">
												<select id="logging" name="logging" class="form-control">
													<option value="enabled" <?php echo $vars['logging'] == "enabled" ? "selected" : ""?>><?php echo _('Enabled')?></option>
													<option value="disabled" <?php echo $vars['logging'] == "disabled" ? "selected" : ""?>><?php echo _('Disabled')?></option>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<span id="element4-help" class="help-block fpbx-help-block"><?php echo _('Whether to log requests to/from the server. This can be a very large amount of data.')?></span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
