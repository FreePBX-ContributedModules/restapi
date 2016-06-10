<?php

class restapi_Presencestate {
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
	* @uri /presencestate
	*/
        function get($params) {
	}

	/**
         * @verb GET
         * @returns - a list of presencestates
         * @uri /presencestate/list
         */
         function get_presencestate_list($params) {
		$presencestates = presencestate_list_get();

		return $presencestates ? $presencestates : false;
         }

        /**
         * @verb GET
         * @returns - a list of presencestate types
         * @uri /presencestate/types
         */
         function get_presencestate_types($params) {
		$types = presencestate_types_get();

		return $types ? $types : false;
         }

        /**
         * @verb GET
         * @returns - a users presencestate preferences
         * @uri /presencestate/prefs/:extension
         */
         function get_presencestate_prefs_extension($params) {
		$prefs = presencestate_prefs_get($params['extension']);

		return $prefs ? $prefs : false;
         }

	/**
         * @verb PUT
         * @uri /presencestate/prefs/:extension
         */
         function put_presencestate_prefs_extension($params) {
		return presencestate_prefs_set($params['extension'], $params);
         }
}
