<?php

class restapi_FaxUsers{
	private $api;
	
	function __construct($api) {
		$this->api = & $api;
	}
	
	function index() {
		return $this->get();
	}
	
	/**
	 * @verb GET
	 * @returns - a list of users' fax settings
	 * @uri /fax/users
	 */
	 function get_fax_users($params) {
		$users = array();
		foreach (fax_get_user() as $user) {
			$users[$user['user']] = $user;
			unset($users[$user['user']]['user']);
		}

		return $users ? $users : false;
	 }

	/**
	 * @verb GET
	 * @returns - a list of users' fax settings
	 * @uri /fax/users
	 */
	 function get_fax_users_id($params) {
		$users = fax_get_user($params['id']);
		if (isset($users['user'])) {
			unset($users['user']);
		}
		return $users ? $users : false;
	 }

	/**
	 * @verb PUT
	 * @uri /fax/users/:id
	 */
	 function put_fax_users_id($params) {
	 	$params['faxemail'] = isset($params['faxemail'])
	 		? $params['faxemail']
	 		: '';

	 	if (isset($params['id'], $params['faxenabled'])) {
	 		return fax_save_user(
	 					$params['id'], 
	 					$params['faxenabled'], 
	 					$params['faxemail']);
	 	} else {
	 		return false;
	 	}
	 }
}
