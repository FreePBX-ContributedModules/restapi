<?php

class restapi_Timeconditions {
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
	* @uri /timeconditions
	*/
        function get($params) {
        
	}

	/**
         * @verb GET
         * @returns - a list of timeconditions settings
         * @uri /timeconditions
         */
         function get_timeconditions($params) {
		$timeconditions = timeconditions_list();

                return $timeconditions ? $timeconditions : false;
         }

        /**
         * @verb GET
         * @returns - timeconditions state
         * @uri /timeconditions/:id
         */
         function get_timeconditions_id($params) {
		$tcstate = timeconditions_get_state($params['id']);
		if ($tcstate !== false) {
			$timeconditions = array();
			$timeconditions['state'] = $tcstate;
		}

		return $timeconditions ? $timeconditions : false;
         }

	/**
         * @verb PUT
         * @uri /timeconditions/:id
         */
         function put_timeconditions_id($params) {
		timeconditions_set_state($params['id'], $params['state']);

		return true;
         }
}
