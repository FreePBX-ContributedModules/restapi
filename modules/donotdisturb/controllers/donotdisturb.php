<?php

class restapi_Donotdisturb{
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
	* @return - a list of users' donotdisturb settings
	* @uri /donotdisturb/users
	*/
	function get_donotdisturb_users($params) {
		return  donotdisturb_get();	
	}	

	/**
	* @verb GET
	* @returns - a users' donotdisturb settings
	* @uri /donotdisturb/users/:id
	*/
	function get_donotdisturb_users_id($params) {
		return array('status' => donotdisturb_get($params['id']));
	}

	/**
	* @verb PUT
	* @uri /donotdisturb/users/:id
	*/
	function put_donotdisturb_users_id($params) {
		return donotdisturb_set($params['id'], $params['status']);
	}
}
