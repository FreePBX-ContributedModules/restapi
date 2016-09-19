<?php

class restapi_Conferences {
	private $api;

	function __construct($api) {
		$this->api = & $api;
	}

	function index() {
		return $this->get_conferences();
	}

	/**
	* @verb GET
	* @returns - the conference list
	* @uri /conferences
	*/
	function get_conferences($params) {
		$conferences = conferences_list();

		foreach($conferences as $conference) {
			$room = new stdClass();
			$room->id = $conference[0];
			$room->description = $conference[1];
			$list[$conference[0]] = $room;
		}

		return $list ? $list : false;
	}

	/**
	* @verb GET
	* @returns - a list of conference settings
	* @uri /conference/:id
	*/
	function get_conference_id($params) {
		$conference = conferences_get($params['id']);

		return $conference ? $conference : false;
	}

	/**
	* @verb DELETE
	* @returns - true if the conference was deleted, false otherwise
	* @uri /conference/:id
	*/
	function delete_conference_id($params)
	{
		return conferences_del($params['id']);
	}

	/**
	* @verb PUT
	* @uri /conference/:id
	*/
	function put_conference_id($params)
	{
		conferences_del($params['id']);
		return conferences_add($params["id"], $params["name"], $params["userpin"], $params["adminpin"], $params["options"], $params["joinmsg_id"], $params["music"], $params["users"]);
	}
}
