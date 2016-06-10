<?php

class restapi_Voicemail {
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
	 * @uri /voicemail
	 */
	function get($parmas) {

	}

	/**
	 * @verb GET
	 * @returns - a mailbox resource
	 * @uri /voicemail/mailboxes/:id
	 */
	function get_mailbox_id($params) {
		return voicemail_mailbox_get($params['id']);
	}

	/**
	 * @verb PUT
	 * @uri /voicemail/password/:id
	 */
	function put_password_id($params) {
		if (!isset($params['password'])) {
			return false;
		}

		$uservm = voicemail_getVoicemail();
		$vmcontexts = array_keys($uservm);

		foreach ($vmcontexts as $vmcontext) {
			if(isset($uservm[$vmcontext][$params['id']])) {
				global $astman;

				$uservm[$vmcontext][$params['id']]['pwd'] = $params['password'];

				voicemail_saveVoicemail($uservm);

				$astman->send_request("Command", array("Command" => "voicemail reload"));

				return true;
			}
		}

		return false;
	}
}
