<?php

class restapi_Core {
	private $api;

	function __construct($api) {
		$this->api = & $api;
	}

	function index() {
		$this->get();
	}

	/**
	 * @verb GET
	 * @return
	 * @uri /core
	 */
	function get($parmas) {

	}

	/**
	 * @verb GET
	 * @return - a list of users
	 * @uri /core/users
	 */
	function get_users() {
		return core_users_list();
	}

	/**
	 * @verb GET
	 * @returns - a user resource
	 * @uri /core/users/:id
	 */
	function get_user_id($params) {
		$base = core_users_get($params['id']);

		// Now, find their voicemail information.
		$z = file("/etc/asterisk/voicemail.conf");
		foreach ($z as $line) {
			$res = explode("=>", $line);
			if (!isset($res[1]))
				continue;

			if (trim($res[0]) == trim($params['id'])) {
				$base['vm'] = trim($res[1]);
				return $base;
			}
		}

		// No voicemail found.
		return $base;
	}
}
