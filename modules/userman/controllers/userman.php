<?php

class restapi_Userman {
	private $api;
	
	function __construct($api) {
		$this->api = & $api;
	}	
	
	function index() {
		return $this->get();
	}

	/**
	 * @verb GET
	 * @returns 
	 * @uri /userman
	 */
	function get($params) {

	}

	/**
	 * @verb GET
	 * @returns - list of users
	 * @uri /userman/users
	 */
	function get_users($params) {
		$userman = setup_userman();
		if ($userman) {
			$users = $userman->getAllUsers();
			foreach ($users as $user) {
				$user['assigned'] = $userman->getAssignedDevices($user['id']);

				$list[] = $user;
			}
			return $list;
		}

		return false;
	}

	/**
	 * @verb GET
	 * @returns - a userman user
	 * @uri /userman/users/:id
	 */
	function get_user_id($params) {
		if ($params['id'] == 'none') {
			/* Don't do that. */
			return false;
		}

		$userman = setup_userman();
		if ($userman) {
			$user = $userman->getUserByUsername($params['id']);
			if ($user) {
				$user['assigned'] = $userman->getAssignedDevices($user['id']);
			}

			return $user;
		}

		return false;
	}

	/**
	 * @verb GET
	 * @returns - list of extensions
	 * @uri /userman/extensions
	 */
	function get_extensions($params) {
		$userman = setup_userman();
		if ($userman) {
			$users = $userman->getAllUsers();
			foreach ($users as $user) {
				if ($user['default_extension'] == NULL || $user['default_extension'] == 'none') {
					continue;
				}

				$list[$user['default_extension']] = array(
					"id" => $user['id'],
					"username" => $user['username'],
					"description" => $user['description']
				);
			}
			return $list;
		}

		return false;
	}

	/**
	 * @verb GET
	 * @returns - a userman user
	 * @uri /userman/extensions/:id
	 */
	function get_extension_id($params) {
		if ($params['id'] == 'none') {
			/* Don't do that. */
			return false;
		}

		$userman = setup_userman();
		if ($userman) {
			$user = $userman->getUserByDefaultExtension($params['id']);
			if ($user) {
				$user['assigned'] = $userman->getAssignedDevices($user['id']);
			}

			return $user;
		}

		return false;
	}
}
