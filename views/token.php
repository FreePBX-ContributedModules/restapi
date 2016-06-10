<?php
$html = '';

$html .= heading(_('REST API Token'), 3);
$html .= '<hr style="width:50%;margin-left:0"/>';
$html .= form_open($_SERVER['REQUEST_URI']);
$html .= form_hidden('action', 'save');
$html .= form_hidden('id', $id);
$html .= form_hidden('token', $token);
$html .= form_hidden('tokenkey', $tokenkey);

$table = new CI_Table;

//$table->add_row(array('data' => heading(_('General Options'), 5) . '<hr>', 'colspan' => 2));
//name
$label = fpbx_label(_('Name'));
$table->add_row($label, form_input('name', $name));

$label = fpbx_label(_('Description'));
$table->add_row($label, form_input('desc', $desc));

//user
$table->add_row(array('data' => heading(_('User'), 5) . '<hr>', 'colspan' => 2));
$label = fpbx_label(
	_('Associated User'),
	_('User this key should be associated with. Will show key on '
		. 'user page and will be visable to the user in the user portal. '
		. 'Key will be deleted if/when user is'
	)
);
$table->add_row($label, form_dropdown('assoc_user', $user_list_none, $assoc_user));

//auth
$table->add_row(array('data' => heading(_('Authentication'), 5) . '<hr>', 'colspan' => 2));

//token
$label = fpbx_label(_('Token'));
$table->add_row($label, $token);

//secret
$label = fpbx_label(_('Token Key'));
$table->add_row($label, $tokenkey);

//status
$label = fpbx_label(_('Status'));
$status = array(
			'enabled'	=> _('Enabled'),
			'disabled'	=> _('Disabled')
);
$table->add_row($label, form_dropdown('token_status', $status, $token_status));


//deny
$label = fpbx_label(_('Deny'), _('Gloabl IP address blacklist'));
$data = array(
		'name'			=> 'deny',
		'value'			=> implode("\n", $deny) . "\n",
		'placeholder'	=> 'NOT YET IMPLEMENTED',
		'cols'			=> 20,
		'rows'			=> count($deny) + 2
);
$table->add_row($label, form_textarea($data));

//allow
$label = fpbx_label(_('Allow'), _('Gloabl IP address whitelist'));
$data = array(
		'name'			=> 'allow',
		'value'			=> implode("\n", $allow) . "\n",
		'placeholder'	=> 'NOT YET IMPLEMENTED',
		'cols'			=> 20,
		'rows'			=> count($allow) + 2
);
$table->add_row($label, form_textarea($data));

//Authorization
$table->add_row(array('data' => heading(_('Authorization'), 5) . '<hr>', 'colspan' => 2));

//users
$label = fpbx_label(_('Users'), _('Users whos data this token may read and write'));
$table->add_row($label, form_multiselect('users[]', $core_user_list_all, $users));

//modules
$label = fpbx_label(_('Modules'), _('Modules this token may read and write'));
$table->add_row($label, form_multiselect('modules[]', $module_list, $modules));

//Accounting
$table->add_row(array('data' => heading(_('Accounting'), 5) . '<hr>', 'colspan' => 2));

//rate limits
$label = fpbx_label(_('Rate Limit'), _('Quantity of API requests this token can make per hour'));
$data = array(
		'name' 	=> 'rate',
		'value'	=> $rate,
		'type'	=> 'number',
		'max'	=> 1000
);
$table->add_row($label, form_input($data) . ' ' . _('requests per hour'));


$html .= $table->generate();

$html .= br(3);
$html .= form_submit('submit', _('Submit'));
if (isset($id)) {
	$html .= form_submit('submit', _('Delete'));
}

$html .= form_close();
//$html .= '<script type="text/javascript" src="modules/parking/assets/js/views/park.js"></script>';
echo $html;
?>
