<?php
$html = '';

$html .= heading(_('Tokens'), 2);
$html .= '<div id="line"><div class="spacer"></div><div class="spacer"></div></div>';

if (!$tokens) {
	$html .= _('No tokens associated with this user');
} else {

	$html .= form_open($_SERVER['PHP_SELF'] 
			. '?' . $_SERVER['QUERY_STRING']);

	$html .= form_hidden('f', 'action');
	$html .= form_hidden('m', 'restapi');
	$html .= form_hidden('action', 'save');
	
	
	$table = new CI_Table;
	
	foreach ($tokens as $token) {
		$t = restapi_tokens_get($token);
		$name = $t['name'] ? $t['name'] : _('Token') . ' ' . $token;
		$label = fpbx_label($name, $t['desc']);
		$status_opts = array(
					'enabled'	=> _('Enabled'),
					'disabled'	=> _('Disabled')
		);
		$status = form_dropdown('token_status[' . $token . ']', $status_opts, $t['token_status']);

		$table->add_row(array('data' => heading($label, 5) . '<hr>', 'colspan' => 2));
		$table->add_row(_('Token'), $t['token']);
		$table->add_row(_('Token Key'), $t['tokenkey']);
		$table->add_row(_('Status'), $status);
	}
	

	$table->add_row(form_submit('save', _('Save')));
	$html .= $table->generate();
	$html .= form_close() . br();
}

echo $html;
?>
