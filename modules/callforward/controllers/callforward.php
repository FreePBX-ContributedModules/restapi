<?php

class restapi_Callforward{
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
	 * @uri /callforward
	 */
	function get($parmas) {

	}

	/**
	 * @verb GET
	 * @return - a list of users' callforward settings
	 * @uri /callforward/users
	 */
	function get_callforward_users($params) {
		return callforward_get_extension();
	}

	/**
	 * @verb GET
	 * @returns - a users' callforward settings
	 * @uri /callforward/users/:id
	 */
	function get_callforward_users_id($params) {
		return callforward_get_extension($params['id']);
	}

	/**
	 * @verb GET
	 * @returns - a users' callforward settings
	 * @uri /callforward/users/:id/ringtimer
	 */
	function get_callforward_users_id_ringtimer($params) {
		return callforward_get_ringtimer($params['id']);
	}

	/**
	 * @verb PUT
	 * @uri /callforward/users/:id
	 */
	function put_callforward_users_id($params) {
		foreach (callforward_get_extension($params['id']) as $type => $value) {
			if (isset($params[$type])) {
				callforward_set_number($params['id'], $params[$type], $type);
			}
		}

		return true;
	}

	/**
	 * @verb PUT
	 * @uri /callforward/users/:id/ringtimer
	 */
	function put_callforward_users_id_ringtimer($params) {
		return callforward_set_ringtimer($params['id'], $params['ringtimer']);
	}
}
