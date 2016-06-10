<?php
$html = '';

$html .= heading(_('REST API Log'), 3);
$html .= '<hr style="width:50%;margin-left:0"/>';
$html .= '<link rel="stylesheet" type="text/css" href="modules/restapi/assets/css/views/logs.css" />';
$html .= '<script type="text/javascript" src="modules/restapi/assets/js/views/logs.js"></script>';
$html .= '<link href="http://alexgorbatchev.com/pub/sh/current/styles/shThemeDefault.css" rel="stylesheet" type="text/css" />';
$html .= '<script src="http://alexgorbatchev.com/pub/sh/current/scripts/shCore.js" type="text/javascript"></script>';
$html .= '<script src="http://alexgorbatchev.com/pub/sh/current/scripts/shAutoloader.js" type="text/javascript"></script>';

		
if (!$logs) {
	$html .= _('No Data Found!');
	echo $html;
	exit();
}

$table = new CI_Table;
//set some classes for more controll in this table
$table->set_template(array(
					'table_open' => '<table id="log_table_parent">', 
					'row_start' => '<tr class="hover">',
					));
$table->set_heading('',
				_('ID'), 
				_('Time') . '<span class="localtime" data-done-text="' 
							. _('Localized') . '"> (' 
							. _('Localizing...') 
							. ')</span>', 
				_('Token'), 
				_('Signature'), 
				_('IP'), 
				_('Server'));

//iterate over log entires
foreach ($logs as $l) {
	$log = isset($log) ? false : true;
	
	$table->add_row(
					'<span class="show_event" data-id="' . $l['id'] . '">+</span>',
					$l['id'], 
					'<span class="log_raw_time" data-date="' . $l['time'] . '">' 
						. date('Y-m-d H:i:s', $l['time']) 
						. '</span>',
					'<span class="hashtr">' . $l['token'] . '</span>',
					'<span class="hashtr">' . $l['signature'] . '</span>',
					$l['ip'],
					'<span class="hashtr">' . $l['server'] . '</span>'
					);
					
	//iterate over events, if we have any
	if (isset($l['events']) && $l['events']) {
		$eTable = new CI_Table;
		$eTable->set_template(array('table_open' => '<table id="log_table_events">'));
		foreach ($l['events'] as $e) {
			$eTable->set_heading(
					_('Time'), 
					_('Event'), 
					_('Data') . '<span class="datahelp"> ('._('click on data to expand').')</span>'
			);
			$eTable->add_row(
						'<span class="log_raw_time" data-date="' . $e['time'] . '">' 
							. date('Y-m-d H:i:s', $e['time']) 
							. '</span>',
						'<span class="log_event" title="' . $e['trigger'] . '">' 
							. $e['event'] 
							. '</span>',
						//'<div class="event_data"><pre class="brush: php">' . var_export($e['data'], true) . '</pre></div>'
						'<div class="event_data" data-state="closed"><pre>' 
						. print_r($e['data'], true) . '</pre></div>'
			);
		}
		$table->add_row(array('data' => $eTable->generate(), 'colspan' => 10, 'class' => 'log_events event' . $e['e_id']));
	}
}

$html .= $table->generate();

$html .= '<script type="text/javascript">'
		. 'SyntaxHighlighter.autoloader("php http://alexgorbatchev.com/pub/sh/current/scripts/shBrushPhp.js");' 
		. 'SyntaxHighlighter.defaults["toolbar"] = false;'
		. 'SyntaxHighlighter.defaults["gutter"] = false;'
		. 'SyntaxHighlighter.all();</script>';
echo $html;	
?>
