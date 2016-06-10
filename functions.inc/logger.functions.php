<?php

/**
 * Fetch api call logs
 */
function restapi_logger_get($start = 0, $limit = 24, $order = 'DESC', $details = false) {
	global $db;

	//some sanitization - we cant rely on pear here as it will escape the commands
	$start = ctype_digit($start) ? $start : 0;
	$limit = ctype_digit($limit) ? $limit : 24;
	$order = in_array($order, array('DESC', 'ASC')) ? $order : 'DESC';
	
	$sql = 'SELECT * FROM restapi_log_events  ORDER BY time '
			. $order
			. ' LIMIT ' . $start . ', ' . $limit;
	$ret = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	db_e($ret);
	
	if ($details) {
		foreach ($ret as $my => $log) {
			$sql = 'SELECT * FROM restapi_log_event_details WHERE e_id = ?';
			$det = $db->getAll($sql, array($log['id']), DB_FETCHMODE_ASSOC);
			$ret[$my]['id'] = restapi_logger_format_id($ret[$my]['id']);	
			foreach ($det as $that => $d) {
				$det[$that]['e_id'] = $ret[$my]['id'];
				$det[$that]['data'] = json_decode($d['data'], true);
			}
			
			$ret[$my]['events'] = $det;
		}
		
	}
	return $ret;
}

/**
 * Log events
 * @param int
 * @param array
 *
 */
function restapi_logger_put_events($id, $log) {
	global $db;
	$data = array();

	foreach ($log as $l) {
		$data[] = array($id, 
					$l['time'], 
					$l['event'], 
					json_encode($l['data']), 
					$l['trigger']);
	}

	$sql = $db->prepare('INSERT INTO restapi_log_event_details (e_id, time, event, data, `trigger`) '
			. 'VALUES (?, ?, ?, ?, ?)');
	$res = $db->executeMultiple($sql, $data);
	db_e($res);
}

/**
 * Insert a log header and return its id
 * @param array
 * @return int
 *
 */
function restapi_logger_put_header($data) {
	global $db, $amp_conf;
	
	$sql = 'INSERT INTO restapi_log_events (time, token, signature, ip, server)'
			. ' VALUES (?, ?, ?, ?, ?)';
	$res = $db->query($sql, array(time(), $data['token'], $data['sig'], $data['ip'], $data['server']));
	db_e($res);
	
	$sql = ($amp_conf["AMPDBENGINE"] == "sqlite3") 
			? 'SELECT last_insert_rowid()' 
			: 'SELECT LAST_INSERT_ID()';
	return $db->getOne($sql);
}
/**
 * Check if a signature is in the database ye
 * As we acutally insert the signature in the db right before this is caclled,
 * we should excpect this signature to be in the db already.
 * Hence, if count === 1, we return true
 * @param string
 * @return bool
 *
 */
function restapi_get_signature($sig) {
	global $db;
	$sql = 'SELECT count(*) FROM restapi_log_events WHERE signature = ?';
	$ret = $db->getOne($sql, array($sig));
	db_e($ret);

	return (int)$ret === 1;
}

/**
 * Get count of requests by a token since $time
 *
 * @param string - token
 * @param return int
 */
function restapi_get_token_usage($token, $time) {
	global $db;
	
	$sql = 'SELECT COUNT(*) FROM restapi_log_events WHERE token = ? AND time > ?';
	$ret = $db->getOne($sql, array($token, $time));
	db_e($ret);
	
	return $ret;
}

function restapi_logger_format_id($id) {
	return sprintf('%u', crc32($id));
}

function restapi_logger_clear_history() {
	global $db;

	/* 30 days ago */
	$oldtime = time() - 2592000;

	$sql = 'DELETE e, ed FROM restapi_log_events AS e LEFT JOIN restapi_log_event_details AS ed on e.id = ed.e_id WHERE e.time < ?';
	$res = $db->query($sql, array($oldtime));
	db_e($res);
}
?>
