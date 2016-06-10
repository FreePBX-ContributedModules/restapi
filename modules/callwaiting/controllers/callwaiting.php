<?php

class restapi_Callwaiting{
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
         * @uri /callwaiting
         */
        function get($parmas) {

        }

       /**
	* @verb GET
	* @return - a list of users' callwaiting settings
	* @uri /callwaiting/users
	*/
	function get_callwaiting_users($params) {
		return  callwaiting_get();	

	}	

        /**
         * @verb GET
         * @returns - a users' callwaiting settings
         * @uri /callwaiting/users/:id
         */
         function get_callwaiting_users_id($params) {
		return callwaiting_get($params['id']);
         }

	/**
         * @verb PUT
         * @uri /callwaiting/users/:id
         */
         function put_callwaiting_users_id($params) {
               	return callwaiting_set($params['id'], $params['state']);
         }
}
