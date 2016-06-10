<?php
/*
 * Returns a list of tokens for a specific user
 *
 * @param string $user - the user id (technicaly an int, but were untyped)
 *
 * @returns array - a list of tokens for the specific user
 */
function restapi_user_get_user_tokens($user) {
	global $db;
	$ret = array();

	$sql = 'SELECT token_id FROM restapi_token_user_mapping WHERE user = ?';
	$res = $db->getAll($sql, array($user));
	db_e($res);

	if ($res) {
		foreach ($res as $index => $array) {
			$ret[] = $array[0];
		}
	}

	return $ret ? $ret : array();
}

/*
 * Returns a list of tokens for a specific user
 *
 * @param string $extension - the extension of the user
 *
 * @returns array - a list of tokens for the specific user
 */
function restapi_user_get_user_tokens_by_extension($extension) {
    $ret = array();
    if(!function_exists('setup_userman')) {
        return $ret;
    }
    $user = setup_userman()->getUserByDefaultExtension($extension);
    if(empty($user)) {
        return $ret;
    }
    return restapi_user_get_user_tokens($user['id']);
}

/*
 * Returns a list of tokens for a specific user
 *
 * @param string $username - the username of the user
 *
 * @returns array - a list of tokens for the specific user
 */
function restapi_user_get_user_tokens_by_username($username) {
    $ret = array();
    if(!function_exists('setup_userman')) {
        return $ret;
    }
    $user = setup_userman()->getUserByUsername($username);
    if(empty($user)) {
        return $ret;
    }
    return restapi_user_get_user_tokens($user['id']);
}
/*
 * Returns the user for a given token
 *
 * @param int - token id
 *
 * @returns string - user
 */
function restapi_user_get_token_user($token) {
	global $db;

	$sql = 'SELECT user FROM restapi_token_user_mapping WHERE token_id = ?';
	$ret = $db->getOne($sql, array($token));
	db_e($ret);

	return $ret;
}

/*
 * Set a token to be mapped back to a user
 *
 * @param string - user
 * @param int - token id
 *
 * @returns true
 */
function restapi_user_set_token($user, $token) {
	global $db;

	//first, ensure no other user are associated with this token
	restapi_user_del_token($token);

	//then update
	$sql = 'INSERT INTO restapi_token_user_mapping (user, token_id) VALUES (?, ?)';
	$ret = $db->query($sql, array($user, $token));
	db_e($ret);

	return true;
}

/*
 * Deletes user token mapping
 *
 * @param int - token
 *
 * @returns true
 */
function restapi_user_del_token($token) {
	global $db;

	$sql = 'DELETE FROM restapi_token_user_mapping WHERE token_id = ?';
	$ret = $db->query($sql, array($token));
	db_e($ret);

	return true;
}
