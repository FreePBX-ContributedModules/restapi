<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

$dir = dirname(__FILE__);

include($dir . '/functions.inc/api.class.php');
include($dir . '/functions.inc/auth.class.php');
include($dir . '/functions.inc/logger.class.php');
include($dir . '/functions.inc/logger.functions.php');
include($dir . '/functions.inc/router.class.php');
include($dir . '/functions.inc/user.functions.php');
include($dir . '/functions.inc/helper.functions.php');

function restapi_opts_get() {
	global $db;

	$sql = 'SELECT * FROM restapi_general';
	$ret = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	db_e($ret);

	foreach ($ret as $r) {
		$res[$r['keyword']] = $r['value'];
	}
	$vars = array(
		'allow'		=> '',
		'deny'		=> '',
		'status'	=> 'enabled',
		'token'		=> '',
		'tokenkey'	=> '',
		'logging'	=> 'disabled'
	);

	foreach ($vars as $k => $v) {
		$vars[$k] = isset($res[$k]) ? $res[$k] : $v;
	}

	return $vars;
}

function restapi_opts_put($vars) {
	global $db;

	//never accept a new token or key
	$orig = restapi_opts_get();
	if ($orig['token'] && $orig['tokenkey']) {
		$vars['token']		= $orig['token'];
		$vars['tokenkey']	= $orig['tokenkey'];
	}

	foreach ($vars as $k => $v) {
		switch ($k) {
			case 'status':
			case 'token':
			case 'tokenkey':
			case 'logging':
				$data[] = array($k, $v);
				break;
		}
	}

	$sql = $db->prepare('REPLACE INTO restapi_general (keyword, value) VALUES (?, ?)');
	$ret = $db->executeMultiple($sql, $data);
	db_e($ret);

	return true;
}

/**
 * Get token data
 * @param mixed - id to filter by, leave blank for default/generic data
 * @param options string - type to stort by
 * 			defaults to id, can be set to token for single entry lookups
 */
function restapi_tokens_get($id = '', $type = 'id') {
	global $db;
	switch ($id) {
		case 'all':
		case 'all_detailed':
			$tokens = array();
			$ret = sql('SELECT * FROM restapi_tokens', 'getAll', DB_FETCHMODE_ASSOC);
			foreach ($ret as $r) {
				if ($id == 'all_detailed') {
					$tokens[$r['id']] = restapi_tokens_get($r['id']);
				} else {
					$tokens[$r['id']] = $r;
				}
			}
			return $tokens;
			break;
		default:
			if ($type == 'token') {
				$sql = 'SELECT token_id FROM restapi_token_details WHERE `key` = "token" AND value = ?';
				$id = $db->getOne($sql, array($id));
				db_e($id);
				if (!$id) {
					return false;
				}
			}

			//if called with no id, we might be after a blank dataset. No need to hit the db to
			//find out that a blank string doesnt exists :)
			if ($id) {
				$sql = 'SELECT * FROM restapi_tokens WHERE id = ?';
				$ret = $db->getAll($sql, array($id), DB_FETCHMODE_ASSOC);
				db_e($ret);

				if (count($ret)) {
					$ret = $ret[0];
				}

				if ($ret) {
					$sql = 'SELECT * FROM restapi_token_details WHERE token_id = ?';
					$ret2 = $db->getAll($sql, array($id), DB_FETCHMODE_ASSOC);
					db_e($ret2);

					if (count($ret2)) {
						foreach ($ret2 as $r) {
							$ret[$r['key']] = $r['value'];
						}

					}
				}

				//get associated user
				$ret['assoc_user'] = restapi_user_get_token_user($id);
			}


			$vars = array(
					'allow'			=> json_encode(array('0.0.0.0')),
					'assoc_user'	=> '',
					'deny'			=> json_encode(array('0.0.0.0')),
					'desc'			=> '',
					'id'			=> '',
					'modules'		=> array(),
					'name'			=> '',
					'rate'			=> '1000',
					'token'			=> '',
					'tokenkey'		=> '',
					'token_status'	=> 'enabled',
					'users'			=> ''
			);

			foreach ($vars as $k => $v) {
				$vars[$k] = isset($ret[$k]) ? $ret[$k] : $v;
			}

			//decode strings stored as json
			$decode = array(
					'allow',
					'deny',
					'users',
					'modules'
			);
			foreach ($decode as $d) {
				$vars[$d] = isset($ret[$d]) ? json_decode($ret[$d], true) : array();
			}

			return $vars;
			break;
	}


}

function restapi_tokens_put($vars) {
	global $db, $amp_conf;

	//reuse old token/key on non-new tokens
	if ($vars['id']) {
		$orig = restapi_tokens_get($vars['id']);
		$vars['token']		= $orig['token'];
		$vars['tokenkey']	= $orig['tokenkey'];
	}

	//insert headers
	$sql = 'REPLACE INTO restapi_tokens (id, name, `desc`) VALUES (?, ?, ?)';
	$ret = $db->query($sql, array($vars['id'], $vars['name'], $vars['desc']));
	db_e($ret);

	//get an id if we dont alredy have one
	if (empty($vars['id'])) {
		$vars['id'] = $db->getOne(
						($amp_conf["AMPDBENGINE"] == "sqlite3")
						? 'SELECT last_insert_rowid()'
						: 'SELECT LAST_INSERT_ID()'
					);
	}

	//clear stale data
	$sql = 'DELETE FROM restapi_token_details WHERE token_id = ?';
	$ret = $db->query($sql, array($vars['id']));
	//dbug($vars['id'], $db->last_query);
	db_e($ret);

	//insert fresh values
	foreach ($vars as $k => $v) {
		switch ($k) {
			case 'allow':
			case 'deny':
				//TODO: validate ip's
				$data[] = array($vars['id'], $k, json_encode($v));
				break;
			case 'users':
				//TODO: validate that users really exist
				$data[] = array($vars['id'], $k, json_encode($v));
				break;
			case 'modules':
				//TODO: validate that modules really exist
				$modules = is_array($v) ? $v : array();
				if (in_array('*', $modules)) {
					$modules = array('*');
				}
				$data[] = array($vars['id'], $k, json_encode($modules));
				break;
			case 'token':
			case 'tokenkey':
			case 'token_status':
			case 'rate':
				$data[] = array($vars['id'], $k, $v);
				break;
			default:
				break;
		}
	}

	//insert fresh data
	$sql = $db->prepare('INSERT INTO restapi_token_details (token_id, `key`, value) VALUES (?, ?, ?)');
	$ret = $db->executeMultiple($sql, $data);
	db_e($ret);

	//update user mappings
	if ($vars['assoc_user']) {
		restapi_user_set_token($vars['assoc_user'], $vars['id']);
	}

	return $vars['id'];
}

function restapi_tokens_del($id) {
	global $db;

	//delete token
	$sql = 'DELETE FROM restapi_tokens WHERE id = ?';
	$ret = $db->query($sql, array($id));
	db_e($ret);

	$sql = 'DELETE FROM restapi_token_details WHERE token_id = ?';
	$ret = $db->query($sql, array($id));
	db_e($ret);

	//delete user mapping
	restapi_user_del_token($id);
	return '';
}

/**
 * @return a token
 */
function restapi_tokens_generate() {
	return sha1(microtime(true) . mt_rand(1000,9999999));
}
