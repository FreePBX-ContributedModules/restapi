<?php

class restapi_Restapi {
	private $api;
    	
	function __construct($api) {
		$this->api = & $api;
	}	
	
	function get_restapi_tokens_search($params) {
		if (!isset($params['user'], $params['hash'], $params['nonce'])) {
			return array('status' => 'error', 'msg' => 'Invalid params');
		}
		$user = core_users_get($params['user']);
		//password MUST be set
		if (!$user['password']) {
			return false;
		}
		
		//password must match!
		//note: we send a sha1+nonce to prevent password snooping. nonce is the 
		//time, and must match the servers time  within a 10 minute frame
		$time = time();
		$time_frame = 5 * 60;

		if ($params['nonce'] < $time - $time_frame 
			|| $params['nonce'] > $time + $time_frame
		) {
			return false;
		}

		if ($params['hash'] 
			!= hash('sha256', $user['password'] . $params['nonce'])
		) {
			return false;
		}
		
		//seems  this request is legit. Lets see if we have any tokens for 
		//this user
		$t = restapi_user_get_user_tokens($params['user']);
		
		if (!isset($t[0])) {
			return array('status' => 'error', 'msg' => 'No tokens found!');
		} else {
			return restapi_tokens_get($t[0]);		
		}
	}
}
